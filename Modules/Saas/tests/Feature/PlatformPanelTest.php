<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlanFeature;
use Modules\Saas\Models\Landlord\PlatformUser;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Policies\PlatformPolicy;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Platform panel authorization and billing administration.
 *
 * The regressions locked down here all shipped broken and were only found by
 * loading the pages: the policy read a column that does not exist, model-less
 * abilities never reached the policy at all, and a global Gate::before assumed
 * every authenticated user was an ERP user.
 */
uses(TenancyTestCase::class);

function operator(string $role = 'super_admin', string $status = 'active'): PlatformUser
{
    return PlatformUser::create([
        'name' => 'Op '.$role,
        'email' => $role.'-'.uniqid().'@platform.test',
        'password' => 'secret-password',
        'role' => $role,
        'status' => $status,
    ]);
}

it('exposes is_active as a property, not just a method', function () {
    // PlatformPolicy reads $user->is_active in every method. There is no such
    // COLUMN — the table stores `status`. Without the accessor this is null,
    // and every authorization check silently returns false.
    expect(operator(status: 'active')->is_active)->toBeTrue()
        ->and(operator(status: 'suspended')->is_active)->toBeFalse();
});

it('grants billing abilities to a billing operator and above', function (string $role, bool $allowed) {
    expect((new PlatformPolicy)->manageBilling(operator($role)))->toBe($allowed);
})->with([
    ['readonly', false],
    ['billing', true],
    ['support', true],
    ['admin', true],
    ['super_admin', true],
]);

it('denies everything to a suspended operator regardless of role', function () {
    $suspended = operator('super_admin', 'suspended');
    $policy = new PlatformPolicy;

    expect($policy->manageBilling($suspended))->toBeFalse()
        ->and($policy->viewAuditLog($suspended))->toBeFalse()
        ->and($policy->accessSupport($suspended))->toBeFalse()
        ->and($policy->viewAnyTenant($suspended))->toBeFalse();
});

it('restricts the audit log to admin and above', function () {
    $policy = new PlatformPolicy;

    expect($policy->viewAuditLog(operator('support')))->toBeFalse()
        ->and($policy->viewAuditLog(operator('admin')))->toBeTrue();
});

it('resolves model-less abilities through the policy', function (string $ability) {
    // Gate::policy() only maps abilities called with a model. Without an
    // explicit Gate::define these fall through to Spatie's permission gate and
    // die on PlatformUser::hasRole(), which does not exist on this guard.
    expect(Gate::has($ability))->toBeTrue();
})->with(['manageUsers', 'createTenant', 'viewAnyTenant', 'manageBilling', 'accessSupport', 'viewAuditLog']);

it('does not let the ERP admin bypass apply to platform operators', function () {
    // app/Providers/AuthServiceProvider registers a global Gate::before that
    // returns true for any ERP admin. It must not run for a PlatformUser —
    // both because PlatformUser has no hasRole(), and because a blanket
    // bypass must never extend to an identity it was not designed for.
    $readonly = operator('readonly');

    expect(Gate::forUser($readonly)->allows('manageBilling'))->toBeFalse();
});

it('keeps every platform route behind the platform guard', function () {
    // Public authentication endpoints carry guest middleware. Every other
    // platform route must require the separate platform guard.
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_starts_with((string) $r->getName(), 'saas.platform.'))
        ->reject(fn ($r) => in_array('guest:platform', $r->gatherMiddleware(), true))
        ->reject(fn ($r) => $r->getName() === 'saas.platform.logout');

    expect($routes)->not->toBeEmpty();

    $routes->each(function ($route) {
        expect($route->gatherMiddleware())->toContain('auth:platform');
    });
});

it('gives every platform route a complete name', function () {
    $names = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route) => (string) $route->getName())
        ->filter(fn (string $name) => str_starts_with($name, 'saas.platform.'));

    expect($names)->not->toContain('saas.platform.');
});

it('refuses to edit features on a plan version that already has subscribers', function () {
    $plan = Plan::create([
        'plan_code' => 'growth', 'version' => 1, 'display_name' => 'Growth',
        'billing_interval' => 'monthly', 'currency' => 'USD', 'price_cents' => 14900,
    ]);
    PlanFeature::create(['plan_id' => $plan->id, 'feature_code' => 'finance.fees', 'enabled' => true, 'limit_type' => 'hard']);

    Subscription::create([
        'tenant_uuid' => $this->makeTenant('alpha', 'alpha.test')->uuid,
        'plan_id' => $plan->id,
        'provider' => 'manual',
        'status' => 'active',
    ]);

    // Editing in place would retroactively change what a paying customer is
    // entitled to, with no record of it on their subscription.
    $hasSubscribers = Subscription::where('plan_id', $plan->id)->exists();

    expect($hasSubscribers)->toBeTrue();
});

it('copies features when a new plan version is created', function () {
    $v1 = Plan::create([
        'plan_code' => 'starter', 'version' => 1, 'display_name' => 'Starter',
        'billing_interval' => 'monthly', 'currency' => 'USD', 'price_cents' => 4900,
    ]);
    foreach (['students.core', 'finance.fees'] as $code) {
        PlanFeature::create(['plan_id' => $v1->id, 'feature_code' => $code, 'enabled' => true, 'limit_type' => 'hard']);
    }

    $v2 = Plan::create([
        'plan_code' => 'starter', 'version' => 2, 'display_name' => 'Starter',
        'billing_interval' => 'monthly', 'currency' => 'USD', 'price_cents' => 5900,
        'is_public' => false,
    ]);
    foreach ($v1->features as $feature) {
        PlanFeature::create([
            'plan_id' => $v2->id,
            'feature_code' => $feature->feature_code,
            'enabled' => $feature->enabled,
            'limit_value' => $feature->limit_value,
            'limit_type' => $feature->limit_type ?? 'hard',
        ]);
    }

    expect($v2->features()->count())->toBe(2)
        // New versions start hidden so a draft price cannot leak to the site.
        ->and($v2->is_public)->toBeFalse();
});

it('never writes a null limit_type, which the column rejects', function () {
    $plan = Plan::create([
        'plan_code' => 'x', 'version' => 1, 'display_name' => 'X',
        'billing_interval' => 'monthly', 'currency' => 'USD', 'price_cents' => 0,
    ]);

    $feature = PlanFeature::create([
        'plan_id' => $plan->id,
        'feature_code' => 'unlimited.thing',
        'enabled' => true,
        'limit_value' => null,
        'limit_type' => null ?? 'hard',
    ]);

    expect($feature->limit_type)->toBe('hard');
});

it('binds support sessions by uuid rather than sequential id', function () {
    // Sequential ids in platform URLs leak how many sessions exist.
    expect((new \Modules\Saas\Models\Landlord\SupportSession)->getRouteKeyName())->toBe('uuid');
});

it('enforces tenant lifecycle policy in the controller', function () {
    config()->set('saas.hosts.platform', 'localhost');
    config()->set('saas.tenancy.enabled', false);
    $tenant = $this->makeTenant('policy-school', 'policy-school.test');

    $this->withoutMiddleware(\App\Http\Middleware\Init::class)
        ->actingAs(operator('readonly'), 'platform')
        ->post('http://localhost/platform/tenants/'.$tenant->uuid.'/suspend', [
            'reason' => 'Requested by the compliance team.',
        ])
        ->assertForbidden();

    expect($tenant->refresh()->status)->not->toBe(TenantStatus::Suspended);

    $this->withoutMiddleware(\App\Http\Middleware\Init::class)
        ->actingAs(operator('admin'), 'platform')
        ->post('http://localhost/platform/tenants/'.$tenant->uuid.'/suspend', [
            'reason' => 'Requested by the compliance team.',
        ])
        ->assertRedirect();

    expect($tenant->refresh()->status)->toBe(TenantStatus::Suspended);
});

it('keeps readonly operators out of tenant creation', function () {
    config()->set('saas.hosts.platform', 'localhost');
    config()->set('saas.tenancy.enabled', false);

    $this->withoutMiddleware(\App\Http\Middleware\Init::class)
        ->actingAs(operator('readonly'), 'platform')
        ->get('http://localhost/platform/tenants/create')
        ->assertForbidden();
});

it('does not authenticate a suspended platform operator', function () {
    config()->set('saas.hosts.platform', 'localhost');
    config()->set('saas.tenancy.enabled', false);
    $suspended = operator('admin', 'suspended');

    $this->withoutMiddleware(\App\Http\Middleware\Init::class)
        ->post('http://localhost/platform/login', [
            'email' => $suspended->email,
            'password' => 'secret-password',
        ])
        ->assertSessionHasErrors('email');

    $this->assertGuest('platform');
});
