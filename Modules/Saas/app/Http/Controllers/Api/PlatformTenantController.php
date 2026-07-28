<?php

namespace Modules\Saas\Http\Controllers\Api;

use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Saas\Domain\Tenancy\TenantLifecycle;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Events\TenantStatusChanged;
use Modules\Saas\Http\Requests\Api\PlatformTenantIndexRequest;
use Modules\Saas\Http\Requests\Api\StorePlatformTenantRequest;
use Modules\Saas\Http\Requests\Api\UpdatePlatformTenantStatusRequest;
use Modules\Saas\Http\Resources\ProvisioningRunResource;
use Modules\Saas\Http\Resources\TenantResource;
use Modules\Saas\Jobs\ProvisionTenantJob;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Services\TenantProvisioner;
use Modules\Saas\Services\TenantResolver;

class PlatformTenantController extends Controller
{
    public function index(PlatformTenantIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $query = Tenant::query()->with(['domains', 'database', 'subscription.plan']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('display_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('uuid', $search);
            });
        }

        $tenants = $query
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 20);

        return TenantResource::collection($tenants)->response();
    }

    public function show(Tenant $tenant): JsonResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('viewTenant', $tenant);

        $tenant->load(['domains', 'database', 'owners', 'subscription.plan']);

        return (new TenantResource($tenant))->response();
    }

    public function provisioning(Tenant $tenant): JsonResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('viewTenant', $tenant);

        $run = $tenant->provisioningRuns()
            ->latest('created_at')
            ->first();

        return response()->json([
            'tenant_uuid' => $tenant->uuid,
            'provisioning' => $run === null
                ? null
                : (new ProvisioningRunResource($run))->resolve(request()),
        ]);
    }

    public function store(
        StorePlatformTenantRequest $request,
        TenantProvisioner $provisioner,
    ): JsonResponse {
        $connection = DB::connection(config('saas.database.landlord_connection', 'landlord'));

        $result = $connection->transaction(
            fn () => $provisioner->createTenant($request->validated())
        );

        try {
            ProvisionTenantJob::dispatch($result['run']->uuid);
        } catch (\Throwable $dispatchError) {
            report($dispatchError);
        }

        return response()->json([
            'message' => 'Tenant created and provisioning queued.',
            'tenant' => (new TenantResource($result['tenant']))->resolve($request),
            'provisioning' => (new ProvisioningRunResource($result['run']))->resolve($request),
        ], 202);
    }

    public function updateStatus(
        UpdatePlatformTenantStatusRequest $request,
        Tenant $tenant,
        TenantLifecycle $lifecycle,
        TenantResolver $resolver,
    ): JsonResponse {
        $data = $request->validated();
        $previous = $tenant->status;
        $next = TenantStatus::from($data['status']);

        try {
            $lifecycle->assertTransition($previous, $next);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error' => 'invalid_tenant_transition',
            ], 409);
        }

        $attributes = ['status' => $next->value];

        if ($next === TenantStatus::Suspended) {
            $attributes['suspended_at'] = now();
        }

        if ($next === TenantStatus::Active) {
            $attributes['suspended_at'] = null;
            $attributes['cancelled_at'] = null;
            $attributes['purge_after'] = null;
        }

        if ($next === TenantStatus::Cancelled) {
            $attributes['cancelled_at'] = now();
            $attributes['purge_after'] = now()->addDays(
                (int) config('saas.tenancy.cancellation_retention_days', 90)
            );
        }

        DB::connection(config('saas.database.landlord_connection', 'landlord'))
            ->transaction(function () use ($tenant, $attributes, $previous, $next, $data, $request) {
                $tenant->update($attributes);

                AuditEvent::record(
                    action: "tenant.{$next->value}",
                    tenantUuid: $tenant->uuid,
                    context: [
                        'from' => $previous->value,
                        'to' => $next->value,
                        'reason' => $data['reason'],
                    ],
                    actorType: 'platform',
                    actorId: (string) $request->user('platform')->uuid,
                    actorLabel: $request->user('platform')->email,
                    ip: $request->ip(),
                );
            });

        foreach ($tenant->domains as $domain) {
            $resolver->forget($domain->hostname);
        }

        TenantStatusChanged::dispatch($tenant->uuid, $previous, $next, $data['reason']);

        return response()->json([
            'message' => "Tenant status updated to {$next->value}.",
            'tenant' => (new TenantResource($tenant->fresh(['domains', 'database'])))->resolve($request),
        ]);
    }
}
