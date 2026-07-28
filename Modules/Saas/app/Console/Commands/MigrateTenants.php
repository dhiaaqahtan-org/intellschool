<?php

namespace Modules\Saas\Console\Commands;

use Illuminate\Console\Command;
use Modules\Saas\Models\Landlord\Tenant;

/**
use Modules\Saas\Services\TenantMigrationRunner;
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

    public function handle(TenantMigrationRunner $migrationRunner): int
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
            $this->info('Batch '.($batchIndex + 1)." ({$batch->count()} tenants)...");

            foreach ($batch as $tenant) {
                $outcome = $migrationRunner->migrateTenant($tenant, force: true);
                $result = $outcome['success']
                    ? ($outcome['migrated'] > 0 ? true : null)
                    : ($outcome['error'] ?? 'Unknown migration failure.');

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
        $this->info("Done. Migrated: {$migrated}, Failures: ".count($failures));

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
