<?php

namespace Modules\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Domain\Entitlements\FeatureCode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level plan enforcement (plan §8).
 *
 * Usage: ->middleware('saas.entitlement:students.core')
 *
 * This is the coarse gate. Fine-grained capacity checks (remaining seats,
 * storage bytes) happen inside services via EntitlementChecker::remaining().
 * Hiding a menu entry is NOT enforcement; this middleware and the service
 * calls together form the actual boundary.
 */
class RequireEntitlement
{
    public function __construct(
        private readonly EntitlementChecker $entitlements,
    ) {}

    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        $feature = FeatureCode::tryFrom($featureCode);
        abort_if($feature === null, 404);

        // ensure() throws EntitlementDenied (402) when the plan lacks the feature.
        $this->entitlements->ensure($feature->value);

        return $next($request);
    }
}
