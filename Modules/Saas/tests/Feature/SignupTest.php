<?php

use Illuminate\Support\Facades\Bus;
use Modules\Saas\Jobs\ProvisionTenantJob;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantOwner;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

beforeEach(function () {
    config()->set('saas.hosts.marketing', 'localhost');
    config()->set('saas.signup.enabled', true);
    config()->set('saas.signup.owner_invitations_enabled', true);
    config()->set('app.url', 'http://localhost');
    app('url')->forceRootUrl('http://localhost');
    config()->set('saas.claims.publish_pricing', true);

    $this->withServerVariables([
        'HTTP_HOST' => 'localhost',
        'SERVER_NAME' => 'localhost',
    ]);
});

function publicSignupPlan(array $overrides = []): Plan
{
    return Plan::create(array_merge([
        'plan_code' => 'starter',
        'version' => 1,
        'display_name' => 'Starter',
        'billing_interval' => 'monthly',
        'currency' => 'USD',
        'price_cents' => 4900,
        'trial_days' => 14,
        'active_from' => now()->subMinute(),
        'is_public' => true,
    ], $overrides));
}

function signupPayload(Plan $plan): array
{
    return [
        'school_name' => 'Al Noor School',
        'owner_name' => 'Ahmed Al Rashid',
        'email' => 'owner@alnoor.test',
        'plan_id' => $plan->id,
        'locale' => 'ar',
        'timezone' => 'Asia/Riyadh',
    ];
}

it('shows a truthful unavailable state when no self-service plan exists', function () {
    $this->get('/en/signup')
        ->assertOk()
        ->assertSee(__('saas::marketing.signup.unavailable_title', locale: 'en'))
        ->assertDontSee('name="school_name"', false);
});

it('renders the unavailable page while public signup is closed', function () {
    config()->set('saas.signup.enabled', false);

    $this->get('/ar/signup')
        ->assertOk()
        ->assertSee(__('saas::marketing.signup.unavailable_title', locale: 'ar'))
        ->assertDontSee('name="school_name"', false);
});

it('fails closed when public signup has not been approved', function () {
    $plan = publicSignupPlan();
    config()->set('saas.signup.enabled', false);

    $this->from('/en/signup')->post('/en/signup', signupPayload($plan))
        ->assertRedirect('/en/signup')
        ->assertSessionHasErrors('plan_id');

    expect(Tenant::count())->toBe(0);
});

it('fails closed when owner invitation delivery is not enabled', function () {
    $plan = publicSignupPlan();
    config()->set('saas.signup.owner_invitations_enabled', false);

    $this->from('/en/signup')->post('/en/signup', signupPayload($plan))
        ->assertRedirect('/en/signup')
        ->assertSessionHasErrors('plan_id');

    expect(Tenant::count())->toBe(0);
});

it('rejects a plan that is private expired or has no self-service trial', function (array $overrides) {
    $plan = publicSignupPlan($overrides);

    $this->from('/en/signup')->post('/en/signup', signupPayload($plan))
        ->assertRedirect('/en/signup')
        ->assertSessionHasErrors('plan_id');

    expect(Tenant::count())->toBe(0);
})->with([
    'private' => [['is_public' => false]],
    'expired' => [['active_until' => now()->subMinute()]],
    'no trial' => [['trial_days' => 0]],
]);

it('creates one consistent landlord record set and dispatches provisioning', function () {
    Bus::fake();
    $plan = publicSignupPlan();

    $this->post('/en/signup', signupPayload($plan))
        ->assertRedirect(route('saas.marketing.signup.success'))
        ->assertSessionHasNoErrors();

    $tenant = Tenant::query()->sole();
    $owner = TenantOwner::query()->sole();
    $subscription = Subscription::query()->sole();
    $run = $tenant->provisioningRuns()->sole();

    expect($tenant->locale)->toBe('ar')
        ->and($tenant->timezone)->toBe('Asia/Riyadh')
        ->and($owner->name)->toBe('Ahmed Al Rashid')
        ->and($owner->email)->toBe('owner@alnoor.test')
        ->and($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->trial_ends_at->diffInDays(now(), absolute: true))->toBeLessThanOrEqual(14);

    Bus::assertDispatched(
        ProvisionTenantJob::class,
        fn (ProvisionTenantJob $job) => $job->runUuid === $run->uuid
    );
});

it('runs the complete control-plane web stack without loading school configuration', function () {
    publicSignupPlan();

    // No withoutMiddleware(Init::class): ResolveTenant must mark this request
    // so the legacy school initializer is bypassed naturally.
    $this->get('/en/signup')->assertOk();
});
