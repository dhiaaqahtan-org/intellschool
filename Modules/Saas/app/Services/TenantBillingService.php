<?php

namespace Modules\Saas\Services;

use Modules\Saas\Models\Landlord\Subscription;

/**
 * Read-only tenant billing projection.
 *
 * Tenant controllers use this service instead of reaching into landlord
 * models directly. Provider mutations remain behind BillingGateway.
 */
class TenantBillingService
{
    public function currentFor(string $tenantUuid): ?Subscription
    {
        return Subscription::query()
            ->forTenant($tenantUuid)
            ->with('plan.features')
            ->latest('created_at')
            ->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function summary(?Subscription $subscription): ?array
    {
        if ($subscription === null) {
            return null;
        }

        return [
            'uuid' => $subscription->uuid,
            'status' => $subscription->status,
            'provider' => $subscription->provider,
            'plan' => $subscription->plan === null ? null : [
                'code' => $subscription->plan->plan_code,
                'name' => $subscription->plan->display_name,
                'interval' => $subscription->plan->billing_interval,
                'currency' => $subscription->plan->currency,
                'price_cents' => (int) $subscription->plan->price_cents,
            ],
            'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
            'current_period_end' => $subscription->current_period_end?->toIso8601String(),
            'grace_ends_at' => $subscription->grace_ends_at?->toIso8601String(),
            'cancel_at_period_end' => (bool) $subscription->cancel_at_period_end,
        ];
    }
}
