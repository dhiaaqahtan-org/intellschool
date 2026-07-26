<?php

namespace Modules\Saas\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

/**
 * Verify that tenant databases are correctly isolated (plan §17.1).
 *
 * Checks:
 *  - Each tenant database contains its own UUID in tenant_installations.
 *  - No tenant database contains another tenant's UUID.
 *  - The landlord database does not contain tenant data tables.
 *  - Connection switching leaves no stale state.
 *
 * Usage:
 *   php artisan saas:verify-isolation
 *   php artisan saas:verify-isolation --tenant=uuid
 */
class VerifyTenantIsolation extends Command
{
    protected $signature = 'saas:verify-isolation
        {--tenant= : Verify only this tenant UUID}
        {--verbose-errors : Show full error details}';

    protected $description = 'Verify tenant database isolation integrity';

    private int $passed = 0;

    private int $failed = 0;

    public function handle(CurrentTenant $currentTenant): int
    {
        $this->info('Tenant Isolation Verification');
        $this->info(str_repeat('=', 50));

        $query = Tenant::query()
            ->where('provisioning_state', 'ready')
            ->with('database');

        if ($this->option('tenant')) {
            $query->where('uuid', $this->option('tenant'));
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No provisioned tenants found.');

            return self::SUCCESS;
        }

        $this->info("Checking {$tenants->count()} tenant(s)...");
        $this->newLine();

        // Check 1: Self-identification in each tenant database.
        $this->info('Check 1: Tenant self-identification');
        foreach ($tenants as $tenant) {
            $this->verifySelfIdentification($tenant, $currentTenant);
        }

        $this->newLine();

        // Check 2: Cross-tenant contamination.
        $this->info('Check 2: Cross-tenant contamination');
        foreach ($tenants as $tenant) {
            $this->verifyNoCrossContamination($tenant, $tenants, $currentTenant);
        }

        $this->newLine();

        // Check 3: Landlord does not contain tenant tables.
        $this->info('Check 3: Landlord database purity');
        $this->verifyLandlordPurity();

        $this->newLine();

        // Check 4: Connection cleanup.
        $this->info('Check 4: Connection state cleanup');
        $this->verifyConnectionCleanup($tenants->first(), $currentTenant);

        $this->newLine();
        $this->info(str_repeat('=', 50));
        $this->info("Results: {$this->passed} passed, {$this->failed} failed");

        if ($this->failed > 0) {
            $this->error('ISOLATION VERIFICATION FAILED');

            return self::FAILURE;
        }

        $this->info('All isolation checks passed.');

        return self::SUCCESS;
    }

    private function verifySelfIdentification(Tenant $tenant, CurrentTenant $currentTenant): void
    {
        try {
            $context = $tenant->toContext($tenant->slug.'.test');

            $currentTenant->runFor($context, function () use ($tenant) {
                $installation = DB::table('tenant_installations')->first();

                if ($installation === null) {
                    $this->fail("  {$tenant->slug}: No tenant_installations record found");

                    return;
                }

                if ($installation->tenant_uuid !== $tenant->uuid) {
                    $this->fail("  {$tenant->slug}: UUID mismatch! Expected {$tenant->uuid}, found {$installation->tenant_uuid}");

                    return;
                }

                $this->pass("  {$tenant->slug}: UUID matches");
            });
        } catch (\Throwable $e) {
            $this->fail("  {$tenant->slug}: Connection failed - {$e->getMessage()}");
        }
    }

    private function verifyNoCrossContamination(Tenant $tenant, $allTenants, CurrentTenant $currentTenant): void
    {
        $otherUuids = $allTenants
            ->where('uuid', '!=', $tenant->uuid)
            ->pluck('uuid');

        if ($otherUuids->isEmpty()) {
            $this->pass("  {$tenant->slug}: No other tenants to check against");

            return;
        }

        try {
            $context = $tenant->toContext($tenant->slug.'.test');

            $currentTenant->runFor($context, function () use ($tenant, $otherUuids) {
                $contaminated = DB::table('tenant_installations')
                    ->whereIn('tenant_uuid', $otherUuids->toArray())
                    ->exists();

                if ($contaminated) {
                    $this->fail("  {$tenant->slug}: CONTAINS another tenant's installation record!");
                } else {
                    $this->pass("  {$tenant->slug}: No cross-tenant records");
                }
            });
        } catch (\Throwable $e) {
            $this->fail("  {$tenant->slug}: Check failed - {$e->getMessage()}");
        }
    }

    private function verifyLandlordPurity(): void
    {
        $landlordConnection = config('saas.database.landlord_connection', 'landlord');

        // These tables must NOT exist in the landlord database.
        $tenantOnlyTables = ['users', 'students', 'teams', 'organizations', 'tenant_installations'];

        try {
            $tables = DB::connection($landlordConnection)
                ->select('SHOW TABLES');

            $tableNames = array_map(fn ($t) => array_values((array) $t)[0], $tables);

            $leaked = array_intersect($tenantOnlyTables, $tableNames);

            if (! empty($leaked)) {
                $this->fail('  Landlord contains tenant tables: ' . implode(', ', $leaked));
            } else {
                $this->pass('  Landlord database is clean (no tenant tables)');
            }
        } catch (\Throwable $e) {
            $this->fail("  Could not inspect landlord database: {$e->getMessage()}");
        }
    }

    private function verifyConnectionCleanup(Tenant $tenant, CurrentTenant $currentTenant): void
    {
        if ($tenant === null) {
            $this->pass('  Skipped (no tenants)');

            return;
        }

        try {
            $context = $tenant->toContext($tenant->slug.'.test');

            // Enter and exit tenant context.
            $currentTenant->runFor($context, function () {
                // Just connect and disconnect.
                DB::table('tenant_installations')->limit(1)->get();
            });

            // After runFor, no tenant should be active.
            if ($currentTenant->has()) {
                $this->fail('  Tenant context leaked after runFor() completed!');
            } else {
                $this->pass('  Context properly cleaned up after runFor()');
            }
        } catch (\Throwable $e) {
            $this->fail("  Cleanup check failed: {$e->getMessage()}");
        }
    }

    private function pass(string $message): void
    {
        $this->line("  ✓ {$message}");
        $this->passed++;
    }

    private function fail(string $message): void
    {
        $this->error("  ✗ {$message}");
        $this->failed++;
    }
}
