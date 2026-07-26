<?php

namespace Modules\Saas\Listeners;

use Modules\Saas\Events\TenantStatusChanged;
use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Services\TenantResolver;

/**
 * When a tenant's status changes (suspended, cancelled, terminated), flush
 * the host-resolution cache for ALL of its domains so the change takes
 * effect immediately rather than waiting for the TTL to expire.
 */
class FlushResolutionCacheOnStatusChange
{
    public function __construct(
        private readonly TenantResolver $resolver,
    ) {
    }

    public function handle(TenantStatusChanged $event): void
    {
        $domains = TenantDomain::query()
            ->where('tenant_uuid', $event->tenantUuid)
            ->pluck('hostname');

        foreach ($domains as $hostname) {
            $this->resolver->forget($hostname);
        }
    }
}
