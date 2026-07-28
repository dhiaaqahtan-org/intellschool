<?php

namespace Modules\Saas\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Saas\Http\Requests\Api\StorePlatformPlanRequest;
use Modules\Saas\Http\Resources\PlanResource;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Plan;

class PlatformPlanController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('manageBilling');

        $plans = Plan::query()
            ->with('features')
            ->orderBy('plan_code')
            ->orderByDesc('version')
            ->get();

        return PlanResource::collection($plans)->response();
    }

    public function store(StorePlatformPlanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $operator = $request->user('platform');

        $plan = DB::connection(config('saas.database.landlord_connection', 'landlord'))
            ->transaction(function () use ($data, $operator, $request) {
                $latestVersion = Plan::query()
                    ->where('plan_code', $data['plan_code'])
                    ->lockForUpdate()
                    ->max('version') ?? 0;

                $plan = Plan::create([
                    'plan_code' => $data['plan_code'],
                    'version' => $latestVersion + 1,
                    'display_name' => $data['display_name'],
                    'description' => $data['description'] ?? null,
                    'billing_interval' => $data['billing_interval'],
                    'currency' => $data['currency'],
                    'price_cents' => $data['price_cents'],
                    'trial_days' => $data['trial_days'] ?? 0,
                    // API-created versions are drafts. Publishing requires a
                    // separate reviewed action in the platform interface.
                    'is_public' => false,
                    'active_from' => now(),
                ]);

                foreach ($data['features'] ?? [] as $feature) {
                    $plan->features()->create([
                        'feature_code' => $feature['feature_code'],
                        'enabled' => $feature['enabled'],
                        'limit_value' => $feature['limit_value'] ?? null,
                        'limit_type' => $feature['limit_type'] ?? 'hard',
                    ]);
                }

                AuditEvent::record(
                    action: 'plan.version_created',
                    context: [
                        'plan_code' => $plan->plan_code,
                        'version' => $plan->version,
                        'source' => 'platform_api',
                    ],
                    actorType: 'platform',
                    actorId: (string) $operator->uuid,
                    actorLabel: $operator->email,
                    ip: $request->ip(),
                );

                return $plan;
            });

        return response()->json([
            'message' => "Plan {$plan->plan_code} v{$plan->version} created as a private draft.",
            'plan' => (new PlanResource($plan->load('features')))->resolve($request),
        ], 201);
    }
}
