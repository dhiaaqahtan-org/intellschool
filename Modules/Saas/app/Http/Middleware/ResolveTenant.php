<?php

namespace Modules\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Saas\Services\TenantResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves a trusted request host to an immutable tenant context.
 *
 * MUST run in the GLOBAL middleware stack, immediately after TrustProxies and
 * before everything else — session, CSRF, SubstituteBindings, authentication
 * and App\Http\Middleware\UserConfig (plan §6).
 *
 * Ordering matters concretely:
 *  - before StartSession, so the session cookie is read in tenant context;
 *  - before SubstituteBindings, or route-model binding queries the previous
 *    default connection and returns another tenant's record;
 *  - before authentication, so the user provider looks the user up in the
 *    right database;
 *  - before UserConfig, which loads teams and academic periods and would
 *    otherwise read the wrong database.
 *
 * This middleware is intentionally permissive about *not* finding a tenant —
 * marketing and platform hosts legitimately have none. Routes that require a
 * tenant enforce that with RequireTenantHost. What it is not permissive about
 * is a tenant host that fails to resolve: that fails closed with a 404.
 */
class ResolveTenant
{
    public function __construct(
        private readonly TenantResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Kill switch. Lets the middleware stay permanently registered in the
        // kernel while the application is still running single-tenant, rather
        // than being commented out — a commented-out isolation control is one
        // careless merge away from disappearing entirely.
        if (! config('saas.tenancy.enabled', false)) {
            $request->attributes->set('saas.tenant_context', null);

            return $next($request);
        }

        // getHost() is only trustworthy because TrustProxies has already run.
        // If this middleware is ever moved above it, X-Forwarded-Host becomes
        // attacker-controlled and tenant selection with it.
        $classification = $this->resolver->classify($request->getHost());

        if ($classification->isInvalid()) {
            throw new NotFoundHttpException('Unrecognised host.');
        }

        if ($classification->isControlPlane()) {
            // No tenant, and — importantly — no tenant connection left over
            $request->attributes->set('saas.control_plane', true);
            // from a previous request on this worker.
            $request->attributes->set('saas.tenant_context', null);

            return $next($request);
        }

        $context = $this->resolver->resolve($classification->host);
        $request->attributes->set('saas.control_plane', false);

        if ($context === null) {
            // Unknown, unverified, suspended-beyond-service or still
            // provisioning. Never fall back to a default tenant.
            throw new NotFoundHttpException('No site is configured for this address.');
        }

        // InitializeTenant consumes this object without another landlord query.
        $request->attributes->set('saas.tenant_context', $context);

        return $next($request);
    }
}
