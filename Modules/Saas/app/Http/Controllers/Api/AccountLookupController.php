<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Http\Requests\Api\AccountLookupRequest;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantOwner;

/**
 * POST /api/v1/auth/lookup  (control-plane host only)
 *
 * Pre-login account discovery for the Flutter client. Given an email, returns
 * the schools that identity can sign in to, each with the exact API base URL
 * the client should switch its Dio to (Approach A — dynamic baseURL).
 *
 * This is the ONLY central call in the connection design. Everything after it
 * — login, tokens, ERP data — happens on the tenant subdomain the client picks
 * from this list. No tenant database is touched here; ownership lives in the
 * landlord `saas_tenant_owners` table as an email reference.
 *
 * Privacy: the response shape is constant. An unknown email returns HTTP 200
 * with an empty `memberships` array — never a 404 — so the endpoint cannot be
 * used to enumerate which emails belong to which schools. It is additionally
 * rate limited at the route (throttle:10,1).
 */
class AccountLookupController extends Controller
{
    public function __invoke(AccountLookupRequest $request): JsonResponse
    {
        $email = $request->normalisedEmail();

        // Owners whose invitation was accepted and not later removed.
        $tenantUuids = TenantOwner::query()
            ->active()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->pluck('tenant_uuid')
            ->unique()
            ->values();

        if ($tenantUuids->isEmpty()) {
            return $this->respond([]);
        }

        // Only tenants that are live and finished provisioning are sign-in-able.
        $tenants = Tenant::query()
            ->whereIn('uuid', $tenantUuids)
            ->where('status', TenantStatus::Active->value)
            ->where('provisioning_state', ProvisioningState::Ready->value)
            ->with(['domains' => fn ($q) => $q->routable()->orderByDesc('is_primary')])
            ->get();

        $scheme = $request->isSecure() ? 'https' : 'http';

        $memberships = $tenants
            ->map(function (Tenant $tenant) use ($scheme) {
                $host = $tenant->domains->first()?->hostname;

                // A tenant with no routable domain cannot be reached yet; skip it
                // rather than hand the client an unusable base URL.
                if ($host === null) {
                    return null;
                }

                return [
                    'tenant_id'    => $tenant->uuid,
                    'school_name'  => $tenant->display_name,
                    'subdomain'    => $tenant->slug,
                    'api_base_url' => "{$scheme}://{$host}/api/v1",
                    'logo_url'     => $tenant->meta['logo_url'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return $this->respond($memberships);
    }

    /**
     * Constant-shape envelope the Flutter AccountLookupService parses
     * (res.data.data.memberships).
     */
    private function respond(array $memberships): JsonResponse
    {
        return response()->json([
            'data' => [
                'memberships' => $memberships,
            ],
        ]);
    }
}
