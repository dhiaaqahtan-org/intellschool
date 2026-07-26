<?php

namespace Modules\Saas\Jobs\Middleware;

use Illuminate\Queue\Events\JobFailed;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;
use Throwable;

/**
 * Queue middleware that initializes and clears tenant context for every job.
 *
 * Apply to any job that touches tenant data:
 *
 *   public function middleware(): array
 *   {
 *       return [new TenantAwareJob()];
 *   }
 *
 * The job MUST carry a `tenant_uuid` property. This middleware:
 *  1. Resolves the full tenant context from the landlord database.
 *  2. Swaps the default connection, cache prefix, and filesystem root.
 *  3. Validates the tenant is still servable.
 *  4. Clears ALL state in a finally block, even on exception.
 *
 * Without step 4, a long-lived Horizon worker carries tenant A's PDO handle
 * into tenant B's job — the exact cross-tenant breach this module exists to
 * prevent (plan §7, §Phase 8).
 */
class TenantAwareJob
{
    /**
     * Process the job inside tenant context.
     *
     * @param  mixed  $job  The job instance.
     * @param  callable  $next
     */
    public function handle(mixed $job, callable $next): void
    {
        $tenantUuid = $this->resolveTenantUuid($job);

        if ($tenantUuid === null) {
            // Job does not declare a tenant — run without context.
            // This is valid for landlord/platform jobs (e.g. reconciliation).
            $next($job);

            return;
        }

        /** @var CurrentTenant $currentTenant */
        $currentTenant = app(CurrentTenant::class);

        $tenant = Tenant::query()
            ->with('database')
            ->where('uuid', $tenantUuid)
            ->first();

        if ($tenant === null) {
            // Tenant was deleted. Fail the job rather than running it
            // against an unknown database.
            throw new \RuntimeException(
                "TenantAwareJob: tenant [{$tenantUuid}] not found. Job cannot proceed."
            );
        }

        if (! $tenant->isServable()) {
            // Tenant is terminated or still provisioning. Release the job
            // back to the queue — it may become servable later, or the
            // retry limit will eventually bury it.
            if (method_exists($job, 'release')) {
                $job->release(300); // retry in 5 minutes

                return;
            }

            throw new \RuntimeException(
                "TenantAwareJob: tenant [{$tenantUuid}] is not servable (status: {$tenant->status->value})."
            );
        }

        $host = $tenant->primaryDomain()?->hostname ?? $tenant->slug.'.queue';
        $context = $tenant->toContext($host);

        // Set locale/timezone for the job execution.
        app()->setLocale($context->locale);
        date_default_timezone_set($context->timezone);

        try {
            $currentTenant->set($context);
            $next($job);
        } finally {
            // ALWAYS clear, even if the job threw. A worker that survives
            // an exception must not carry this tenant's state into the
            // next job, which might belong to a different tenant.
            $currentTenant->forget();
        }
    }

    /**
     * Called when the job fails permanently. Ensure cleanup happened.
     */
    public function failed(mixed $job, Throwable $exception): void
    {
        /** @var CurrentTenant $currentTenant */
        $currentTenant = app(CurrentTenant::class);

        if ($currentTenant->has()) {
            $currentTenant->forget();
        }
    }

    /**
     * Extract the tenant UUID from the job. Supports:
     *  - A public $tenantUuid property
     *  - A tenantUuid() method
     *  - A $tenantUuid key in a serialized payload
     */
    private function resolveTenantUuid(mixed $job): ?string
    {
        if (property_exists($job, 'tenantUuid')) {
            return $job->tenantUuid;
        }

        if (method_exists($job, 'tenantUuid')) {
            return $job->tenantUuid();
        }

        return null;
    }
}
