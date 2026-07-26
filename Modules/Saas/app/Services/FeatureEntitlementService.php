<?php

namespace Modules\Saas\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Exceptions\EntitlementDenied;
use Modules\Saas\Models\Landlord\Tenant;

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
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly CurrentTenant $tenant,
        private readonly Cache $cache,
    ) {
    }

    public function has(string $featureCode): bool
    {
        $context = $this->tenant->get();

        if ($context === null) {
            return false;
        }

        $snapshot = $this->snapshot($context->uuid);

        return ($snapshot['features'][$featureCode]['enabled'] ?? false) === true;
    }

    public function remaining(string $featureCode): ?int
    {
        $context = $this->tenant->get();

        if ($context === null) {
            return 0;
        }

        $snapshot = $this->snapshot($context->uuid);
        $feature = $snapshot['features'][$featureCode] ?? null;

        if ($feature === null || ! ($feature['enabled'] ?? false)) {
            return 0;
        }

        // null limit means unlimited
        return $feature['limit'] ?? null;
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

        $snapshot = $this->snapshot($context->uuid);

        return $snapshot['plan_id'] ?? null;
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
        $data = $this->cache->remember(
            $this->cacheKey($tenantUuid),
            self::CACHE_TTL,
            fn () => $this->buildSnapshot($tenantUuid)
        );

        // Add metadata for API consumers.
        $data['cached_at'] = now()->toIso8601String();
        $data['plan_code'] = $data['plan_id'] ?? null;
        $data['limits'] = $this->extractLimits($data['features'] ?? []);

        return $data;
    }

    private function buildSnapshot(string $tenantUuid): array
    {
        $tenant = Tenant::query()
            ->with(['database'])
            ->where('uuid', $tenantUuid)
            ->first();

        if ($tenant === null) {
            return ['plan_id' => null, 'features' => []];
        }

        // TODO(Phase 6): Load from saas_subscriptions + saas_plan_features
        // + saas_tenant_entitlements once billing tables are populated.
        // For now, return a permissive development snapshot so the ERP
        // remains usable before billing is wired.
        if (app()->environment('local', 'testing')) {
            return $this->developmentSnapshot();
        }

        // Production: deny by default until billing is configured.
        return ['plan_id' => null, 'features' => []];
    }

    /**
     * In development, all features are enabled so the ERP remains testable
     * before billing infrastructure exists.
     */
    private function developmentSnapshot(): array
    {
        $features = [];
        $codes = [
            'students.core', 'students.admissions', 'students.attendance',
            'students.promotion', 'academics.classes', 'academics.subjects',
            'academics.timetable', 'exam.core', 'exam.grading',
            'finance.fees', 'finance.payroll', 'finance.expenses',
            'hr.employees', 'hr.leave', 'hr.attendance',
            'transport.routes', 'transport.vehicles',
            'library.core', 'inventory.core', 'hostel.core',
            'communication.sms', 'communication.email', 'communication.chat',
            'website.cms', 'mobile.offline', 'api.access',
            'reports.academic', 'reports.financial',
            'campuses.max', 'storage.gb',
        ];

        foreach ($codes as $code) {
            $features[$code] = ['enabled' => true, 'limit' => null];
        }

        return ['plan_id' => 'dev-unlimited', 'features' => $features];
    }

    private function cacheKey(string $tenantUuid): string
    {
        return "saas:entitlements:{$tenantUuid}";
    }

    /**
     * Extract limit values from features for API response.
     */
    private function extractLimits(array $features): array
    {
        $limits = [];

        foreach ($features as $code => $feature) {
            if (isset($feature['limit']) && $feature['limit'] !== null) {
                $limits[$code] = $feature['limit'];
            }
        }

        return $limits;
    }
}
