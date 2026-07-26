<?php

namespace Modules\Saas\Services;

use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Exceptions\TenantNotResolved;

/**
 * Guards against tenant context drift and misuse (plan §4, §6).
 *
 * This service provides assertions and checks that can be called at critical
 * points in the request/job lifecycle to verify:
 *
 *  - A tenant context exists when one is required.
 *  - The active context matches an expected tenant.
 *  - No context is active when running landlord-only operations.
 *  - The context has not been corrupted or swapped unexpectedly.
 *
 * Usage in middleware, controllers, jobs, and service decorators:
 *
 *   $guard->assertActive();
 *   $guard->assertIs($expectedUuid);
 *   $guard->assertNone(); // for landlord operations
 */
class TenantContextGuard
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
    ) {
    }

    /**
     * Assert that a tenant context is currently active.
     *
     * @throws TenantNotResolved when no tenant is active.
     */
    public function assertActive(): TenantContext
    {
        return $this->currentTenant->getOrFail();
    }

    /**
     * Assert that the active tenant matches the expected UUID.
     *
     * Use this before operations that reference a specific tenant to prevent
     * cross-tenant access due to a stale or swapped context.
     *
     * @throws TenantNotResolved when no tenant is active.
     * @throws \LogicException when the active tenant does not match.
     */
    public function assertIs(string $expectedUuid): TenantContext
    {
        $context = $this->currentTenant->getOrFail();

        if (! hash_equals($context->uuid, $expectedUuid)) {
            throw new \LogicException(
                "Tenant context mismatch: expected [{$expectedUuid}], "
                ."active [{$context->uuid}]. Possible cross-tenant access attempt."
            );
        }

        return $context;
    }

    /**
     * Assert that NO tenant context is active.
     *
     * Use this in landlord/control-plane operations that must never
     * accidentally read through a tenant connection.
     *
     * @throws \LogicException when a tenant context IS active.
     */
    public function assertNone(): void
    {
        if ($this->currentTenant->has()) {
            $context = $this->currentTenant->get();

            throw new \LogicException(
                "Expected no tenant context, but [{$context?->uuid}] is active. "
                .'Landlord operations must run without a tenant context.'
            );
        }
    }

    /**
     * Assert that the active tenant can perform write operations.
     *
     * A suspended or read-only tenant should not be able to write.
     *
     * @throws TenantNotResolved when no tenant is active.
     * @throws \Modules\Saas\Exceptions\EntitlementDenied when writes are blocked.
     */
    public function assertCanWrite(): TenantContext
    {
        $context = $this->currentTenant->getOrFail();

        if (! $context->canWrite()) {
            throw \Modules\Saas\Exceptions\EntitlementDenied::withMessage(
                'tenant.write',
                "Tenant [{$context->slug}] is in {$context->status->value} state and cannot write."
            );
        }

        return $context;
    }

    /**
     * Assert that the active tenant is fully active (not trialing, suspended, etc.).
     *
     * @throws TenantNotResolved when no tenant is active.
     * @throws \Modules\Saas\Exceptions\EntitlementDenied when tenant is not active.
     */
    public function assertActiveStatus(): TenantContext
    {
        $context = $this->currentTenant->getOrFail();

        if (! $context->isActive()) {
            throw \Modules\Saas\Exceptions\EntitlementDenied::withMessage(
                'tenant.active',
                "Tenant [{$context->slug}] is not active (status: {$context->status->value})."
            );
        }

        return $context;
    }

    /**
     * Check if a tenant context is active (non-throwing).
     */
    public function hasActive(): bool
    {
        return $this->currentTenant->has();
    }

    /**
     * Get the active tenant UUID or null.
     */
    public function activeUuid(): ?string
    {
        return $this->currentTenant->uuid();
    }

    /**
     * Verify context integrity by checking that the connection name
     * matches what we expect for the active tenant.
     *
     * This catches bugs where the context says one tenant but the
     * database connection points at another.
     */
    public function verifyConnectionIntegrity(): bool
    {
        $context = $this->currentTenant->get();

        if ($context === null) {
            return true; // No context, no connection to verify.
        }

        $expectedConnection = config('saas.database.tenant_connection', 'tenant');
        $defaultConnection = config('database.default');

        // During a tenant request, the default connection should be the
        // tenant connection, not the original default.
        return $defaultConnection === $expectedConnection;
    }

    /**
     * Run a callback with context verification before and after.
     *
     * Useful for critical operations where context drift would be catastrophic.
     *
     * @template T
     * @param  \Closure(): T  $callback
     * @return T
     */
    public function withVerification(\Closure $callback): mixed
    {
        $contextBefore = $this->currentTenant->get();

        $result = $callback();

        $contextAfter = $this->currentTenant->get();

        // Verify the context was not changed by the callback.
        if ($contextBefore === null && $contextAfter !== null) {
            throw new \LogicException(
                'Tenant context was set during a verified operation. '
                .'This may indicate a context leak.'
            );
        }

        if ($contextBefore !== null && ! $contextBefore->is($contextAfter)) {
            $afterUuid = $contextAfter?->uuid ?? 'null';

            throw new \LogicException(
                "Tenant context changed during a verified operation: "
                ."{$contextBefore->uuid} → {$afterUuid}."
            );
        }

        return $result;
    }
}
