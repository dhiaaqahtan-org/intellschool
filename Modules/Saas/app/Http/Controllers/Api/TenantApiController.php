<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Services\UsageMeter;

/**
 * API endpoints for tenant operations.
 *
 * Used by the Flutter/mobile client and platform integrations.
 */
class TenantApiController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
        private readonly UsageMeter $usageMeter,
    ) {}

    /**
     * GET /api/saas/discover/{slug}
     *
     * Public endpoint for tenant discovery. The Flutter client uses this
     * to validate a school code/subdomain before authentication.
     */
    public function discover(string $slug): JsonResponse
    {
        $tenant = Tenant::query()
            ->where('slug', $slug)
            ->where('status', TenantStatus::Active->value)
            ->where('provisioning_state', ProvisioningState::Ready->value)
            ->first();

        if (! $tenant) {
            return response()->json([
                'found' => false,
                'message' => 'No active school found with this code.',
            ], 404);
        }

        $domain = $tenant->domains()
            ->routable()
            ->orderByDesc('is_primary')
            ->first();

        return response()->json([
            'found' => true,
            'tenant' => [
                'uuid' => $tenant->uuid,
                'slug' => $tenant->slug,
                'name' => $tenant->display_name,
                'locale' => $tenant->locale ?? 'en',
                'timezone' => $tenant->timezone ?? 'UTC',
                'host' => $domain?->hostname,
            ],
        ]);
    }

    /**
     * GET /api/saas/tenant
     *
     * Returns the current tenant's configuration for the authenticated user.
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
        ]);
    }

    /**
     * GET /api/saas/tenant/usage
     *
     * Returns usage statistics for the current tenant.
     */
    public function usage(): JsonResponse
    {
        $context = $this->currentTenant->getOrFail();

        $metrics = ['active_students', 'active_staff', 'storage_bytes'];
        $usage = [];

        foreach ($metrics as $metric) {
            $usage[$metric] = $this->usageMeter->current($metric, now()->format('Y-m'));
        }

        return response()->json([
            'tenant_uuid' => $context->uuid,
            'period' => now()->format('Y-m'),
            'usage' => $usage,
        ]);
    }
}
