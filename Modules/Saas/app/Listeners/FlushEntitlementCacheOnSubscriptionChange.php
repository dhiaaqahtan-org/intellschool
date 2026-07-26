<?php

namespace Modules\Saas\Listeners;

use Modules\Saas\Events\SubscriptionStateChanged;
use Modules\Saas\Contracts\EntitlementChecker;

/**
 * When a subscription changes state, the cached entitlement snapshot is
 * stale. Flush it so the next request rebuilds from the new plan features.
 */
class FlushEntitlementCacheOnSubscriptionChange
{
    public function __construct(
        private readonly EntitlementChecker $entitlements,
    ) {
    }

    public function handle(SubscriptionStateChanged $event): void
    {
        $this->entitlements->flushCache($event->tenantUuid);
    }
}
