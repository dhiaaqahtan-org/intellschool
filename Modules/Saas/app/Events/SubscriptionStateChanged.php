<?php

namespace Modules\Saas\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a subscription transitions state (trialing → active, active →
 * past_due, etc.). Listeners flush the entitlement cache so plan changes
 * take effect immediately.
 */
class SubscriptionStateChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $tenantUuid,
        public readonly string $previousState,
        public readonly string $newState,
        public readonly ?string $providerEventId = null,
    ) {
    }
}
