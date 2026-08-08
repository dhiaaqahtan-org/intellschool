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
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Services\DomainVerifier;
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
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255'],

            // Only letters, digits and underscore: the name reaches a PDO DSN
            // and, on the create path, a backtick-quoted SQL identifier. Neither
            // can be parameterised, so the charset is the whole defence.
            'database_name' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/'],
            'database_username' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/', 'required_with:database_password'],
            'database_password' => ['nullable', 'string', 'max:255', 'required_with:database_username'],

            'hostname' => ['nullable', 'string', 'max:253'],
            'domain_type' => ['nullable', Rule::in([TenantDomain::TYPE_SUBDOMAIN, TenantDomain::TYPE_CUSTOM])],
        ], [
            'database_name.regex' => 'Use only letters, digits and underscores — this is a MySQL identifier.',
            'database_username.regex' => 'Use only letters, digits and underscores — this is a MySQL username.',
            'database_username.required_with' => 'Enter the database username that goes with this password.',
            'database_password.required_with' => 'Enter the password for this database user.',
        ]);

        // A username with no database name has nothing to apply to, and would
        // be silently discarded — which looks like the form worked.
        if (! empty($validated['database_username']) && empty($validated['database_name'])) {
            return back()
                ->withErrors(['database_name' => 'Enter the database name these credentials belong to.'])
                ->withInput();
        }

        $hostname = null;

        if (! empty($validated['hostname'])) {
            $hostname = HostNormalizer::normalize($validated['hostname']);

            if ($hostname === null) {
                return back()->withErrors(['hostname' => 'Enter a valid hostname, with no scheme or path.'])->withInput();
            }

            if (TenantDomain::where('hostname', $hostname)->exists()) {
                return back()->withErrors(['hostname' => 'That hostname is already registered to a tenant.'])->withInput();
            }
        }

        try {
            $result = $provisioner->createTenant($validated);

            if ($hostname !== null) {
                $this->attachDomain($result['tenant'], $hostname, $validated['domain_type'] ?? TenantDomain::TYPE_SUBDOMAIN);
            }

            try {
                ProvisionTenantJob::dispatch($result['run']->uuid)->afterCommit();
            } catch (\Throwable $dispatchError) {
                report($dispatchError);
            }

            return redirect()
                ->route('saas.platform.tenants.show', $result['tenant'])
                ->with('success', 'Tenant created and provisioning queued.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['display_name' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Register the school's first hostname.
     *
     * A subdomain we issue is trusted immediately. A school's own domain gets a
     * token and stays unroutable until the DNS check passes — the same gate as
     * adding one later, because "it was entered at creation time" proves nothing
     * about who controls the DNS.
     */
    private function attachDomain(Tenant $tenant, string $hostname, string $type): void
    {
        TenantDomain::create([
            'tenant_uuid' => $tenant->uuid,
            'hostname' => $hostname,
            'type' => $type,
            'is_primary' => true,
            'verification_token' => $type === TenantDomain::TYPE_CUSTOM
                ? bin2hex(random_bytes(32))
                : null,
        ]);
    }

    public function show(Tenant $tenant, DomainVerifier $verifier): View
    {
        Gate::forUser(auth('platform')->user())->authorize('viewTenant', $tenant);

        $tenant->load(['domains', 'database', 'subscription.plan', 'owners', 'provisioningRuns']);

        $recentAuditEvents = AuditEvent::where('tenant_uuid', $tenant->uuid)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        // The exact TXT record each unverified custom domain is waiting on.
        // Built here rather than in the view so the record name stays defined
        // in one place — an operator reads these off the screen and gives them
        // to a school, so a mismatch means a verification that never passes.
        $verificationRecords = $tenant->domains
            ->filter(fn (TenantDomain $d) => $d->type === TenantDomain::TYPE_CUSTOM && $d->verified_at === null)
            ->mapWithKeys(fn (TenantDomain $d) => [$d->id => [
                'name' => $verifier->recordName($d),
            ]])
            ->all();

        $plans = Plan::all();

        return view('saas::platform.tenants.show', compact('tenant', 'recentAuditEvents', 'verificationRecords', 'plans'));
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

    /**
     * Re-check the DNS TXT record for a custom domain.
     *
     * Operator-triggered rather than scheduled: DNS propagation is measured in
     * minutes to hours, and a school watching the panel wants to press a button
     * when their registrar says the record is live, not wait for a poller.
     */
    public function verifyDomain(
        Request $request,
        Tenant $tenant,
        TenantDomain $domain,
        DomainVerifier $verifier,
    ): RedirectResponse {
        Gate::forUser(auth('platform')->user())->authorize('manageDomains', $tenant);

        if ($domain->tenant_uuid !== $tenant->uuid) {
            return back()->with('error', 'Domain does not belong to this tenant.');
        }

        $result = $verifier->verify($domain);

        AuditEvent::record(
            action: $result['verified'] ? 'domain.verified' : 'domain.verification_failed',
            tenantUuid: $tenant->uuid,
            context: ['hostname' => $domain->hostname, 'reason' => $result['reason']],
            actorType: 'platform',
            ip: $request->ip(),
        );

        return $result['verified']
            ? back()->with('success', "{$domain->hostname} verified and now routing.")
            : back()->with('error', $result['reason']);
    }

    /**
     * Promote a domain to primary.
     *
     * This is not cosmetic: DomainTenantUrlGenerator builds every absolute URL
     * the school sends out — password resets, invitations, export and report
     * links — from the primary hostname. Pointing it at a host that does not
     * route would send working mail containing dead links.
     */
    public function setPrimaryDomain(
        Request $request,
        Tenant $tenant,
        TenantDomain $domain,
        TenantResolver $resolver,
    ): RedirectResponse {
        Gate::forUser(auth('platform')->user())->authorize('manageDomains', $tenant);

        if ($domain->tenant_uuid !== $tenant->uuid) {
            return back()->with('error', 'Domain does not belong to this tenant.');
        }

        if (! $domain->isRoutable()) {
            return back()->with('error', 'Verify this domain before making it primary.');
        }

        $tenant->domains()->where('is_primary', true)->update(['is_primary' => false]);
        $domain->forceFill(['is_primary' => true])->save();

        $resolver->forget($domain->hostname);

        AuditEvent::record(
            action: 'domain.primary_changed',
            tenantUuid: $tenant->uuid,
            context: ['hostname' => $domain->hostname],
            actorType: 'platform',
            ip: $request->ip(),
        );

        // The URL generator caches the primary host per tenant, under that
        // tenant's own cache prefix, which the control plane cannot reach. Mail
        // sent in the next few minutes may still carry the old hostname.
        return back()->with(
            'success',
            "{$domain->hostname} is now the primary domain. Outgoing links switch over within a few minutes."
        );
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

    public function destroy(Request $request, Tenant $tenant, TenantResolver $resolver): RedirectResponse
    {
        Gate::forUser(auth('platform')->user())->authorize('suspendTenant', $tenant);

        foreach ($tenant->domains as $domain) {
            $resolver->forget($domain->hostname);
        }

        $displayName = $tenant->display_name;

        AuditEvent::record(
            action: 'tenant.deleted',
            tenantUuid: $tenant->uuid,
            context: ['display_name' => $displayName, 'slug' => $tenant->slug],
            actorType: 'platform',
            ip: $request->ip(),
        );

        $tenant->domains()->delete();
        $tenant->provisioningRuns()->delete();
        $tenant->owners()->delete();
        $tenant->subscription()?->delete();
        $tenant->delete();

        return redirect()
            ->route('saas.platform.tenants.index')
            ->with('success', "Tenant '{$displayName}' deleted successfully.");
    }
}
