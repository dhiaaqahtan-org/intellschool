<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;

/**
 * API endpoints for subscription and plan management.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
    ) {
    }

    /**
     * GET /api/saas/plans
     *
     * Public endpoint: list available plans for pricing pages.
     */
    public function publicPlans(): JsonResponse
    {
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
            ->activeOrTrialing()
            ->with('plan')
            ->first();

        if (! $subscription) {
            return response()->json([
                'tenant_uuid' => $context->uuid,
                'subscription' => null,
                'message' => 'No active subscription found.',
            ]);
        }

        return response()->json([
            'tenant_uuid' => $context->uuid,
            'subscription' => [
                'uuid' => $subscription->uuid,
                'status' => $subscription->status,
                'plan' => $subscription->plan?->plan_code,
                'plan_name' => $subscription->plan?->display_name,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                'current_period_end' => $subscription->current_period_end?->toIso8601String(),
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
            ],
        ]);
    }

    /**
     * GET /api/saas/platform/subscriptions
     *
     * Platform endpoint: list all subscriptions.
     */
    public function platformIndex(Request $request): JsonResponse
    {
        $query = Subscription::query()->with(['tenant', 'plan']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($tenantUuid = $request->query('tenant_uuid')) {
            $query->where('tenant_uuid', $tenantUuid);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 20));

        return response()->json($subscriptions);
    }

    /**
     * GET /api/saas/platform/subscriptions/{subscription}
     *
     * Platform endpoint: get a single subscription.
     */
    public function platformShow(Subscription $subscription): JsonResponse
    {
        $subscription->load(['tenant', 'plan.features']);

        return response()->json(['subscription' => $subscription]);
    }

    /**
     * GET /api/saas/platform/plans
     *
     * Platform endpoint: list all plans (including non-public).
     */
    public function platformPlans(): JsonResponse
    {
        $plans = Plan::query()
            ->with('features')
            ->orderBy('plan_code')
            ->orderByDesc('version')
            ->get();

        return response()->json(['plans' => $plans]);
    }

    /**
     * POST /api/saas/platform/plans
     *
     * Platform endpoint: create a new plan version.
     */
    public function platformStorePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_code' => 'required|string|max:50',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'billing_interval' => 'required|in:monthly,annual',
            'currency' => 'required|string|size:3',
            'price_cents' => 'required|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'is_public' => 'boolean',
            'features' => 'nullable|array',
            'features.*.feature_code' => 'required_with:features|string',
            'features.*.enabled' => 'required_with:features|boolean',
            'features.*.limit_value' => 'nullable|integer',
            'features.*.limit_type' => 'nullable|in:hard,soft',
        ]);

        // Determine the next version number.
        $latestVersion = Plan::where('plan_code', $validated['plan_code'])
            ->max('version') ?? 0;

        $plan = Plan::create([
            'plan_code' => $validated['plan_code'],
            'version' => $latestVersion + 1,
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'billing_interval' => $validated['billing_interval'],
            'currency' => $validated['currency'],
            'price_cents' => $validated['price_cents'],
            'trial_days' => $validated['trial_days'] ?? 0,
            'is_public' => $validated['is_public'] ?? true,
            'active_from' => now(),
        ]);

        // Create features.
        if (! empty($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                $plan->features()->create($feature);
            }
        }

        return response()->json([
            'message' => "Plan {$plan->plan_code} v{$plan->version} created.",
            'plan' => $plan->load('features'),
        ], 201);
    }
}
