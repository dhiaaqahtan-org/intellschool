<?php

namespace Modules\Saas\Listeners;

use Modules\Saas\Events\TenantProvisioned;
use Modules\Saas\Models\Landlord\AuditEvent;

/**
 * Records an immutable audit entry when a tenant finishes provisioning.
 */
class RecordAuditOnTenantProvisioned
{
    public function handle(TenantProvisioned $event): void
    {
        AuditEvent::record(
            action: 'tenant.provisioned',
            tenantUuid: $event->tenantUuid,
            context: [
                'slug' => $event->slug,
                'database_name' => $event->databaseName,
            ],
            actorType: 'system',
        );
    }
}
