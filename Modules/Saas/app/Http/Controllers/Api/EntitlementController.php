<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;

/**
 * API endpoints for entitlement checking.
 *
 * The Flutter/mobile client uses these to discover which features
 * are available under the tenant's current plan.
 */
class EntitlementController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
        private readonly EntitlementChecker $entitlements,
    ) {
    }

    /**
     * GET /api/saas/tenant/entitlements
     *
     * Returns the full entitlement snapshot for the current tenant.
     */
    public function index(): JsonResponse
    {
        $context = $this->currentTenant->getOrFail();
        $snapshot = $this->entitlements->snapshot($context->uuid);

        return response()->json([
            'tenant_uuid' => $context->uuid,
            'plan' => $snapshot['plan_code'] ?? null,
            'features' => $snapshot['features'] ?? [],
            'limits' => $snapshot['limits'] ?? [],
            'cached_at' => $snapshot['cached_at'] ?? null,
        ]);
    }

    /**
     * GET /api/saas/tenant/entitlements/{featureCode}
     *
     * Check a specific feature entitlement.
     */
    public function check(string $featureCode): JsonResponse
    {
        $context = $this->currentTenant->getOrFail();

        $hasAccess = $this->entitlements->has($featureCode);
        $remaining = $this->entitlements->remaining($featureCode);

        return response()->json([
            'tenant_uuid' => $context->uuid,
            'feature' => $featureCode,
            'enabled' => $hasAccess,
            'remaining' => $remaining,
            'unlimited' => $remaining === null && $hasAccess,
        ]);
    }
}
