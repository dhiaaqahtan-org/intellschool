<?php

use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Exceptions\TenantNotResolved;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Tenant\TenantInstallation;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

it('pins landlord and tenant models to separate explicit contexts', function () {
    $tenant = $this->makeTenant('model-boundary', 'model-boundary.test');

    expect((new Tenant)->getConnectionName())->toBe('landlord')
        ->and(fn () => (new TenantInstallation)->getConnectionName())
        ->toThrow(TenantNotResolved::class);

    app(CurrentTenant::class)->runFor(
        $tenant->toContext('model-boundary.test'),
        function () {
            expect((new TenantInstallation)->getConnectionName())->toBe('tenant');
        },
    );
});

it('resolves module-local factories for landlord models', function () {
    $tenant = Tenant::factory()->make();
    $plan = Plan::factory()->published()->make();

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->status->value)->toBe('pending')
        ->and($tenant->provisioning_state->value)->toBe('queued')
        ->and($plan)->toBeInstanceOf(Plan::class)
        ->and($plan->is_public)->toBeTrue()
        ->and($plan->version)->toBe(1);
});
