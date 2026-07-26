<?php

namespace Modules\Saas\Contracts;

/**
 * Decides whether the active tenant's plan includes a product capability.
 *
 * Three separate decisions happen on a request (plan §8):
 *  1. Is this request inside the correct tenant? (ResolveTenant)
 *  2. May this user perform this action? (Spatie permissions)
 *  3. Does this tenant's plan include this capability? (THIS contract)
 *
 * Deny by default: if the entitlement context is missing, the answer is no.
 * Hiding a menu entry is NOT enforcement — this contract is checked at
 * service/API boundaries, inside transactions, before writes.
 */
interface EntitlementChecker
{
    /**
     * Does the active tenant have access to the given feature?
     *
     * @param  string  $featureCode  Stable dotted code, e.g. 'students.core',
     *                               'finance.fees', 'hr.payroll', 'mobile.offline'.
     */
    public function has(string $featureCode): bool;

    /**
     * Remaining capacity for a metered feature, or null if unlimited.
     *
     * @return int|null null means unlimited; 0 means exhausted.
     */
    public function remaining(string $featureCode): ?int;

    /**
     * Assert access or throw. Use in controllers/services where proceeding
     * without the feature would corrupt data or violate the contract.
     *
     * @throws \Modules\Saas\Exceptions\EntitlementDenied
     */
    public function ensure(string $featureCode): void;

    /**
     * The effective plan identifier for the active tenant, or null when
     * no tenant is resolved.
     */
    public function currentPlanId(): ?string;

    /**
     * Invalidate the cached entitlement snapshot for a tenant. Called from
     * billing webhooks, plan changes, and entitlement overrides.
     */
    public function flushCache(string $tenantUuid): void;

    /**
     * Get the full entitlement snapshot for a tenant.
     * Used by the Flutter/mobile client to discover available features.
     *
     * @return array{features: array, limits: array, plan_code: ?string, cached_at: ?string}
     */
    public function snapshot(string $tenantUuid): array;
}
