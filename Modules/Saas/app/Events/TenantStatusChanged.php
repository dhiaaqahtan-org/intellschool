<?php

namespace Modules\Saas\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Saas\Enums\TenantStatus;

/**
 * Fired when a tenant's lifecycle status changes (active → suspended, etc.).
 * Listeners flush the host-resolution cache so the change takes effect
 * immediately rather than waiting for the TTL to expire.
 */
class TenantStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $tenantUuid,
        public readonly TenantStatus $previousStatus,
        public readonly TenantStatus $newStatus,
        public readonly ?string $reason = null,
    ) {
    }
}
