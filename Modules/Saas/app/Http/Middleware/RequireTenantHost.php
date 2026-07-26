<?php

namespace Modules\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Saas\Contracts\CurrentTenant;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Asserts a tenant is active before a route touches tenant data.
 *
 * Apply to every ERP route group. ResolveTenant already refuses to serve an
 * unknown tenant host, but this closes the other direction: an ERP route
 * reached on the marketing or platform host, where ResolveTenant legitimately
 * left no tenant in place and the default connection is the landlord.
 *
 * Without this, such a request would run ERP queries against the control-plane
 * database. Fail closed instead (plan §6 rule 1).
 */
class RequireTenantHost
{
    public function __construct(
        private readonly CurrentTenant $tenant,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenant->has()) {
            throw new NotFoundHttpException('This resource is only available on a school address.');
        }

        return $next($request);
    }
}
