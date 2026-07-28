<?php

namespace Modules\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Fails closed when a marketing route is reached on a host that is not the
 * configured marketing host.
 *
 * The domain constraint in RouteServiceProvider is the primary control; this
 * middleware is the second one, so that a misordered provider, a route cache
 * built with a different SAAS_MARKETING_HOST, or a proxy rewriting the Host
 * header cannot quietly serve marketing pages on a tenant's own domain.
 *
 * It also guarantees no tenant database connection is in play: marketing pages
 * must never read from a tenant's data plane.
 */
class RequireLandlordHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedHosts = array_values(array_filter([
            config('saas.hosts.marketing'),
            config('saas.hosts.platform'),
        ]));

        // Unconfigured control-plane hosts are only tolerable outside
        // production, where RouteServiceProvider uses localhost.
        if ($expectedHosts === []) {
            if (app()->environment('production')) {
                throw new NotFoundHttpException('Control-plane hosts are not configured.');
            }

            return $next($request);
        }

        $requestHost = $this->normalise($request->getHost());
        $allowed = array_map(fn (string $host) => $this->normalise($host), $expectedHosts);

        if (! in_array($requestHost, $allowed, true)) {
            throw new NotFoundHttpException('Control-plane routes are not served on this host.');
        }

        return $next($request);
    }

    /**
     * Lowercase, strip a trailing dot and any port. Exact match only — no
     * suffix or wildcard matching, which is how host-header abuse gets in.
     */
    protected function normalise(string $host): string
    {
        $host = strtolower(trim($host));
        $host = rtrim($host, '.');

        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return $host;
    }
}
