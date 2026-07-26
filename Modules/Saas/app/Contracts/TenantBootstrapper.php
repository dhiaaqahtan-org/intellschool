<?php

namespace Modules\Saas\Contracts;

use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * One piece of per-tenant runtime state.
 *
 * The database connection is the obvious one, but it is not the only shared
 * global that leaks across tenants: cache keys, filesystem roots, log context,
 * and queue payloads all do too (plan §7).
 *
 * Every bootstrapper must be symmetric. If bootstrap() mutates a global,
 * revert() must put it back exactly — otherwise a long-lived worker
 * accumulates state from whichever tenant it happened to serve first.
 */
interface TenantBootstrapper
{
    public function bootstrap(TenantContext $context): void;

    /**
     * Must be safe to call when bootstrap() was never called, and must not
     * throw — it runs in finally blocks.
     */
    public function revert(): void;
}
