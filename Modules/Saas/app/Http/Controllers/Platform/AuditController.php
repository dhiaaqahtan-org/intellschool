<?php

namespace Modules\Saas\Http\Controllers\Platform;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Tenant;

/**
 * Audit log viewer (plan §5.1, §7).
 *
 * Read-only by construction: AuditEvent blocks updates and deletes at the
 * model level, and there is no write path here. If an operator could edit the
 * audit log from the panel that administers the platform, the log would not be
 * evidence of anything.
 */
class AuditController extends Controller
{
    public function index(Request $request): View
    {
        Gate::forUser(auth('platform')->user())->authorize('viewAuditLog');

        $events = AuditEvent::query()
            ->when($request->string('tenant')->toString(), fn ($q, $t) => $q->where('tenant_uuid', $t))
            ->when($request->string('action')->toString(), fn ($q, $a) => $q->where('action', 'like', $a.'%'))
            ->when($request->string('actor')->toString(), fn ($q, $a) => $q->where('actor_label', 'like', '%'.$a.'%'))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        $tenants = Tenant::query()->orderBy('display_name')->get(['uuid', 'display_name']);

        $actions = AuditEvent::query()
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(25)
            ->pluck('total', 'action');

        return view('saas::platform.audit.index', compact('events', 'tenants', 'actions'));
    }
}
