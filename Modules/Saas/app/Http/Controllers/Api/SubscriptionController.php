<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Domain\Website\ClaimGate;
use Modules\Saas\Http\Resources\SubscriptionResource;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;

/**
 * API endpoints for subscription and plan management.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
        private readonly ClaimGate $claims,
    ) {}

    /**
     * GET /api/saas/plans
     *
     * Public endpoint: list available plans for pricing pages.
     */
    public function publicPlans(): JsonResponse
    {
        abort_unless($this->claims->pricing(), 404);

        $plans = Plan::query()
            ->active()
            ->public()
            ->with(['features' => fn ($q) => $q->where('enabled', true)])
            ->orderBy('price_cents')
            ->get();

        return response()->json([
            'plans' => $plans->map(fn (Plan $plan) => [
                'code' => $plan->plan_code,
                'name' => $plan->display_name,
                'description' => $plan->description,
                'interval' => $plan->billing_interval,
                'currency' => $plan->currency,
                'price' => $plan->price_cents / 100,
                'trial_days' => $plan->trial_days,
                'features' => $plan->features->pluck('feature_code'),
            ]),
        ]);
    }

    /**
     * GET /api/saas/tenant/subscription
     *
     * Returns the current tenant's subscription status.
     */
    public function show(): JsonResponse
    {
        $context = $this->currentTenant->getOrFail();

        $subscription = Subscription::query()
            ->forTenant($context->uuid)
            ->with('plan.features')
            ->latest('created_at')
            ->first();

        if (! $subscription) {
            return response()->json([
                'tenant_uuid' => $context->uuid,
                'subscription' => null,
                'message' => 'No subscription found.',
            ]);
        }

        return response()->json([
            'tenant_uuid' => $context->uuid,
            'subscription' => (new SubscriptionResource($subscription))->resolve(request()),
        ]);
    }
}
