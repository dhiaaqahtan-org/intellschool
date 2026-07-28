<?php

namespace Modules\Saas\Http\Controllers\Platform;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Saas\Domain\Tenancy\HostNormalizer;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Events\TenantStatusChanged;
use Modules\Saas\Http\Requests\Api\PlatformTenantIndexRequest;
use Modules\Saas\Jobs\ProvisionTenantJob;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Services\TenantProvisioner;
use Modules\Saas\Services\TenantResolver;

/**
 * Platform tenant management (web UI).
 *
 * All actions are control-plane only — this controller never touches tenant
 * databases. Platform operators manage lifecycle, domains, and provisioning;
 * they do NOT have implicit access to school data (plan §5.4).
 */
class TenantController extends Controller
{
    public function index(PlatformTenantIndexRequest $request): View
    {
        $filters = $request->validated();
        $query = Tenant::query()->with(['domains', 'subscription.plan']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('uuid', $search);
            });
        }

        $tenants = $query
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 25);

        return view('saas::platform.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        Gate::forUser(auth('platform')->user())->authorize('createTenant');

        return view('saas::platform.tenants.create');
    }

    public function store(Request $request, TenantProvisioner $provisioner): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('createTenant');

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:63', 'alpha_dash'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', Rule::in(config('localizer.supported_locales', ['en', 'ar']))],
            'timezone' => ['nullable', 'timezone'],
            'region' => ['nullable', 'string', 'max:32'],
            'tier' => ['nullable', 'string', 'max:32'],
        ]);

        try {
            $result = $provisioner->createTenant($validated);

            try {
                ProvisionTenantJob::dispatch($result['run']->uuid)->afterCommit();
            } catch (\Throwable $dispatchError) {
                report($dispatchError);
            }

            return redirect()
                ->route('saas.platform.tenants.show', $result['tenant'])
                ->with('success', 'Tenant created and provisioning queued.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['slug' => $e->getMessage()])->withInput();
        }
    }

    public function show(Tenant $tenant): View
    {
        Gate::forUser(auth('platform')->user())->authorize('viewTenant', $tenant);

        $tenant->load(['domains', 'database', 'subscription.plan', 'owners', 'provisioningRuns']);

        $recentAuditEvents = AuditEvent::where('tenant_uuid', $tenant->uuid)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('saas::platform.tenants.show', compact('tenant', 'recentAuditEvents'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('updateTenant', $tenant);

        $validated = $request->validate([
            'display_name' => ['sometimes', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'locale' => ['sometimes', Rule::in(config('localizer.supported_locales', ['en', 'ar']))],
            'timezone' => ['sometimes', 'timezone'],
            'region' => ['nullable', 'string', 'max:32'],
            'tier' => ['sometimes', 'string', 'max:32'],
            'meta' => ['nullable', 'array'],
        ]);

        $tenant->update($validated);

        AuditEvent::record(
            action: 'tenant.updated',
            tenantUuid: $tenant->uuid,
            context: ['fields' => array_keys($validated)],
            actorType: 'platform',
            ip: $request->ip(),
        );

        return redirect()
            ->route('saas.platform.tenants.show', $tenant)
            ->with('success', 'Tenant updated.');
    }

    public function suspend(Request $request, Tenant $tenant, TenantResolver $resolver): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('suspendTenant', $tenant);
        $validated = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']]);

        if ($tenant->status === TenantStatus::Suspended) {
            return back()->with('error', 'Tenant is already suspended.');
        }

        $previousStatus = $tenant->status;

        $tenant->update([
            'status' => TenantStatus::Suspended->value,
            'suspended_at' => now(),
        ]);

        foreach ($tenant->domains as $domain) {
            $resolver->forget($domain->hostname);
        }

        TenantStatusChanged::dispatch($tenant->uuid, $previousStatus, TenantStatus::Suspended, $validated['reason']);

        AuditEvent::record(
            action: 'tenant.suspended',
            tenantUuid: $tenant->uuid,
            context: ['reason' => $validated['reason']],
            actorType: 'platform',
            ip: $request->ip(),
        );

        return back()->with('success', 'Tenant suspended.');
    }

    public function reactivate(Request $request, Tenant $tenant, TenantResolver $resolver): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('reactivateTenant', $tenant);

        if ($tenant->status === TenantStatus::Active) {
            return back()->with('error', 'Tenant is already active.');
        }

        if ($tenant->status === TenantStatus::Terminated) {
            return back()->with('error', 'Cannot reactivate a terminated tenant.');
        }

        $previousStatus = $tenant->status;

        $tenant->update([
            'status' => TenantStatus::Active->value,
            'suspended_at' => null,
        ]);

        foreach ($tenant->domains as $domain) {
            $resolver->forget($domain->hostname);
        }

        TenantStatusChanged::dispatch($tenant->uuid, $previousStatus, TenantStatus::Active);

        AuditEvent::record(
            action: 'tenant.reactivated',
            tenantUuid: $tenant->uuid,
            actorType: 'platform',
            ip: $request->ip(),
        );

        return back()->with('success', 'Tenant reactivated.');
    }

    public function cancel(Request $request, Tenant $tenant, TenantResolver $resolver): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('suspendTenant', $tenant);
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'retention_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
        ]);

        if ($tenant->status === TenantStatus::Cancelled) {
            return back()->with('error', 'Tenant is already cancelled.');
        }

        $previousStatus = $tenant->status;

        $tenant->update([
            'status' => TenantStatus::Cancelled->value,
            'cancelled_at' => now(),
            'purge_after' => now()->addDays((int) ($validated['retention_days']
                ?? config('saas.tenancy.cancellation_retention_days', 90)
            )),
        ]);

        foreach ($tenant->domains as $domain) {
            $resolver->forget($domain->hostname);
        }

        TenantStatusChanged::dispatch($tenant->uuid, $previousStatus, TenantStatus::Cancelled, $validated['reason']);

        AuditEvent::record(
            action: 'tenant.cancelled',
            tenantUuid: $tenant->uuid,
            context: [
                'reason' => $validated['reason'],
                'purge_after' => $tenant->purge_after?->toIso8601String(),
            ],
            actorType: 'platform',
            ip: $request->ip(),
        );

        return back()->with('success', 'Tenant cancelled.');
    }

    public function provision(Request $request, Tenant $tenant, TenantProvisioner $provisioner): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('provision', $tenant);

        $run = $tenant->provisioningRuns()
            ->whereIn('state', ['queued', 'failed_recoverable'])
            ->latest()
            ->first();

        if ($run === null) {
            return back()->with('error', 'No resumable provisioning run found.');
        }

        try {
            $provisioner->provision($run);

            return back()->with('success', 'Provisioning completed.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Provisioning failed. Review the provisioning history and application logs before retrying.');
        }
    }

    public function addDomain(Request $request, Tenant $tenant, TenantResolver $resolver): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('manageDomains', $tenant);

        $validated = $request->validate([
            'hostname' => ['required', 'string', 'max:253'],
            'type' => ['required', 'in:subdomain,custom'],
        ]);

        $hostname = HostNormalizer::normalize($validated['hostname']);

        if ($hostname === null) {
            return back()->withErrors(['hostname' => 'Enter a valid hostname without a path or scheme.'])->withInput();
        }

        if (TenantDomain::where('hostname', $hostname)->exists()) {
            return back()->with('error', 'This hostname is already registered.');
        }

        $domain = TenantDomain::create([
            'tenant_uuid' => $tenant->uuid,
            'hostname' => $hostname,
            'type' => $validated['type'],
            'is_primary' => $tenant->domains()->count() === 0,
            'verification_token' => $validated['type'] === 'custom' ? bin2hex(random_bytes(32)) : null,
        ]);

        AuditEvent::record(
            action: 'domain.added',
            tenantUuid: $tenant->uuid,
            context: ['hostname' => $domain->hostname, 'type' => $domain->type],
            actorType: 'platform',
            ip: $request->ip(),
        );

        return back()->with('success', "Domain {$domain->hostname} added.");
    }

    public function removeDomain(Request $request, Tenant $tenant, TenantDomain $domain, TenantResolver $resolver): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('manageDomains', $tenant);

        if ($domain->tenant_uuid !== $tenant->uuid) {
            return back()->with('error', 'Domain does not belong to this tenant.');
        }

        if ($domain->is_primary && $tenant->domains()->count() > 1) {
            return back()->with('error', 'Cannot remove the primary domain while others exist.');
        }

        $resolver->forget($domain->hostname);

        AuditEvent::record(
            action: 'domain.removed',
            tenantUuid: $tenant->uuid,
            context: ['hostname' => $domain->hostname],
            actorType: 'platform',
            ip: $request->ip(),
        );

        $domain->delete();

        return back()->with('success', 'Domain removed.');
    }
}
