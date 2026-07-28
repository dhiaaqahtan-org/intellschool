<?php

namespace Modules\Saas\Http\Controllers\Platform;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlanFeature;
use Modules\Saas\Models\Landlord\Subscription;

/**
 * Plan and plan-feature administration (plan §8, §9.3).
 *
 * The governing rule: PLANS ARE VERSIONED AND EFFECTIVELY IMMUTABLE once
 * customers are on them. A price or feature change creates a new version; it
 * must not silently rewrite an existing customer's contract. This controller
 * enforces that by refusing feature edits on any plan version that already has
 * subscribers, and offering "new version" instead.
 */
class PlanController extends Controller
{
    public function index(): View
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $plans = Plan::query()
            ->withCount(['features', 'subscriptions'])
            ->orderBy('plan_code')
            ->orderByDesc('version')
            ->get()
            ->groupBy('plan_code');

        return view('saas::platform.plans.index', compact('plans'));
    }

    public function show(Plan $plan): View
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $plan->load('features');

        $subscriberCount = Subscription::where('plan_id', $plan->id)->count();

        return view('saas::platform.plans.show', compact('plan', 'subscriberCount'));
    }

    /**
     * Create the next version of an existing plan, copying its features.
     *
     * This is the sanctioned way to change pricing or entitlements. Existing
     * subscriptions keep pointing at the old version, so their terms are
     * untouched until they are explicitly migrated.
     */
    public function newVersion(Request $request, Plan $plan): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $data = $request->validate([
            'price_cents' => ['required', 'integer', 'min:0'],
            'display_name' => ['required', 'string', 'max:255'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $nextVersion = Plan::where('plan_code', $plan->plan_code)->max('version') + 1;

        $new = Plan::create([
            'plan_code' => $plan->plan_code,
            'version' => $nextVersion,
            'display_name' => $data['display_name'],
            'description' => $plan->description,
            'billing_interval' => $plan->billing_interval,
            'currency' => $plan->currency,
            'price_cents' => $data['price_cents'],
            'trial_days' => $data['trial_days'],
            'active_from' => now(),
            'is_public' => false, // published deliberately, never by accident
        ]);

        foreach ($plan->features as $feature) {
            PlanFeature::create([
                'plan_id' => $new->id,
                'feature_code' => $feature->feature_code,
                'enabled' => $feature->enabled,
                'limit_value' => $feature->limit_value,
                'limit_type' => $feature->limit_type ?? 'hard',
            ]);
        }

        AuditEvent::record(
            action: 'plan.version_created',
            context: [
                'plan_code' => $new->plan_code,
                'version' => $new->version,
                'from_version' => $plan->version,
            ],
            actorType: 'platform',
            actorId: (string) $this->operator()->uuid,
            actorLabel: $this->operator()->email,
            ip: request()->ip(),
        );

        return redirect()
            ->route('saas.platform.plans.show', $new)
            ->with('success', "Created {$new->plan_code} v{$new->version}. It is not public yet — review the features, then publish.");
    }

    /**
     * Update a single feature on a plan version.
     */
    public function updateFeature(Request $request, Plan $plan): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        if (Subscription::where('plan_id', $plan->id)->exists()) {
            // Editing in place would retroactively change what existing
            // customers are entitled to, without any record of the change on
            // their subscription.
            return back()->with('error',
                'This version already has subscribers. Create a new version instead of editing it in place.');
        }

        $data = $request->validate([
            'feature_code' => ['required', 'string', 'max:80', Rule::in(config('saas.entitlements.feature_codes', []))],
            'enabled' => ['required', 'boolean'],
            'limit_value' => ['nullable', 'integer', 'min:0'],
            'limit_type' => ['required', 'in:hard,soft'],
        ]);

        PlanFeature::updateOrCreate(
            ['plan_id' => $plan->id, 'feature_code' => $data['feature_code']],
            [
                'enabled' => $data['enabled'],
                'limit_value' => $data['limit_value'],
                'limit_type' => $data['limit_type'],
            ]
        );

        return back()->with('success', "Feature {$data['feature_code']} updated.");
    }

    /**
     * Publish or unpublish a plan version on the marketing site.
     */
    public function togglePublic(Plan $plan): RedirectResponse
    {
        Gate::forUser($this->operator())->authorize('manageBilling');

        $plan->update(['is_public' => ! $plan->is_public]);

        AuditEvent::record(
            action: $plan->is_public ? 'plan.published' : 'plan.unpublished',
            context: ['plan_code' => $plan->plan_code, 'version' => $plan->version],
            actorType: 'platform',
            actorId: (string) $this->operator()->uuid,
            actorLabel: $this->operator()->email,
            ip: request()->ip(),
        );

        return back()->with('success',
            $plan->is_public
                ? "{$plan->plan_code} v{$plan->version} is now public."
                : "{$plan->plan_code} v{$plan->version} is hidden from the website.");
    }

    private function operator()
    {
        return auth('platform')->user();
    }
}
