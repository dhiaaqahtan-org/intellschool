<?php

namespace Modules\Saas\Http\Controllers\Platform;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Services\FeatureEntitlementService;

/**
 * Subscription administration (plan §9.2).
 *
 * Two things this deliberately does NOT do:
 *
 *  - It does not talk to a payment provider. Provider state is authoritative
 *    and arrives via verified webhooks; an operator forcing a status here is
 *    an override, recorded as one, not a billing action.
 *  - It never deletes school data on cancellation. Cancelling moves the
 *    subscription to a terminal state; the tenant lifecycle is separate and
 *    has its own retention window (plan §12).
 */
class SubscriptionController extends Controller
{
    /** Statuses an operator may set by hand, with what each means. */
    public const MANUAL_STATUSES = [
        'trialing' => 'Trial — full access, not yet billed',
        'active' => 'Active — paid and current',
        'past_due' => 'Past due — payment failed, access retained',
        'grace' => 'Grace — access retained while recovery is attempted',
        'paused' => 'Paused — access withdrawn, data retained',
        'cancelled' => 'Cancelled — access withdrawn at period end',
    ];

    public function index(Request $request): View
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $query = Subscription::query()->with(['tenant', 'plan'])->latest('id');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $subscriptions = $query->paginate(25)->withQueryString();

        $counts = Subscription::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('saas::platform.subscriptions.index', compact('subscriptions', 'counts'));
    }

    public function show(Subscription $subscription): View
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $subscription->load(['tenant', 'plan.features']);

        // What the tenant is ACTUALLY entitled to right now, resolved through
        // the same service the application uses — not re-derived here, so the
        // panel cannot disagree with enforcement.
        $snapshot = app(FeatureEntitlementService::class)->snapshot($subscription->tenant_uuid);

        return view('saas::platform.subscriptions.show', compact('subscription', 'snapshot'));
    }

    /**
     * Attach a plan to a tenant, or move an existing subscription to another
     * plan version.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $data = $request->validate([
            'tenant_uuid' => ['required', 'string', 'exists:landlord.saas_tenants,uuid'],
            'plan_id' => ['required', 'integer', 'exists:landlord.saas_plans,id'],
            'status' => ['required', 'in:'.implode(',', array_keys(self::MANUAL_STATUSES))],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $tenant = Tenant::where('uuid', $data['tenant_uuid'])->firstOrFail();
        $plan = Plan::findOrFail($data['plan_id']);

        $existing = Subscription::where('tenant_uuid', $tenant->uuid)
            ->whereNotIn('status', ['terminated', 'cancelled'])
            ->latest('id')
            ->first();

        if ($existing) {
            $previousPlan = $existing->plan_id;

            $existing->update([
                'plan_id' => $plan->id,
                'status' => $data['status'],
            ]);

            $this->audit('subscription.plan_changed', $tenant->uuid, [
                'from_plan_id' => $previousPlan,
                'to_plan' => "{$plan->plan_code} v{$plan->version}",
                'status' => $data['status'],
                'reason' => $data['reason'],
            ]);

            $subscription = $existing;
        } else {
            $subscription = Subscription::create([
                'tenant_uuid' => $tenant->uuid,
                'plan_id' => $plan->id,
                'provider' => 'manual',
                'status' => $data['status'],
                'trial_ends_at' => $data['status'] === 'trialing'
                    ? now()->addDays($plan->trial_days)
                    : null,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            $this->audit('subscription.created', $tenant->uuid, [
                'plan' => "{$plan->plan_code} v{$plan->version}",
                'status' => $data['status'],
                'reason' => $data['reason'],
            ]);
        }

        $this->flushEntitlements($tenant->uuid);

        return redirect()
            ->route('saas.platform.subscriptions.show', $subscription)
            ->with('success', "{$tenant->display_name} is now on {$plan->plan_code} v{$plan->version}.");
    }

    /**
     * Force a status transition. This is an override, not a billing event.
     */
    public function updateStatus(Request $request, Subscription $subscription): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(self::MANUAL_STATUSES))],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $from = $subscription->status;

        $subscription->update([
            'status' => $data['status'],
            'cancelled_at' => $data['status'] === 'cancelled' ? now() : $subscription->cancelled_at,
            'grace_ends_at' => $data['status'] === 'grace' ? now()->addDays(14) : $subscription->grace_ends_at,
        ]);

        $this->audit('subscription.status_overridden', $subscription->tenant_uuid, [
            'from' => $from,
            'to' => $data['status'],
            'reason' => $data['reason'],
        ]);

        $this->flushEntitlements($subscription->tenant_uuid);

        return back()->with('success', "Status changed from {$from} to {$data['status']}.");
    }

    /**
     * Entitlements are cached per tenant. A plan or status change that does
     * not invalidate the cache leaves the tenant on their old entitlements
     * for the rest of the TTL.
     */
    private function flushEntitlements(string $tenantUuid): void
    {
        app(EntitlementChecker::class) instanceof FeatureEntitlementService
            ? app(FeatureEntitlementService::class)->flushCache($tenantUuid)
            : null;
    }

    private function audit(string $action, string $tenantUuid, array $context): void
    {
        AuditEvent::record(
            action: $action,
            tenantUuid: $tenantUuid,
            context: $context,
            actorType: 'platform',
            actorId: (string) $this->operator()->uuid,
            actorLabel: $this->operator()->email,
            ip: request()->ip(),
        );
    }

    private function operator()
    {
        return auth('platform')->user();
    }
}
