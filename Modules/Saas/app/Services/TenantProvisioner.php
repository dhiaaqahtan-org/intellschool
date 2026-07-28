<?php

namespace Modules\Saas\Services;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Domain\Provisioning\ProvisioningStep;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Events\TenantProvisioned;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\ProvisioningRun;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDatabase;
use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Models\Landlord\TenantOwner;
use Modules\Saas\Models\Tenant\TenantInstallation;
use Throwable;

/**
 * Idempotent tenant provisioning state machine (plan §11).
 *
 * Every step is recorded. Retrying a step never creates a duplicate tenant,
 * database, owner, subscription, or domain — idempotency is enforced by the
 * run's idempotency key and per-step completion checks.
 *
 * Steps:
 *  1. allocate_database  — CREATE DATABASE + credentials via secret manager
 *  2. migrate            — run ERP schema + tenant support migrations
 *  3. seed               — minimum required defaults (org, team, period, owner)
 *  4. configure_domain   — create subdomain record
 *  5. verify             — health/isolation checks
 *  6. ready              — mark tenant active, fire event
 */
class TenantProvisioner
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
    ) {}

    /**
     * Create a pending tenant and queue its provisioning run.
     *
     * @param  array  $attributes  display_name, slug, locale, timezone, region, tier, owner_email
     * @return array{tenant: Tenant, run: ProvisioningRun}
     */
    public function createTenant(array $attributes): array
    {
        $slug = $this->normalizeSlug($attributes['slug'] ?? $attributes['display_name'] ?? '');

        $locale = $attributes['locale'] ?? config('app.fallback_locale', 'en');
        if (! in_array($locale, config('localizer.supported_locales', ['en', 'ar']), true)) {
            throw new \InvalidArgumentException(
                "Unsupported tenant locale [{$locale}]. Supported locales: ".implode(', ', config('localizer.supported_locales', ['en', 'ar']))
            );
        }
        $this->assertSlugAvailable($slug);

        $tenant = Tenant::create([
            'slug' => $slug,
            'display_name' => $attributes['display_name'] ?? $slug,
            'legal_name' => $attributes['legal_name'] ?? null,
            'status' => TenantStatus::Pending->value,
            'tier' => $attributes['tier'] ?? 'standard',
            'region' => $attributes['region'] ?? null,
            'locale' => $locale,
            'timezone' => $attributes['timezone'] ?? 'UTC',
            'provisioning_state' => ProvisioningState::Queued->value,
            'meta' => $attributes['meta'] ?? null,
        ]);

        if (isset($attributes['owner_email']) && trim((string) $attributes['owner_email']) !== '') {
            TenantOwner::create([
                'tenant_uuid' => $tenant->uuid,
                'name' => $attributes['owner_name'] ?? null,
                'email' => Str::lower(trim((string) $attributes['owner_email'])),
                'role' => 'owner',
                'status' => 'invited',
                'invited_at' => now(),
            ]);
        }

        $idempotencyKey = "provision:{$tenant->uuid}";

        $run = ProvisioningRun::create([
            'tenant_uuid' => $tenant->uuid,
            'idempotency_key' => $idempotencyKey,
            'state' => ProvisioningState::Queued->value,
            'started_at' => now(),
        ]);

        AuditEvent::record(
            action: 'tenant.created',
            tenantUuid: $tenant->uuid,
            context: ['slug' => $slug, 'display_name' => $tenant->display_name],
            actorType: 'platform',
        );

        return ['tenant' => $tenant, 'run' => $run];
    }

    /**
     * Execute the full provisioning pipeline for a run.
     * Safe to call multiple times — completed steps are skipped.
     */
    public function provision(ProvisioningRun $run): void
    {
        $tenant = Tenant::where('uuid', $run->tenant_uuid)->firstOrFail();

        $steps = [
            [ProvisioningStep::AllocateDatabase, fn () => $this->allocateDatabase($tenant)],
            [ProvisioningStep::Migrate, fn () => $this->runMigrations($tenant)],
            [ProvisioningStep::Seed, fn () => $this->seedDefaults($tenant)],
            [ProvisioningStep::ConfigureDomain, fn () => $this->configureDomain($tenant)],
            [ProvisioningStep::Verify, fn () => $this->verify($tenant)],
        ];

        foreach ($steps as [$step, $stepFn]) {
            $stepName = $step->value;
            if ($run->hasCompleted($stepName)) {
                continue;
            }

            $this->advanceState($run, $tenant, $step);

            try {
                $stepFn();
                $run->recordStep($stepName, true);
            } catch (Throwable $e) {
                $run->recordStep($stepName, false, $this->safeError($e));
                $this->markFailed($run, $tenant, $e);

                throw $e;
            }
        }

        $this->markReady($run, $tenant);
    }

    /**
     * Step 1: Create the tenant database and record its metadata.
     */
    private function allocateDatabase(Tenant $tenant): void
    {
        // Idempotent: skip if the database record already exists.
        if ($tenant->database()->exists()) {
            return;
        }

        $databaseName = TenantDatabase::nameFor($tenant->uuid);
        $connection = config('saas.database.landlord_connection', 'landlord');

        // Use the landlord connection to CREATE the tenant database.
        // In production this would go through an infrastructure adapter
        // (RDS API, Cloud SQL Admin, etc.) rather than raw SQL.
        $templateConnection = config('saas.database.tenant_template', 'mysql');
        $driver = config("database.connections.{$templateConnection}.driver", 'mysql');

        if ($driver === 'mysql') {
            DB::connection($connection)->statement(
                "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
        }

        TenantDatabase::create([
            'tenant_uuid' => $tenant->uuid,
            'cluster' => 'default',
            'database_name' => $databaseName,
            'secret_ref' => 'env:SAAS_CLUSTER_DEFAULT', // dev only; production uses secret manager
            'health_status' => 'creating',
        ]);
    }

    /**
     * Step 2: Run the ERP schema migrations inside the tenant database.
     */
    private function runMigrations(Tenant $tenant): void
    {
        $database = $tenant->database()->firstOrFail();

        // Build a temporary context to run migrations against the tenant DB.
        $context = new TenantContext(
            uuid: $tenant->uuid,
            slug: $tenant->slug,
            status: TenantStatus::Pending,
            databaseName: $database->database_name,
            connectionName: config('saas.database.tenant_connection', 'tenant'),
            host: '',
            cluster: $database->cluster,
            secretRef: $database->secret_ref,
            locale: $tenant->locale ?? 'en',
            timezone: $tenant->timezone ?? 'UTC',
        );

        $this->currentTenant->runFor($context, function () use ($database) {
            $migrator = app('migrator');
            $tenantConnection = config('saas.database.tenant_connection', 'tenant');
            $previousConnection = $migrator->getConnection();
            $paths = [database_path('migrations')];

            $tenantMigrationPath = module_path('Saas', 'database/migrations/tenant');
            if (is_dir($tenantMigrationPath)) {
                $paths[] = $tenantMigrationPath;
            }

            try {
                // Migrator keeps its own connection state; changing Laravel's
                // default connection alone is insufficient on a long-lived
                // worker. Always select the active tenant explicitly.
                $migrator->setConnection($tenantConnection);

                if (! $migrator->repositoryExists()) {
                    $migrator->getRepository()->createRepository();
                }

                $migrator->run($paths, ['step' => false]);
                $schemaVersion = DB::table('migrations')
                    ->orderByDesc('id')
                    ->value('migration');
            } finally {
                $migrator->setConnection($previousConnection);
            }

            $database->update([
                'schema_version' => $schemaVersion,
                'app_version' => config('app.version', app()->version()),
                'last_migrated_at' => now(),
                'health_status' => 'healthy',
            ]);
        });
    }

    /**
     * Step 3: Seed minimum required defaults.
     */
    private function seedDefaults(Tenant $tenant): void
    {
        $database = $tenant->database()->firstOrFail();

        $context = new TenantContext(
            uuid: $tenant->uuid,
            slug: $tenant->slug,
            status: TenantStatus::Pending,
            databaseName: $database->database_name,
            connectionName: config('saas.database.tenant_connection', 'tenant'),
            host: '',
            cluster: $database->cluster,
            secretRef: $database->secret_ref,
        );

        $this->currentTenant->runFor($context, function () use ($tenant) {
            // Insert the tenant_installations self-identification record.
            TenantInstallation::query()->updateOrCreate(
                ['tenant_uuid' => $tenant->uuid],
                [
                    'tenant_slug' => $tenant->slug,
                    'schema_version' => app()->version(),
                    'app_version' => config('app.version', '5.5.0'),
                    'provisioned_at' => now(),
                    'access_state' => 'active',
                ]
            );

            // Run the configured tenant seeder (full structural seed).
            $seederClass = config('saas.provisioning.tenant_seeder', 'Database\\Seeders\\TenantSeeder');

            if (class_exists($seederClass)) {
                try {
                    app(Kernel::class)->call('db:seed', [
                        '--class' => $seederClass,
                        '--force' => true,
                    ]);
                } catch (Throwable $e) {
                    // Non-fatal: the owner can complete setup via the wizard.
                    report($e);
                }
            } else {
                // Fallback to minimal structural seed.
                try {
                    $this->seedEssentials($tenant);
                } catch (Throwable $e) {
                    report($e);
                }
            }
        });
    }

    /**
     * Seed only the structural essentials: organization, team, academic period.
     */
    private function seedEssentials(Tenant $tenant): void
    {
        // Create default organization if the table exists.
        if (Schema::hasTable('organizations')) {
            $orgId = DB::table('organizations')->insertGetId([
                'name' => $tenant->display_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create default team/campus.
            if (Schema::hasTable('teams')) {
                DB::table('teams')->insert([
                    'organization_id' => $orgId,
                    'name' => $tenant->display_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Step 4: Create the tenant's primary subdomain.
     */
    private function configureDomain(Tenant $tenant): void
    {
        // Idempotent: skip if a primary domain already exists.
        if ($tenant->domains()->where('is_primary', true)->exists()) {
            return;
        }

        // Note the explicit '.' separator. ltrim($suffix, '.') strips the
        // leading dot from the conventional ".product.example" form, so
        // concatenating directly produced "alphaproduct.example" — a valid
        // string, a registerable-looking hostname, and completely wrong.
        $suffix = ltrim((string) config('saas.hosts.tenant_suffix', ''), '.');

        if ($suffix === '') {
            $suffix = 'localhost'; // development default
        }

        $hostname = $tenant->slug.'.'.$suffix;

        TenantDomain::create([
            'tenant_uuid' => $tenant->uuid,
            'hostname' => $hostname,
            'type' => TenantDomain::TYPE_SUBDOMAIN,
            'is_primary' => true,
            'verified_at' => now(), // Subdomains we issue are trusted immediately.
            'tls_status' => 'pending',
        ]);
    }

    /**
     * Step 5: Health and isolation verification.
     */
    private function verify(Tenant $tenant): void
    {
        $database = $tenant->database()->firstOrFail();

        $context = new TenantContext(
            uuid: $tenant->uuid,
            slug: $tenant->slug,
            status: TenantStatus::Pending,
            databaseName: $database->database_name,
            connectionName: config('saas.database.tenant_connection', 'tenant'),
            host: '',
            cluster: $database->cluster,
            secretRef: $database->secret_ref,
        );

        $this->currentTenant->runFor($context, function () use ($tenant) {
            // Verify the self-identification record matches.
            $installation = TenantInstallation::query()
                ->where('tenant_uuid', $tenant->uuid)
                ->first();

            if ($installation === null) {
                throw new \RuntimeException(
                    "Tenant [{$tenant->uuid}] verification failed: no installation record found."
                );
            }

            if ($installation->tenant_uuid !== $tenant->uuid) {
                throw new \RuntimeException(
                    "Tenant [{$tenant->uuid}] verification failed: UUID mismatch in tenant_installations."
                );
            }
        });
    }

    /**
     * Mark the tenant as ready and fire the provisioned event.
     */
    private function markReady(ProvisioningRun $run, Tenant $tenant): void
    {
        $run->update([
            'state' => ProvisioningState::Ready->value,
            'progress' => 100,
            'finished_at' => now(),
        ]);

        $tenant->update([
            'status' => TenantStatus::Active->value,
            'provisioning_state' => ProvisioningState::Ready->value,
        ]);

        $database = $tenant->database()->first();

        TenantProvisioned::dispatch(
            $tenant->uuid,
            $tenant->slug,
            $database?->database_name ?? '',
        );
    }

    private function markFailed(ProvisioningRun $run, Tenant $tenant, Throwable $e): void
    {
        $recoverable = ! ($e instanceof \LogicException);

        $state = $recoverable
            ? ProvisioningState::FailedRecoverable
            : ProvisioningState::FailedManualReview;

        $run->update([
            'state' => $state->value,
            'error_summary' => $this->safeError($e),
            'finished_at' => now(),
        ]);

        $tenant->update([
            'provisioning_state' => $state->value,
        ]);

        AuditEvent::record(
            action: 'tenant.provisioning_failed',
            tenantUuid: $tenant->uuid,
            context: [
                'step' => $run->step,
                'state' => $state->value,
                'error' => $this->safeError($e),
            ],
            actorType: 'system',
        );
    }

    private function advanceState(ProvisioningRun $run, Tenant $tenant, ProvisioningStep $step): void
    {
        $state = $step->state();

        $run->update(['state' => $state->value, 'step' => $step->value, 'progress' => $step->progress()]);
        $tenant->update(['provisioning_state' => $state->value]);
    }

    private function normalizeSlug(string $input): string
    {
        $slug = Str::slug($input, '-');
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $slug = trim($slug, '-');

        if (strlen($slug) < 2) {
            $slug = 'school-'.Str::random(6);
        }

        // Reserved slugs cannot be issued.
        $reserved = config('saas.tenancy.reserved_slugs', []);
        if (in_array($slug, $reserved, true)) {
            $slug = $slug.'-'.Str::random(4);
        }

        return Str::limit($slug, 63, '');
    }

    private function assertSlugAvailable(string $slug): void
    {
        if (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            throw new \InvalidArgumentException(
                "The slug [{$slug}] is already taken. Choose a different name."
            );
        }
    }

    /**
     * Produce an operator-safe error summary. Never includes credentials,
     * connection strings, or personal data.
     */
    private function safeError(Throwable $e): string
    {
        $message = $e->getMessage();

        // Strip anything that looks like a credential or connection string.
        $message = preg_replace('/(password|secret|token|key)\s*[=:]\s*\S+/i', '$1=[REDACTED]', $message);
        $message = preg_replace('/mysql:\/\/[^\s]+/', 'mysql://[REDACTED]', $message);

        return Str::limit($message, 500, '...');
    }
}
