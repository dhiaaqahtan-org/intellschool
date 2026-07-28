<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Saas\Http\Requests\Api\PlatformSubscriptionIndexRequest;
use Modules\Saas\Http\Resources\SubscriptionResource;
use Modules\Saas\Models\Landlord\Subscription;

class PlatformSubscriptionController extends Controller
{
    public function index(PlatformSubscriptionIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $query = Subscription::query()->with(['tenant.domains', 'plan.features']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['tenant_uuid'])) {
            $query->where('tenant_uuid', $filters['tenant_uuid']);
        }

        $subscriptions = $query
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 20);

        return SubscriptionResource::collection($subscriptions)->response();
    }

    public function show(Subscription $subscription): JsonResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('manageBilling');

        $subscription->load(['tenant.domains', 'plan.features']);

        return (new SubscriptionResource($subscription))->response();
    }
}
