<?php

namespace Modules\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Applies the tenant resolved by ResolveTenant to process-wide state.
 *
 * Resolution and initialization are intentionally separate. ResolveTenant
 * only classifies the trusted host and builds an immutable context; this
 * middleware owns connection, cache, filesystem, locale and timezone state.
 */
class InitializeTenant
{
    private ?string $originalLocale = null;

    private ?string $originalTimezone = null;

    public function __construct(
        private readonly CurrentTenant $tenant,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('saas.tenancy.enabled', false)) {
            $this->tenant->forget();

            return $next($request);
        }

        $context = $request->attributes->get('saas.tenant_context');

        if (! $context instanceof TenantContext) {
            // A control-plane request must never inherit tenant state from a
            // previous request handled by the same long-lived worker.
            $this->tenant->forget();

            return $next($request);
        }

        $this->originalLocale = app()->getLocale();
        $this->originalTimezone = date_default_timezone_get();

        try {
            $this->tenant->set($context);
            app()->setLocale($context->locale);
            date_default_timezone_set($context->timezone);

            return $next($request);
        } catch (Throwable $exception) {
            $this->resetProcessState();

            throw $exception;
        }
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->resetProcessState();
    }

    private function resetProcessState(): void
    {
        $this->tenant->forget();

        if ($this->originalLocale !== null) {
            app()->setLocale($this->originalLocale);
        }

        if ($this->originalTimezone !== null) {
            date_default_timezone_set($this->originalTimezone);
        }

        $this->originalLocale = null;
        $this->originalTimezone = null;
    }
}
