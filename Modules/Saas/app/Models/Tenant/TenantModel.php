<?php

namespace Modules\Saas\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Modules\Saas\Contracts\CurrentTenant;

/**
 * Base class for SaaS module models whose records live inside one tenant DB.
 *
 * Resolving the current tenant is mandatory: a missing context must fail
 * before Eloquent can fall back to the landlord or legacy default database.
 */
abstract class TenantModel extends Model
{
    public function getConnectionName(): string
    {
        app(CurrentTenant::class)->getOrFail();

        return config('saas.database.tenant_connection', 'tenant');
    }
}
