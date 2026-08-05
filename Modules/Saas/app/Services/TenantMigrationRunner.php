<?php

namespace Modules\Saas\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

/**
 * Runs schema migrations on tenant databases (plan §4, Phase 4).
 *
 * This service handles:
 *  - Running the full ERP schema on a new tenant database.
 *  - Running incremental migrations for existing tenants.
 *  - Recording migration state per-tenant in the landlord database.
 *  - Batch/canary rollout with pause/resume support.
 *
 * It is used by the MigrateTenants console command and the provisioning
 * pipeline. It never runs migrations on the landlord database.
 */
class TenantMigrationRunner
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
    ) {}

    /**
     * Run all pending migrations for a single tenant.
     *
     * @param  Tenant  $tenant  The tenant to migrate.
     * @param  bool  $force  Skip confirmation prompts.
     * @return array{success: bool, migrated: int, error: ?string}
     */
    public function migrateTenant(Tenant $tenant, bool $force = false): array
    {
        $database = $tenant->database;

        if (! $database) {
            return [
                'success' => false,
                'migrated' => 0,
                'error' => 'Tenant has no database record.',
            ];
        }

        $connectionName = $this->configureConnection($tenant);

        try {
            // Record the migration run start.
            $runId = $this->recordMigrationStart($tenant, $connectionName);

            // Run migrations using Artisan.
            $exitCode = Artisan::call('migrate', [
                '--database' => $connectionName,
                '--force' => $force,
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();

            if ($exitCode === 0) {
                $supportPath = module_path('Saas', 'database/migrations/tenant');
                $exitCode = Artisan::call('migrate', [
                    '--database' => $connectionName,
                    '--path' => $supportPath,
                    '--realpath' => true,
                    '--force' => $force,
                    '--no-interaction' => true,
                ]);
                $output .= PHP_EOL.Artisan::output();
            }

            $migratedCount = $this->countMigrations($output);

            if ($exitCode === 0) {
                $this->recordMigrationSuccess($runId, $migratedCount);

                // Update the tenant's schema version.
                $database->update([
                    'schema_version' => $this->getCurrentSchemaVersion($connectionName),
                    'app_version' => config('app.version', app()->version()),
                    'last_migrated_at' => now(),
                    'health_status' => 'healthy',
                ]);

                Log::info('Tenant migration completed', [
                    'tenant_uuid' => $tenant->uuid,
                    'migrated' => $migratedCount,
                ]);

                return [
                    'success' => true,
                    'migrated' => $migratedCount,
                    'error' => null,
                ];
            }

            $error = "Migration failed with exit code {$exitCode}: {$output}";
            $this->recordMigrationFailure($runId, $error);

            return [
                'success' => false,
                'migrated' => $migratedCount,
                'error' => $error,
            ];
        } catch (\Throwable $e) {
            if (isset($runId)) {
                $this->recordMigrationFailure($runId, $e->getMessage());
            }
            $database->update(['health_status' => 'migration_failed']);

            Log::error('Tenant migration exception', [
                'tenant_uuid' => $tenant->uuid,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'migrated' => 0,
                'error' => $e->getMessage(),
            ];
        } finally {
            // Always purge the dynamic connection.
            $this->purgeConnection();
        }
    }

    /**
     * Run the tenant-specific SaaS support migrations.
     * These are additive migrations that add tenant_installations and
     * other SaaS support tables to the tenant database.
     */
    public function migrateTenantSupportTables(Tenant $tenant): array
    {
        $database = $tenant->database;

        if (! $database) {
            return ['success' => false, 'error' => 'No database record.'];
        }

        $connectionName = $this->configureConnection($tenant);

        try {
            $migrationPath = module_path('Saas', 'database/migrations/tenant');

            $exitCode = Artisan::call('migrate', [
                '--database' => $connectionName,
                '--path' => $migrationPath,
                '--realpath' => true,
                '--force' => true,
                '--no-interaction' => true,
            ]);

            return [
                'success' => $exitCode === 0,
                'error' => $exitCode === 0 ? null : Artisan::output(),
            ];
        } finally {
            $this->purgeConnection();
        }
    }

    /**
     * Check if a tenant has pending migrations.
     */
    public function hasPendingMigrations(Tenant $tenant): bool
    {
        $database = $tenant->database;

        if (! $database) {
            return false;
        }

        $connectionName = $this->configureConnection($tenant);

        try {
            Artisan::call('migrate:status', [
                '--database' => $connectionName,
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();

            return str_contains($output, 'Pending');
        } catch (\Throwable) {
            return true; // Assume pending if we can't check.
        } finally {
            $this->purgeConnection();
        }
    }

    /**
     * Get the current schema version for a tenant.
     */
    public function getSchemaVersion(Tenant $tenant): ?string
    {
        return $tenant->database?->schema_version;
    }

    /** Enter the standard tenant context so credentials always pass through
     * the configured TenantCredentialResolver rather than being assembled here.
     */
    private function configureConnection(Tenant $tenant): string
    {
        if ($this->currentTenant->has()) {
            throw new \LogicException('Tenant migrations must start from the control plane.');
        }

        $host = $tenant->primaryDomain()?->hostname ?? $tenant->slug.'.migration';
        $this->currentTenant->set($tenant->toContext($host));

        return config('saas.database.tenant_connection', 'tenant');
    }

    /**
     * Tear down the full tenant context, including cache and storage prefixes.
     */
    private function purgeConnection(): void
    {
        if ($this->currentTenant->has()) {
            $this->currentTenant->forget();
        }
    }

    /**
     * Count the number of migrations from Artisan output.
     */
    private function countMigrations(string $output): int
    {
        return preg_match_all('/Migrating:|Migrated:/i', $output);
    }

    /**
     * Get the current schema version from the migrations table.
     */
    private function getCurrentSchemaVersion(string $connectionName): ?string
    {
        try {
            $lastMigration = DB::connection($connectionName)
                ->table('migrations')
                ->orderBy('id', 'desc')
                ->value('migration');

            return $lastMigration;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Record a migration run start in the landlord database.
     */
    private function recordMigrationStart(Tenant $tenant, string $connectionName): int
    {
        return DB::connection(config('saas.database.landlord_connection', 'landlord'))
            ->table('saas_migration_runs')
            ->insertGetId([
                'tenant_uuid' => $tenant->uuid,
                'release' => config('app.version', 'unknown'),
                'started_at' => now(),
                'status' => 'running',
                'application_version' => config('app.version'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Record migration success.
     */
    private function recordMigrationSuccess(int $runId, int $migratedCount): void
    {
        DB::connection(config('saas.database.landlord_connection', 'landlord'))
            ->table('saas_migration_runs')
            ->where('id', $runId)
            ->update([
                'status' => 'completed',
                'finished_at' => now(),
                'migrations_run' => $migratedCount,
                'updated_at' => now(),
            ]);
    }

    /**
     * Record migration failure.
     */
    private function recordMigrationFailure(int $runId, string $error): void
    {
        DB::connection(config('saas.database.landlord_connection', 'landlord'))
            ->table('saas_migration_runs')
            ->where('id', $runId)
            ->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_summary' => substr($error, 0, 1000),
                'updated_at' => now(),
            ]);
    }
}
