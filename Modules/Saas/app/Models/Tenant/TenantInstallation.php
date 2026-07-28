<?php

namespace Modules\Saas\Models\Tenant;

/**
 * Self-identification and cached control-plane state inside one tenant DB.
 */
class TenantInstallation extends TenantModel
{
    protected $table = 'tenant_installations';

    protected $fillable = [
        'tenant_uuid',
        'tenant_slug',
        'schema_version',
        'app_version',
        'provisioned_at',
        'last_migrated_at',
        'entitlements_snapshot',
        'entitlements_synced_at',
        'access_state',
        'access_message',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
        'last_migrated_at' => 'datetime',
        'entitlements_snapshot' => 'array',
        'entitlements_synced_at' => 'datetime',
    ];
}
