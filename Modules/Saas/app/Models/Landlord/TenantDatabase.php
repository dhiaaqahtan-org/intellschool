<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $cluster
 * @property string $database_name
 * @property string $secret_ref  Pointer to a secret. NEVER the secret itself.
 */
class TenantDatabase extends LandlordModel
{
    protected $table = 'saas_tenant_databases';

    protected $fillable = [
        'tenant_uuid', 'cluster', 'database_name', 'secret_ref',
        'schema_version', 'app_version', 'health_status',
        'last_checked_at', 'last_migrated_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'last_migrated_at' => 'datetime',
    ];

    /**
     * `secret_ref` is a pointer, but it still identifies infrastructure, so
     * keep it out of arrays, API resources, logs and exception payloads.
     */
    protected $hidden = ['secret_ref'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    /**
     * Deterministic database name derived from the tenant UUID.
     *
     * Derived, never supplied. A request-controlled database name is the most
     * direct route into another tenant's data, so the provisioner calls this
     * and nothing else may set the column.
     *
     * MySQL identifiers cap at 64 characters; the hash keeps it well inside.
     */
    public static function nameFor(string $tenantUuid): string
    {
        $prefix = config('saas.database.tenant_prefix', 'tnt_');
        $digest = substr(hash('sha256', $tenantUuid), 0, 32);

        return $prefix.$digest;
    }
}
