<?php

namespace Modules\Saas\Http\Controllers\Marketing;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Models\Landlord\TenantOwner;
use Modules\Saas\Services\TenantProvisioner;

/**
 * Self-service signup flow (plan §7, §11).
 *
 * Creates a pending tenant with a trial subscription and queues provisioning.
 * The owner receives an invitation email (when mail is configured) to set
 * their password and complete the setup wizard.
 */
class SignupController extends Controller
{
    public function showForm(): View
    {
        $plans = Plan::query()
            ->active()
            ->public()
            ->orderBy('price_cents')
            ->get();

        return view('saas::marketing.signup', compact('plans'));
    }

    public function register(Request $request, TenantProvisioner $provisioner): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'plan_id' => ['nullable', 'exists:saas_plans,id'],
            'locale' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        // Check if email already owns a tenant.
        $existingOwner = TenantOwner::where('email', $validated['email'])->first();
        if ($existingOwner) {
            return back()
                ->withErrors(['email' => 'This email is already associated with a school on our platform.'])
                ->withInput();
        }

        $locale = $validated['locale'] ?? 'en';
        $timezone = $validated['timezone'] ?? 'UTC';

        try {
            $result = DB::connection(config('saas.database.landlord_connection', 'landlord'))
                ->transaction(function () use ($validated, $provisioner, $locale, $timezone) {
                    // 1. Create the tenant.
                    $tenantResult = $provisioner->createTenant([
                        'display_name' => $validated['school_name'],
                        'locale' => $locale,
                        'timezone' => $timezone,
                    ]);

                    $tenant = $tenantResult['tenant'];

                    // 2. Create the owner record.
                    TenantOwner::create([
                        'tenant_uuid' => $tenant->uuid,
                        'email' => $validated['email'],
                        'role' => 'owner',
                        'status' => 'invited',
                        'invited_at' => now(),
                    ]);

                    // 3. Create a trial subscription.
                    $plan = null;
                    if (! empty($validated['plan_id'])) {
                        $plan = Plan::find($validated['plan_id']);
                    }

                    $trialDays = (int) config('saas.billing.trial_days', 14);

                    Subscription::create([
                        'tenant_uuid' => $tenant->uuid,
                        'plan_id' => $plan?->id,
                        'provider' => 'internal',
                        'status' => 'trialing',
                        'trial_ends_at' => now()->addDays($trialDays),
                        'current_period_start' => now(),
                        'current_period_end' => now()->addDays($trialDays),
                    ]);

                    return $tenantResult;
                });

            return redirect()
                ->route('saas.marketing.signup.success')
                ->with('tenant_slug', $result['tenant']->slug)
                ->with('owner_email', $validated['email']);
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['school_name' => $e->getMessage()])
                ->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['school_name' => 'Something went wrong while creating your school. Please try again.'])
                ->withInput();
        }
    }

    public function showSuccess(): View
    {
        return view('saas::marketing.signup-success');
    }
}
