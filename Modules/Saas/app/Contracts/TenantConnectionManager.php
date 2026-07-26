<?php

namespace Modules\Saas\Contracts;

use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * Owns the database connection swap.
 *
 * Implementations must:
 *  - build connection config from landlord metadata plus a credential
 *    resolver, never from request input;
 *  - purge and reconnect so no PDO handle is reused across tenants;
 *  - make the tenant connection the DEFAULT, so existing ERP models that
 *    declare no connection land on it without modification;
 *  - restore the original default on release, including after an exception.
 */
interface TenantConnectionManager
{
    public function connect(TenantContext $context): void;

    /**
     * Tear down the tenant connection and restore the prior default.
     * Must be safe to call when nothing was connected.
     */
    public function release(): void;

    public function connectionName(): string;

    /**
     * Is a tenant connection currently the default? Used by guards that need
     * to assert the request is not about to query the wrong database.
     */
    public function isConnected(): bool;
}
