<?php

namespace Modules\Saas\Queue;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Support\Facades\Queue;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;
use Throwable;

/**
 * Automatic tenant propagation through the queue.
 *
 * TenantAwareJob (the queue middleware) is opt-in: a job only gets tenant
 * context if someone remembered to add `middleware()` to it. This application
 * has ~70 job classes plus notifications, mailables, listeners and exports, so
 * opt-in guarantees that sooner or later one is missed — and a missed job does
 * not fail loudly, it runs against whatever tenant the worker happened to
 * serve last. That is a silent cross-tenant write.
 *
 * This class makes propagation automatic instead:
 *
 *   dispatch  -> stamp the active tenant UUID onto EVERY payload
 *   process   -> rebuild the context from the landlord before the job runs
 *   finish    -> tear the context down, on success, failure and exception
 *
 * Jobs dispatched with no tenant active (control-plane work) carry no UUID and
 * run with no context, which is correct.
 */
class QueueTenancy
{
    public function __construct(
        private readonly CurrentTenant $tenant,
    ) {
    }

    public function register(Dispatcher $events): void
    {
        $this->stampPayloads();
        $this->bindWorkerEvents($events);
    }

    /**
     * Stamp the active tenant onto every job payload at dispatch time.
     *
     * Only the UUID travels. The database name, credentials and connection
     * details are re-derived from the landlord by the worker, so a tampered or
     * replayed payload cannot point a job at an arbitrary database.
     */
    private function stampPayloads(): void
    {
        Queue::createPayloadUsing(function () {
            $uuid = $this->tenant->uuid();

            return $uuid === null ? [] : ['tenant_uuid' => $uuid];
        });
    }

    private function bindWorkerEvents(Dispatcher $events): void
    {
        $events->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->enterTenant($this->uuidFromPayload($event->job->payload()));
        });

        // Every terminal path must clear. Missing any one of these leaves the
        // worker holding an open PDO handle and a cache prefix belonging to
        // the tenant whose job just ended.
        foreach ([JobProcessed::class, JobFailed::class, JobExceptionOccurred::class, JobReleasedAfterException::class] as $event) {
            $events->listen($event, fn () => $this->leaveTenant());
        }
    }

    private function uuidFromPayload(array $payload): ?string
    {
        $uuid = $payload['tenant_uuid'] ?? null;

        return is_string($uuid) && $uuid !== '' ? $uuid : null;
    }

    private function enterTenant(?string $uuid): void
    {
        // Always start from a clean slate — never inherit the previous job's
        // context, even for a job that declares no tenant.
        $this->leaveTenant();

        if ($uuid === null) {
            return;
        }

        $tenant = Tenant::query()->with('database')->where('uuid', $uuid)->first();

        if ($tenant === null || ! $tenant->isServable()) {
            // Do not run the job against no tenant, and do not guess. Throwing
            // sends it to the failed-jobs table where it can be inspected.
            throw new \RuntimeException(
                "Queued job references tenant [{$uuid}], which is missing or not servable."
            );
        }

        $host = $tenant->primaryDomain()?->hostname ?? $tenant->slug;

        $this->tenant->set($tenant->toContext($host));
    }

    private function leaveTenant(): void
    {
        try {
            if ($this->tenant->has()) {
                $this->tenant->forget();
            }
        } catch (Throwable $e) {
            // Teardown must never mask the job's own outcome, but a failure
            // here means state may be stranded on this worker — surface it.
            report($e);
        }
    }
}
