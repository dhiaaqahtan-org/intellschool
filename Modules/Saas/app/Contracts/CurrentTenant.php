<?php

namespace Modules\Saas\Contracts;

use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * The only sanctioned way for core ERP code to learn which tenant it is in.
 *
 * Legacy controllers and services must depend on this contract, never on
 * Modules\Saas\Models\Landlord\Tenant directly (plan §3.3). That keeps the
 * module boundary intact and means the resolution strategy can change without
 * touching 693 controllers.
 */
interface CurrentTenant
{
    public function get(): ?TenantContext;

    /**
     * @throws \Modules\Saas\Exceptions\TenantNotResolved when no tenant is active.
     */
    public function getOrFail(): TenantContext;

    public function has(): bool;

    public function uuid(): ?string;

    /**
     * Replace the active context. Callers should prefer runFor()/forget() so
     * that restoration is guaranteed.
     */
    public function set(TenantContext $context): void;

    public function forget(): void;

    /**
     * Run a callback inside a tenant, restoring the previous context
     * afterwards even if the callback throws.
     *
     * @template T
     * @param  \Closure():T  $callback
     * @return T
     */
    public function runFor(TenantContext $context, \Closure $callback): mixed;

    /**
     * Run a callback with NO tenant active — for landlord/control-plane work
     * that must never read through a tenant connection.
     *
     * @template T
     * @param  \Closure():T  $callback
     * @return T
     */
    public function runWithout(\Closure $callback): mixed;
}
