<?php

namespace Modules\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Saas\Contracts\CurrentTenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks writes for a tenant that is suspended or cancelled.
 *
 * The plan is explicit (§12): a payment failure must never delete or corrupt
 * school data. It degrades to read-only, keeps billing and export reachable,
 * and shows an actionable banner.
 *
 * So this checks the HTTP method rather than blocking the tenant outright:
 * GET/HEAD/OPTIONS keep working, state-changing verbs get 423 Locked.
 */
class EnsureTenantActive
{
    /**
     * Routes that must keep working while suspended, so the customer can
     * actually fix the problem. Matched against the route name.
     */
    private const ALWAYS_ALLOWED = [
        'billing.*',
        'saas.tenant.billing.*',
        'subscription.*',
        'export.*',
        'auth.logout',
        'logout',
    ];

    public function __construct(
        private readonly CurrentTenant $tenant,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = $this->tenant->get();

        // No tenant here at all — that is RequireTenantHost's job, not ours.
        if ($context === null || $context->canWrite()) {
            return $next($request);
        }

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        if ($this->isAlwaysAllowed($request)) {
            return $next($request);
        }

        $message = __('saas::tenancy.read_only');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'tenant_status' => $context->status->value,
            ], Response::HTTP_LOCKED);
        }

        return back()->withInput()->with('saas_tenant_error', $message);
    }

    private function isAlwaysAllowed(Request $request): bool
    {
        $name = $request->route()?->getName();

        if ($name === null) {
            return false;
        }

        foreach (self::ALWAYS_ALLOWED as $pattern) {
            if (fnmatch($pattern, $name)) {
                return true;
            }
        }

        return false;
    }
}
