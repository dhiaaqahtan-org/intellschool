<?php

namespace Modules\Saas\Http\Controllers\Marketing;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Saas\Domain\Website\ClaimGate;
use Modules\Saas\Jobs\ProvisionTenantJob;
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
    public function __construct(
        private readonly ClaimGate $claims,
    ) {}

    public function showForm(): View
    {
        // Do not touch the landlord database while self-service signup is
        // deliberately closed. This keeps the public page fail-closed and
        // renderable during control-plane maintenance or initial setup.
        if (! $this->publicSignupEnabled()) {
            return view('saas::marketing.signup', ['plans' => collect(), 'signupAvailable' => false]);
        }

        $plans = Plan::query()
            ->active()
            ->public()
            ->where('trial_days', '>', 0)
            ->orderBy('price_cents')
            ->get();

        $signupAvailable = $plans->isNotEmpty();

        return view('saas::marketing.signup', compact('plans', 'signupAvailable'));
    }

    public function register(Request $request, TenantProvisioner $provisioner): RedirectResponse
    {
        if (! $this->publicSignupEnabled()) {
            throw ValidationException::withMessages([
                'plan_id' => __('saas::marketing.signup.errors.signup_unavailable'),
            ]);
        }

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'plan_id' => ['required', 'integer'],
            'locale' => ['nullable', Rule::in(config('localizer.supported_locales', ['en', 'ar']))],
            'timezone' => ['nullable', 'timezone'],
        ], [], [
            'school_name' => __('saas::marketing.signup.school_name'),
            'owner_name' => __('saas::marketing.signup.owner_name'),
            'email' => __('saas::marketing.signup.email'),
            'locale' => __('saas::marketing.signup.language'),
            'timezone' => __('saas::marketing.signup.timezone'),
        ]);

        $plan = Plan::query()
            ->active()
            ->public()
            ->where('trial_days', '>', 0)
            ->find($validated['plan_id']);

        if ($plan === null) {
            throw ValidationException::withMessages([
                'plan_id' => __('saas::marketing.signup.errors.plan_unavailable'),
            ]);
        }

        // Check if email already owns a tenant.
        $existingOwner = TenantOwner::where('email', $validated['email'])->first();
        if ($existingOwner) {
            return back()
                ->withErrors(['email' => __('saas::marketing.signup.errors.existing_owner')])
                ->withInput();
        }

        $locale = $validated['locale'] ?? app()->getLocale();
        $timezone = $validated['timezone'] ?? 'UTC';

        try {
            $result = DB::connection(config('saas.database.landlord_connection', 'landlord'))
                ->transaction(function () use ($validated, $provisioner, $locale, $timezone, $plan) {
                    // 1. Create the tenant.
                    $tenantResult = $provisioner->createTenant([
                        'display_name' => $validated['school_name'],
                        'locale' => $locale,
                        'timezone' => $timezone,
                    ]);

                    $tenant = $tenantResult['tenant'];

                    // 2. Create the owner record.
                    TenantOwner::create([
                        'name' => $validated['owner_name'],
                        'tenant_uuid' => $tenant->uuid,
                        'email' => $validated['email'],
                        'role' => 'owner',
                        'status' => 'invited',
                        'invited_at' => now(),
                    ]);

                    // 3. Create a trial subscription.
                    $trialDays = (int) $plan->trial_days;

                    Subscription::create([
                        'tenant_uuid' => $tenant->uuid,
                        'plan_id' => $plan->id,
                        'provider' => 'internal',
                        'status' => 'trialing',
                        'trial_ends_at' => now()->addDays($trialDays),
                        'current_period_start' => now(),
                        'current_period_end' => now()->addDays($trialDays),
                    ]);

                    return $tenantResult;
                });
            // Provisioning is intentionally outside the request transaction.
            // If the queue broker is temporarily unavailable, the committed
            // queued run remains visible to the scheduler/operator fallback.
            try {
                ProvisionTenantJob::dispatch($result['run']->uuid)->afterCommit();
            } catch (\Throwable $dispatchError) {
                report($dispatchError);
            }

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
                ->withErrors(['school_name' => __('saas::marketing.signup.errors.provisioning')])
                ->withInput();
        }
    }

    public function showSuccess(): View
    {
        return view('saas::marketing.signup-success');
    }

    private function publicSignupEnabled(): bool
    {
        return $this->claims->selfServiceSignup();
    }
}
