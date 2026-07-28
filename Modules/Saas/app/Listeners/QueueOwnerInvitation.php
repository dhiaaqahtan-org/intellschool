<?php

namespace Modules\Saas\Listeners;

use Modules\Saas\Events\TenantProvisioned;
use Modules\Saas\Jobs\SendOwnerInvitationJob;

class QueueOwnerInvitation
{
    public function handle(TenantProvisioned $event): void
    {
        if (config('saas.signup.owner_invitations_enabled', false)) {
            SendOwnerInvitationJob::dispatch($event->tenantUuid);
        }
    }
}
