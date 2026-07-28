<?php

namespace Modules\Saas\Http\Controllers\Platform;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Saas\Models\Landlord\SupportSession;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Services\Support\SupportAccessService;

/**
 * Support access to tenant data (plan §7, §12, ADR-009).
 *
 * This is the ONLY sanctioned route from a platform operator to a school's
 * records. The rules it exists to enforce:
 *
 *   - read-only by default;
 *   - a stated reason, always;
 *   - a second person approves — never the requester;
 *   - time-limited, and it expires on its own;
 *   - every step audited.
 *
 * The plan is explicit that the ERP's existing `is_default` super-user bypass
 * must not be reused as a cross-tenant skeleton key. This replaces it.
 */
class SupportSessionController extends Controller
{
    public function __construct(
        private readonly SupportAccessService $support,
    ) {
    }

    public function index(Request $request): View
    {
        Gate::forUser($this->operator())->authorize('accessSupport');

        $sessions = SupportSession::query()
            ->with('tenant')
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $tenants = Tenant::query()->orderBy('display_name')->get(['uuid', 'display_name', 'slug']);

        return view('saas::platform.support.index', compact('sessions', 'tenants'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('accessSupport');

        $data = $request->validate([
            'tenant_uuid' => ['required', 'string', 'exists:landlord.saas_tenants,uuid'],
            // Long enough to be a real justification, not "debugging".
            'reason' => ['required', 'string', 'min:20', 'max:1000'],
            'scope' => ['required', 'in:read,write'],
            // Duration is not accepted here: SupportAccessService owns the
            // expiry window so an operator cannot grant themselves a longer
            // one than policy allows.
        ]);

        $this->support->requestAccess(
            tenantUuid: $data['tenant_uuid'],
            operatorId: $this->operator()->id,
            operatorEmail: $this->operator()->email,
            reason: $data['reason'],
            scope: $data['scope'],
        );

        return back()->with('success',
            'Access requested. Another operator with admin rights must approve it before it becomes usable.');
    }

    public function approve(SupportSession $session): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('suspendTenant', $session->tenant);

        // Four-eyes: the person who asked for access cannot be the person who
        // grants it. Without this the approval step is decoration.
        if ((string) $session->operator_id === (string) $this->operator()->id) {
            return back()->with('error',
                'You cannot approve your own support request. Another operator must approve it.');
        }

        if ($session->status !== 'requested') {
            return back()->with('error', "This request is already {$session->status}.");
        }

        $this->support->approve(
            session: $session,
            approverId: $this->operator()->id,
            approverEmail: $this->operator()->email,
        );

        return back()->with('success', 'Support session approved.');
    }

    public function revoke(SupportSession $session): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('accessSupport');

        $this->support->revoke($session, $this->operator()->email);

        return back()->with('success', 'Support session revoked.');
    }

    private function operator()
    {
        return auth('platform')->user();
    }
}
