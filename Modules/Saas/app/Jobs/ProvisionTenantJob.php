<?php

namespace Modules\Saas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Models\Landlord\ProvisioningRun;
use Modules\Saas\Services\TenantProvisioner;

/**
 * Runs the control-plane provisioning state machine asynchronously.
 *
 * Only the immutable run UUID is serialized. Database credentials and tenant
 * connection details are resolved by the worker from the landlord database.
 * The job deliberately carries no tenantUuid property because provisioning a
 * pending tenant is control-plane work, not a tenant-data job.
 */
class ProvisionTenantJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $uniqueFor = 3600;

    public function __construct(public readonly string $runUuid)
    {
        $this->onQueue((string) config('saas.provisioning.queue', 'provisioning'));
    }

    public function uniqueId(): string
    {
        return $this->runUuid;
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [30, 120, 300, 900];
    }

    public function handle(TenantProvisioner $provisioner): void
    {
        $run = ProvisioningRun::query()->where('uuid', $this->runUuid)->firstOrFail();

        if ($run->state === ProvisioningState::Ready) {
            return;
        }

        if ($run->state === ProvisioningState::FailedManualReview) {
            return;
        }

        $provisioner->provision($run);
    }

    public function tags(): array
    {
        return ['saas', 'provisioning', 'run:'.$this->runUuid];
    }
}
