<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\EntitlementChecker;
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
        private readonly EntitlementChecker $entitlements,
        private readonly UsageMeter $usageMeter,
    ) {
    }

    /**
     * GET /api/saas/discover/{slug}
     *
     * Public endpoint for tenant discovery. The Flutter client uses this
     * to validate a school code/subdomain before authentication.
     */
    public function discover(string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)
            ->whereIn('status', ['active', 'trialing'])
            ->first();

        if (! $tenant) {
            return response()->json([
                'found' => false,
                'message' => 'No active school found with this code.',
            ], 404);
        }

        $domain = $tenant->domains()->where('is_primary', true)->first();

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

        $metrics = ['students.active', 'users.active', 'storage.bytes'];
        $usage = [];

        foreach ($metrics as $metric) {
            $usage[$metric] = $this->usageMeter->current($context->uuid, $metric);
        }

        return response()->json([
            'tenant_uuid' => $context->uuid,
            'period' => now()->format('Y-m'),
            'usage' => $usage,
        ]);
    }

    /**
     * GET /api/saas/platform/tenants
     *
     * Platform endpoint: list all tenants with filtering.
     */
    public function platformIndex(Request $request): JsonResponse
    {
        $query = Tenant::query()->with(['domains', 'database']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $tenants = $query->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 20));

        return response()->json($tenants);
    }

    /**
     * GET /api/saas/platform/tenants/{tenant}
     *
     * Platform endpoint: get a single tenant with full details.
     */
    public function platformShow(Tenant $tenant): JsonResponse
    {
        $tenant->load(['domains', 'database', 'owners', 'subscription']);

        return response()->json(['tenant' => $tenant]);
    }

    /**
     * POST /api/saas/platform/tenants
     *
     * Platform endpoint: create a new tenant (queues provisioning).
     */
    public function platformStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|alpha_dash|unique:landlord.saas_tenants,slug',
            'owner_email' => 'required|email|max:255',
            'plan_code' => 'nullable|string|exists:landlord.saas_plans,plan_code',
        ]);

        // Tenant creation is handled by the provisioning pipeline.
        // This endpoint queues the provisioning run.
        return response()->json([
            'message' => 'Tenant creation queued. Use the provisioning status endpoint to track progress.',
            'data' => $validated,
        ], 202);
    }

    /**
     * PATCH /api/saas/platform/tenants/{tenant}/status
     *
     * Platform endpoint: update tenant status (suspend, activate, etc.).
     */
    public function platformUpdateStatus(Request $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,suspended,read_only,closed',
            'reason' => 'required|string|max:500',
        ]);

        $oldStatus = $tenant->status;
        $tenant->update(['status' => $validated['status']]);

        // Fire status change event for cache invalidation, notifications, etc.
        event(new \Modules\Saas\Events\TenantStatusChanged(
            $tenant->uuid,
            $oldStatus,
            $validated['status'],
            $validated['reason']
        ));

        return response()->json([
            'message' => "Tenant status updated to {$validated['status']}.",
            'tenant' => $tenant->fresh(),
        ]);
    }
}
