<?php

namespace Modules\Saas\Console\Commands;

use Illuminate\Console\Command;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDatabase;

/**
 * Run migrations across all tenant databases with batched rollout,
 * pause/resume, and per-tenant failure isolation (plan §Phase 4).
 *
 * Usage:
 *   php artisan saas:migrate-tenants              (all active tenants)
 *   php artisan saas:migrate-tenants --tenant=uuid (single tenant)
 *   php artisan saas:migrate-tenants --batch=5    (5 at a time)
 *   php artisan saas:migrate-tenants --dry-run    (report only)
 */
class MigrateTenants extends Command
{
    protected $signature = 'saas:migrate-tenants
        {--tenant= : Migrate only this tenant UUID}
        {--batch=10 : Number of tenants per batch}
        {--force : Skip confirmation}
        {--dry-run : Show which tenants need migration without executing}';

    protected $description = 'Run schema migrations across tenant databases';

    public function handle(CurrentTenant $currentTenant): int
    {
        $query = Tenant::query()
            ->where('provisioning_state', 'ready')
            ->whereIn('status', ['active', 'suspended']);

        if ($this->option('tenant')) {
            $query->where('uuid', $this->option('tenant'));
        }

        $tenants = $query->with('database')->get();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found to migrate.');

            return self::SUCCESS;
        }

        $this->info("Found {$tenants->count()} tenant(s) to check.");

        if ($this->option('dry-run')) {
            return $this->dryRun($tenants);
        }

        if (! $this->option('force') && ! $this->confirm("Run migrations on {$tenants->count()} tenant database(s)?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $batchSize = (int) $this->option('batch');
        $batches = $tenants->chunk($batchSize);
        $failures = [];
        $migrated = 0;

        foreach ($batches as $batchIndex => $batch) {
            $this->newLine();
            $this->info("Batch " . ($batchIndex + 1) . " ({$batch->count()} tenants)...");

            foreach ($batch as $tenant) {
                $result = $this->migrateTenant($tenant, $currentTenant);

                if ($result === true) {
                    $migrated++;
                    $this->line("  ✓ {$tenant->slug}");
                } elseif ($result === null) {
                    $this->line("  - {$tenant->slug} (already up to date)");
                } else {
                    $failures[] = ['slug' => $tenant->slug, 'error' => $result];
                    $this->error("  ✗ {$tenant->slug}: {$result}");
                }
            }

            // Pause between batches to allow monitoring and back-pressure.
            if ($batchIndex < $batches->count() - 1 && ! $this->option('force')) {
                if (! $this->confirm('Continue to next batch?', true)) {
                    $this->warn('Paused. Re-run to continue remaining tenants.');
                    break;
                }
            }
        }

        $this->newLine();
        $this->info("Done. Migrated: {$migrated}, Failures: " . count($failures));

        if (! empty($failures)) {
            $this->newLine();
            $this->warn('Failed tenants:');
            foreach ($failures as $f) {
                $this->line("  - {$f['slug']}: {$f['error']}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return bool|null|string true=migrated, null=nothing to do, string=error
     */
    private function migrateTenant(Tenant $tenant, CurrentTenant $currentTenant): bool|null|string
    {
        $database = $tenant->database;

        if ($database === null) {
            return 'No database record found.';
        }

        try {
            $context = $tenant->toContext($tenant->primaryDomain()?->hostname ?? $tenant->slug.'.localhost');

            $migrated = false;

            $currentTenant->runFor($context, function () use (&$migrated, $database, $tenant) {
                $migrator = app('migrator');

                $paths = [database_path('migrations')];
                $tenantMigrationPath = module_path('Saas', 'database/migrations/tenant');
                if (is_dir($tenantMigrationPath)) {
                    $paths[] = $tenantMigrationPath;
                }

                $pending = $migrator->run($paths, ['pretend' => false]);

                $migrated = count($migrator->getNotes()) > 0;

                // Update schema tracking.
                $database->update([
                    'schema_version' => app()->version(),
                    'last_migrated_at' => now(),
                    'health_status' => 'healthy',
                ]);
            });

            return $migrated ?: null;
        } catch (\Throwable $e) {
            // Update health status on failure.
            $database->update(['health_status' => 'migration_failed']);

            return $e->getMessage();
        }
    }

    private function dryRun($tenants): int
    {
        $this->newLine();
        $this->info('[DRY RUN] Tenants that would be migrated:');

        foreach ($tenants as $tenant) {
            $db = $tenant->database;
            $version = $db?->schema_version ?? 'unknown';
            $lastMigrated = $db?->last_migrated_at?->diffForHumans() ?? 'never';

            $this->line("  - {$tenant->slug} (schema: {$version}, last migrated: {$lastMigrated})");
        }

        return self::SUCCESS;
    }
}
