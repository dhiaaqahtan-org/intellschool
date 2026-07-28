<?php

namespace Modules\Saas\Http\Controllers\Platform;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantEntitlement;
use Modules\Saas\Services\FeatureEntitlementService;

/**
 * Per-tenant entitlement overrides (plan §8).
 *
 * Overrides sit above plan features, so this is how negotiated enterprise
 * terms and temporary grants are expressed without minting a bespoke plan for
 * one customer.
 *
 * A reason is REQUIRED on every override. An override that quietly enables a
 * paid feature with no recorded justification is indistinguishable from a bug
 * or an abuse of platform access, and it is exactly the kind of thing an
 * auditor asks about.
 */
class EntitlementController extends Controller
{
    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $data = $request->validate([
            'feature_code' => ['required', 'string', 'max:80', Rule::in(config('saas.entitlements.feature_codes', []))],
            'enabled' => ['required', 'boolean'],
            'limit_value' => ['nullable', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'valid_until' => ['nullable', 'date', 'after:now'],
        ]);

        TenantEntitlement::updateOrCreate(
            ['tenant_uuid' => $tenant->uuid, 'feature_code' => $data['feature_code']],
            [
                'enabled' => $data['enabled'],
                // A `nullable` rule omits the key entirely when the field is
                // absent from the request, rather than setting it to null.
                'limit_value' => $data['limit_value'] ?? null,
                'source' => 'platform_override',
                'reason' => $data['reason'],
                'valid_from' => now(),
                'valid_until' => $data['valid_until'] ?? null,
                'granted_by' => $this->operator()->email,
            ]
        );

        AuditEvent::record(
            action: 'entitlement.overridden',
            tenantUuid: $tenant->uuid,
            context: [
                'feature_code' => $data['feature_code'],
                'enabled' => $data['enabled'],
                'limit_value' => $data['limit_value'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'reason' => $data['reason'],
            ],
            actorType: 'platform',
            actorId: (string) $this->operator()->uuid,
            actorLabel: $this->operator()->email,
            ip: $request->ip(),
        );

        app(FeatureEntitlementService::class)->flushCache($tenant->uuid);

        return back()->with('success', "Override saved for {$data['feature_code']}.");
    }

    public function destroy(Request $request, Tenant $tenant, TenantEntitlement $entitlement): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        // Route-model binding resolves the entitlement by id alone, so confirm
        // it actually belongs to this tenant before deleting it.
        abort_unless($entitlement->tenant_uuid === $tenant->uuid, 404);

        $code = $entitlement->feature_code;
        $entitlement->delete();

        AuditEvent::record(
            action: 'entitlement.override_removed',
            tenantUuid: $tenant->uuid,
            context: ['feature_code' => $code],
            actorType: 'platform',
            actorId: (string) $this->operator()->uuid,
            actorLabel: $this->operator()->email,
            ip: $request->ip(),
        );

        app(FeatureEntitlementService::class)->flushCache($tenant->uuid);

        return back()->with('success', "Override removed for {$code}. The plan's own setting applies again.");
    }

    private function operator()
    {
        return auth('platform')->user();
    }
}
