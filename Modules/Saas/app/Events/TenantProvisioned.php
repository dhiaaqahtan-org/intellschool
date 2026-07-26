<?php

namespace Modules\Saas\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a tenant completes provisioning and becomes ready to serve.
 */
class TenantProvisioned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $tenantUuid,
        public readonly string $slug,
        public readonly string $databaseName,
    ) {
    }
}
