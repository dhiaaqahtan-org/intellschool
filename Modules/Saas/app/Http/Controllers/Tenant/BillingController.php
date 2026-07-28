<?php

namespace Modules\Saas\Http\Controllers\Tenant;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Services\TenantBillingService;

class BillingController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
        private readonly TenantBillingService $billing,
    ) {}

    public function index(): View
    {
        $context = $this->currentTenant->getOrFail();
        $subscription = $this->billing->currentFor($context->uuid);
        $summary = $this->billing->summary($subscription);

        return view('saas::billing.index', compact('context', 'subscription', 'summary'));
    }
}
