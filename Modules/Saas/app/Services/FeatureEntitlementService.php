<?php

namespace Modules\Saas\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Domain\Billing\SubscriptionStatus;
use Modules\Saas\Domain\Entitlements\EntitlementSnapshot;
use Modules\Saas\Exceptions\EntitlementDenied;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlanFeature;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantEntitlement;

/**
 * Checks whether the active tenant's plan includes a feature.
 *
 * Resolution order (plan §8):
 *  1. Tenant-local entitlement override (saas_tenant_entitlements)
 *  2. Plan features (saas_plan_features for the subscription's plan version)
 *  3. Deny by default
 *
 * The effective snapshot is cached by tenant UUID and invalidated from
 * billing webhooks, plan changes, and manual overrides.
 */
class FeatureEntitlementService implements EntitlementChecker
{
    /**
     * Subscription statuses that still grant access.
     *
     * `past_due` and `grace` are included deliberately: plan §8 and §12 require
     * a payment failure to degrade to a grace/read-only state, not to cut a
     * school off from its own records mid-term.
     */
    public function __construct(
        private readonly CurrentTenant $tenant,
        private readonly Cache $cache,
    ) {}

    public function has(string $featureCode): bool
    {
        $context = $this->tenant->get();

        if ($context === null) {
            return false;
        }

        return $this->valueSnapshot($context->uuid)->has($featureCode);
    }

    public function remaining(string $featureCode): ?int
    {
        $context = $this->tenant->get();

        if ($context === null) {
            return 0;
        }

        return $this->valueSnapshot($context->uuid)->remaining($featureCode);
    }

    public function ensure(string $featureCode): void
    {
        if (! $this->has($featureCode)) {
            throw EntitlementDenied::forFeature($featureCode, $this->tenant->uuid());
        }
    }

    public function currentPlanId(): ?string
    {
        $context = $this->tenant->get();

        if ($context === null) {
            return null;
        }

        return $this->valueSnapshot($context->uuid)->planCode;
    }

    public function flushCache(string $tenantUuid): void
    {
        $this->cache->forget($this->cacheKey($tenantUuid));
    }

    /**
     * Load or build the effective entitlement snapshot for a tenant.
     */
    public function snapshot(string $tenantUuid): array
    {
        return $this->valueSnapshot($tenantUuid)->toArray();
    }

    private function valueSnapshot(string $tenantUuid): EntitlementSnapshot
    {
        $data = $this->cache->remember(
            $this->cacheKey($tenantUuid),
            max(1, (int) config('saas.entitlements.cache_ttl', 300)),
            fn () => $this->buildSnapshot($tenantUuid)
        );

        $data['cached_at'] = now()->toIso8601String();

        return EntitlementSnapshot::fromArray($data);
    }

    /**
     * Resolve the effective entitlements for a tenant (plan §8).
     *
     * Order, most specific wins:
     *   1. saas_tenant_entitlements — per-tenant override, within its validity
     *      window. Covers negotiated terms and temporary grants.
     *   2. saas_plan_features       — the features of the plan version the
     *      tenant's live subscription points at.
     *   3. deny
     *
     * Note this reads the plan version recorded on the SUBSCRIPTION, not the
     * newest version of that plan. Republishing a plan must not silently
     * rewrite an existing customer's contract (plan §8).
     */
    private function buildSnapshot(string $tenantUuid): array
    {
        $tenant = Tenant::query()->where('uuid', $tenantUuid)->first();

        if ($tenant === null) {
            return ['plan_id' => null, 'features' => []];
        }

        $subscription = Subscription::query()
            ->where('tenant_uuid', $tenantUuid)
            ->whereIn('status', SubscriptionStatus::entitlingValues())
            ->orderByDesc('id')
            ->first();

        $features = [];
        $planCode = null;

        if ($subscription !== null) {
            $plan = Plan::query()->find($subscription->plan_id);
            $planCode = $plan?->plan_code;

            $planFeatures = PlanFeature::query()
                ->where('plan_id', $subscription->plan_id)
                ->get();

            foreach ($planFeatures as $feature) {
                $features[$feature->feature_code] = [
                    'enabled' => (bool) $feature->enabled,
                    'limit' => $feature->limit_value,
                    'source' => 'plan',
                ];
            }
        }

        // Overrides last so they win, but only while actually in effect.
        $now = now();

        $overrides = TenantEntitlement::query()
            ->where('tenant_uuid', $tenantUuid)
            ->inEffect($now)
            ->get();

        foreach ($overrides as $override) {
            $features[$override->feature_code] = [
                'enabled' => (bool) $override->enabled,
                'limit' => $override->limit_value,
                'source' => 'override',
            ];
        }

        return ['plan_id' => $planCode, 'features' => $features];
    }

    /*
     * There was a developmentSnapshot() here that enabled every feature when
     * the environment was local or testing. It is deliberately gone: it meant
     * the entitlement layer was never actually exercised by any test, and the
     * first time it ran for real would have been in production. Seed plans via
     * PlanSeeder instead, so development runs the same code path as production.
     */

    private function cacheKey(string $tenantUuid): string
    {
        return "saas:entitlements:{$tenantUuid}";
    }
}
