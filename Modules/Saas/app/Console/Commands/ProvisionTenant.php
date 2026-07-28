<?php

namespace Modules\Saas\Console\Commands;

use Illuminate\Console\Command;
use Modules\Saas\Models\Landlord\ProvisioningRun;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Services\TenantProvisioner;

/**
 * Provision a new tenant or resume a failed provisioning run.
 *
 * Usage:
 *   php artisan saas:provision --name="Al Noor School" --slug=al-noor
 *   php artisan saas:provision --resume=<run-uuid>
 *   php artisan saas:provision --pending  (process all queued runs)
 */
class ProvisionTenant extends Command
{
    protected $signature = 'saas:provision
        {--name= : Display name for the new tenant}
        {--slug= : URL-safe slug (derived from name if omitted)}
        {--locale=en : Default locale}
        {--timezone=UTC : Default timezone}
        {--region= : Deployment region}
        {--tier=standard : Plan tier}
        {--resume= : Resume a specific failed provisioning run by UUID}
        {--pending : Process all queued/pending provisioning runs}
        {--dry-run : Show what would happen without executing}';

    protected $description = 'Provision a new tenant or resume a failed provisioning run';

    public function handle(TenantProvisioner $provisioner): int
    {
        if ($this->option('resume')) {
            return $this->resumeRun($provisioner, $this->option('resume'));
        }

        if ($this->option('pending')) {
            return $this->processPending($provisioner);
        }

        if (! $this->option('name')) {
            $this->error('Provide --name for a new tenant, --resume=<uuid> to retry, or --pending for all queued.');

            return self::FAILURE;
        }

        $locale = (string) $this->option('locale');
        if (! in_array($locale, config('localizer.supported_locales', ['en', 'ar']), true)) {
            $this->error("Unsupported locale [{$locale}]. Choose one of: ".implode(', ', config('localizer.supported_locales', ['en', 'ar'])));

            return self::FAILURE;
        }

        return $this->createNew($provisioner);
    }

    private function createNew(TenantProvisioner $provisioner): int
    {
        $name = $this->option('name');
        $slug = $this->option('slug') ?: null;

        if ($this->option('dry-run')) {
            $slugDisplay = $slug ?? 'auto';
            $this->info("[DRY RUN] Would create tenant: {$name} (slug: {$slugDisplay})");

            return self::SUCCESS;
        }

        $this->info("Creating tenant: {$name}");

        try {
            $result = $provisioner->createTenant([
                'display_name' => $name,
                'slug' => $slug ?? $name,
                'locale' => $this->option('locale'),
                'timezone' => $this->option('timezone'),
                'region' => $this->option('region'),
                'tier' => $this->option('tier'),
            ]);

            $tenant = $result['tenant'];
            $run = $result['run'];

            $this->info("Tenant created: {$tenant->uuid} (slug: {$tenant->slug})");
            $this->info("Provisioning run: {$run->uuid}");

            $this->newLine();
            $this->info('Running provisioning pipeline...');

            $provisioner->provision($run);

            $this->newLine();
            $this->info("✓ Tenant [{$tenant->slug}] provisioned successfully.");
            $this->info("  UUID: {$tenant->uuid}");
            $this->info("  Domain: {$tenant->slug}.localhost (development)");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Provisioning failed: {$e->getMessage()}");
            $this->line('  Run `php artisan saas:provision --resume=<run-uuid>` to retry.');

            return self::FAILURE;
        }
    }

    private function resumeRun(TenantProvisioner $provisioner, string $runUuid): int
    {
        $run = ProvisioningRun::where('uuid', $runUuid)->first();

        if ($run === null) {
            $this->error("Provisioning run [{$runUuid}] not found.");

            return self::FAILURE;
        }

        if ($run->state->isTerminal() && ! $run->state->isRetryable()) {
            $this->error("Run [{$runUuid}] is in terminal state [{$run->state->value}] and cannot be retried.");

            return self::FAILURE;
        }

        $tenant = Tenant::where('uuid', $run->tenant_uuid)->first();

        $this->info("Resuming provisioning for tenant [{$tenant?->slug}] (run: {$runUuid})");
        $this->info("  Current state: {$run->state->value}");
        $this->info("  Completed steps: " . implode(', ', array_keys(array_filter($run->steps ?? [], fn ($s) => $s['ok'] ?? false))));

        if ($this->option('dry-run')) {
            $this->info('[DRY RUN] Would resume from next incomplete step.');

            return self::SUCCESS;
        }

        try {
            $provisioner->provision($run);

            $this->newLine();
            $this->info("✓ Provisioning completed for [{$tenant?->slug}].");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Still failing: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function processPending(TenantProvisioner $provisioner): int
    {
        $runs = ProvisioningRun::whereIn('state', ['queued', 'failed_recoverable'])
            ->orderBy('created_at')
            ->get();

        if ($runs->isEmpty()) {
            $this->info('No pending provisioning runs.');

            return self::SUCCESS;
        }

        $this->info("Found {$runs->count()} pending run(s).");

        $failures = 0;

        foreach ($runs as $run) {
            $tenant = Tenant::where('uuid', $run->tenant_uuid)->first();
            $this->newLine();
            $this->info("Processing: {$tenant?->slug} ({$run->uuid})");

            try {
                $provisioner->provision($run);
                $this->info("  ✓ Done.");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed: {$e->getMessage()}");
                $failures++;
            }
        }

        $this->newLine();
        $this->info("Completed. Failures: {$failures}");

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
