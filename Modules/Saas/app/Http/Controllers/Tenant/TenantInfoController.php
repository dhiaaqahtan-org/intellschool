<?php

namespace Modules\Saas\Http\Controllers\Tenant;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Contracts\TenantUrlGenerator;

/**
 * Provides tenant identity and entitlement information.
 *
 * The Flutter/mobile client calls these endpoints to:
 *  1. Validate that its stored tenant UUID/host matches the server.
 *  2. Discover which features are available under the current plan.
 *  3. Get the API base URL for subsequent requests.
 *
 * These endpoints run on the tenant's own host, after ResolveTenant has
 * initialized the context. They never expose landlord internals or other
 * tenants' data.
 */
class TenantInfoController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
        private readonly EntitlementChecker $entitlements,
        private readonly TenantUrlGenerator $urlGenerator,
    ) {
    }

    /**
     * GET /saas/info
     *
     * Returns the tenant's public identity and configuration.
     * Safe to call without authentication (used during tenant discovery).
     */
    public function show(): JsonResponse
    {
        $context = $this->currentTenant->getOrFail();

        return response()->json([
            'tenant' => [
                'uuid' => $context->uuid,
                'slug' => $context->slug,
                'status' => $context->status->value,
                'locale' => $context->locale,
                'timezone' => $context->timezone,
                'region' => $context->region,
            ],
            'urls' => [
                'base' => $this->urlGenerator->baseUrl(),
                'api' => $this->urlGenerator->apiBaseUrl(),
            ],
            'features' => [
                'locales' => config('saas.facts.locales', ['en', 'ar']),
            ],
        ]);
    }

    /**
     * GET /saas/entitlements
     *
     * Returns the effective entitlement snapshot for this tenant.
     * Requires authentication (the client must be logged in).
     */
    public function entitlements(): JsonResponse
    {
        $context = $this->currentTenant->getOrFail();
        $snapshot = $this->entitlements->snapshot($context->uuid);

        return response()->json([
            'tenant_uuid' => $context->uuid,
            'features' => $snapshot['features'] ?? [],
            'limits' => $snapshot['limits'] ?? [],
            'plan' => $snapshot['plan_code'] ?? null,
            'cached_at' => $snapshot['cached_at'] ?? null,
        ]);
    }
}
