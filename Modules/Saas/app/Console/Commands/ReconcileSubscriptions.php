<?php

namespace Modules\Saas\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Saas\Contracts\BillingGateway;
use Modules\Saas\Domain\Billing\SubscriptionStatus;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Events\SubscriptionStateChanged;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Services\Billing\NullBillingGateway;

/**
 * Reconciles local subscription state with the billing provider (plan §9.2).
 *
 * Webhooks are the primary state source, but they can be delayed, dropped,
 * or arrive out of order. This command periodically re-fetches authoritative
 * state from the provider and corrects any drift.
 *
 * Schedule: every 6 hours (configurable via saas.billing.reconcile_schedule).
 *
 * Usage:
 *   php artisan saas:reconcile-subscriptions
 *   php artisan saas:reconcile-subscriptions --tenant=uuid
 *   php artisan saas:reconcile-subscriptions --dry-run
 */
class ReconcileSubscriptions extends Command
{
    protected $signature = 'saas:reconcile-subscriptions
        {--tenant= : Reconcile only this tenant UUID}
        {--dry-run : Report drift without applying changes}
        {--verbose-output : Log every subscription checked}';

    protected $description = 'Reconcile local subscription state with the billing provider';

    public function handle(BillingGateway $gateway): int
    {
        if ($gateway instanceof NullBillingGateway) {
            $this->warn('Billing gateway is null. No provider to reconcile against.');
            $this->info('Configure SAAS_BILLING_PROVIDER and bind a real gateway to enable reconciliation.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $tenantFilter = $this->option('tenant');

        $this->info($dryRun
            ? 'Starting subscription reconciliation (DRY RUN)...'
            : 'Starting subscription reconciliation...'
        );

        $query = Subscription::query()
            ->whereIn('status', SubscriptionStatus::reconcilableValues())
            ->whereNotNull('provider_subscription_id');

        if ($tenantFilter) {
            $query->where('tenant_uuid', $tenantFilter);
        }

        $subscriptions = $query->get();
        $total = $subscriptions->count();
        $drifted = 0;
        $errors = 0;

        $this->info("Found {$total} subscription(s) to reconcile.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($subscriptions as $subscription) {
            try {
                $drift = $this->reconcileOne($subscription, $gateway, $dryRun);

                if ($drift) {
                    $drifted++;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::error('Subscription reconciliation failed', [
                    'subscription_uuid' => $subscription->uuid,
                    'tenant_uuid' => $subscription->tenant_uuid,
                    'error' => $e->getMessage(),
                ]);

                if ($this->option('verbose-output')) {
                    $this->newLine();
                    $this->error("  Error for {$subscription->uuid}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Reconciliation complete.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total checked', $total],
                ['Drift corrected', $drifted],
                ['Errors', $errors],
            ]
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Reconcile a single subscription against the provider.
     *
     * @return bool Whether drift was detected.
     */
    private function reconcileOne(Subscription $subscription, BillingGateway $gateway, bool $dryRun): bool
    {
        // Fetch authoritative state from the provider.
        try {
            $providerState = $gateway->fetchSubscription($subscription->provider_subscription_id);
        } catch (\Throwable) {
            // Provider unreachable or subscription not found.
            if ($this->option('verbose-output')) {
                $this->newLine();
                $this->warn("  {$subscription->uuid}: could not fetch from provider");
            }

            return false;
        }

        $localStatus = $subscription->status;
        $providerStatus = $this->mapProviderStatus($providerState['status'] ?? '');

        // Check for status drift.
        if ($providerStatus !== null && $providerStatus !== $localStatus) {
            if ($this->option('verbose-output') || $dryRun) {
                $this->newLine();
                $this->warn("  {$subscription->uuid}: local={$localStatus} provider={$providerStatus}");
            }

            if (! $dryRun) {
                $oldStatus = $subscription->status;
                $subscription->status = $providerStatus;

                // Update period dates if provided.
                if (isset($providerState['current_period_end'])) {
                    $subscription->current_period_end = $providerState['current_period_end'];
                }
                if (isset($providerState['trial_ends_at'])) {
                    $subscription->trial_ends_at = $providerState['trial_ends_at'];
                }

                $subscription->save();

                event(new SubscriptionStateChanged(
                    $subscription->tenant_uuid,
                    $oldStatus,
                    $providerStatus,
                    'reconciliation'
                ));

                Log::info('Subscription reconciled', [
                    'subscription_uuid' => $subscription->uuid,
                    'tenant_uuid' => $subscription->tenant_uuid,
                    'old_status' => $oldStatus,
                    'new_status' => $providerStatus,
                ]);
            }

            return true;
        }

        // Check for expired trial that hasn't transitioned.
        if ($localStatus === 'trialing' && $subscription->trial_ends_at?->isPast()) {
            if ($this->option('verbose-output') || $dryRun) {
                $this->newLine();
                $this->warn("  {$subscription->uuid}: trial expired but still trialing");
            }

            if (! $dryRun) {
                $subscription->status = 'past_due';
                $subscription->save();

                event(new SubscriptionStateChanged(
                    $subscription->tenant_uuid,
                    'trialing',
                    'past_due',
                    'trial_expiry_reconciliation'
                ));
            }

            return true;
        }

        // Check for grace period expiry.
        if ($localStatus === 'grace' && $subscription->grace_ends_at?->isPast()) {
            if ($this->option('verbose-output') || $dryRun) {
                $this->newLine();
                $this->warn("  {$subscription->uuid}: grace period expired");
            }

            if (! $dryRun) {
                $subscription->status = 'paused';
                $subscription->save();

                event(new SubscriptionStateChanged(
                    $subscription->tenant_uuid,
                    'grace',
                    'paused',
                    'grace_expiry_reconciliation'
                ));

                // Suspend the tenant's write access.
                $tenant = Tenant::where('uuid', $subscription->tenant_uuid)->first();
                if ($tenant && $tenant->status === TenantStatus::Active) {
                    $tenant->status = 'suspended';
                    $tenant->save();
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Map a provider-specific status string to our internal state machine.
     */
    private function mapProviderStatus(string $providerStatus): ?string
    {
        return SubscriptionStatus::fromProvider($providerStatus)?->value;
    }
}
