<?php

namespace Modules\Saas\Http\Controllers\Platform;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\ProvisioningRun;
use Modules\Saas\Models\Landlord\Subscription;

class DashboardController extends Controller
{
    /**
     * Platform overview: tenant counts by status, recent activity.
     */
    public function index(Request $request): View
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'trialing_tenants' => Tenant::where('status', 'trialing')->count(),
            'active_subscriptions' => Subscription::whereIn('status', ['active', 'trialing'])->count(),
        ];

        $recentTenants = Tenant::query()
            ->with(['domains', 'subscription.plan'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $provisioningRuns = ProvisioningRun::query()
            ->with('tenant')
            ->whereIn('state', ['queued', 'running', 'failed_recoverable', 'failed_manual_review'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('saas::platform.dashboard', compact(
            'stats',
            'recentTenants',
            'provisioningRuns',
        ));
    }
}
