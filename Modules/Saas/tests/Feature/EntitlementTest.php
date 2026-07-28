<?php

use Modules\Saas\Contracts\EntitlementChecker;
use Modules\Saas\Exceptions\EntitlementDenied;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlanFeature;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Models\Landlord\TenantEntitlement;
use Modules\Saas\Services\FeatureEntitlementService;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Entitlement resolution (plan §8).
 *
 * The rule that matters most here is DENY BY DEFAULT. Hiding a menu item is
 * not an entitlement control; the server has to refuse.
 */
uses(TenancyTestCase::class);

beforeEach(function () {
    $this->tenantA = $this->makeTenant('alpha', 'alpha.test');
    $this->tenantB = $this->makeTenant('beta', 'beta.test');
    $this->tenant = app(\Modules\Saas\Contracts\CurrentTenant::class);
    $this->checker = app(EntitlementChecker::class);

    $this->plan = Plan::create([
        'plan_code' => 'growth',
        'version' => 1,
        'display_name' => 'Growth',
        'billing_interval' => 'monthly',
        'currency' => 'USD',
        'price_cents' => 0,
    ]);

    PlanFeature::create(['plan_id' => $this->plan->id, 'feature_code' => 'finance.fees', 'enabled' => true, 'limit_value' => null]);
    PlanFeature::create(['plan_id' => $this->plan->id, 'feature_code' => 'campuses.max', 'enabled' => true, 'limit_value' => 5]);
    PlanFeature::create(['plan_id' => $this->plan->id, 'feature_code' => 'hr.payroll', 'enabled' => false, 'limit_value' => null]);
});

function subscribe(string $tenantUuid, int $planId, string $status = 'active'): Subscription
{
    return Subscription::create([
        'tenant_uuid' => $tenantUuid,
        'plan_id' => $planId,
        'provider' => 'test',
        'status' => $status,
    ]);
}

it('denies every feature when the tenant has no subscription', function () {
    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('finance.fees'))->toBeFalse()
            ->and($this->checker->has('anything.at.all'))->toBeFalse();
    });
});

it('denies when there is no tenant context at all', function () {
    expect($this->checker->has('finance.fees'))->toBeFalse();
});

it('grants the features of the subscribed plan', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('finance.fees'))->toBeTrue()
            ->and($this->checker->remaining('campuses.max'))->toBe(5);
    });
});

it('respects a plan feature that is explicitly disabled', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('hr.payroll'))->toBeFalse()
            ->and($this->checker->remaining('hr.payroll'))->toBe(0);
    });
});

it('does not leak one tenant\'s entitlements to another', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), fn () => expect($this->checker->has('finance.fees'))->toBeTrue());
    $this->tenant->runFor($this->tenantB->toContext('beta.test'), fn () => expect($this->checker->has('finance.fees'))->toBeFalse());
});

it('lets a tenant override enable a feature the plan withholds', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    TenantEntitlement::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'feature_code' => 'hr.payroll',
        'enabled' => true,
        'source' => 'negotiated',
        'reason' => 'Included in signed enterprise agreement.',
    ]);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('hr.payroll'))->toBeTrue();
    });
});

it('ignores an override that has expired', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    TenantEntitlement::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'feature_code' => 'hr.payroll',
        'enabled' => true,
        'source' => 'trial',
        'valid_from' => now()->subMonths(2),
        'valid_until' => now()->subDay(),
    ]);

    // An expired override must stop applying on its own, without waiting for
    // a cleanup job that might never run.
    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('hr.payroll'))->toBeFalse();
    });
});

it('ignores an override that has not started yet', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    TenantEntitlement::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'feature_code' => 'hr.payroll',
        'enabled' => true,
        'source' => 'scheduled',
        'valid_from' => now()->addWeek(),
    ]);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('hr.payroll'))->toBeFalse();
    });
});

it('lets an override revoke a feature the plan grants', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    TenantEntitlement::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'feature_code' => 'finance.fees',
        'enabled' => false,
        'source' => 'manual',
        'reason' => 'Suspended pending compliance review.',
    ]);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('finance.fees'))->toBeFalse();
    });
});

it('keeps access during past_due and grace so a payment failure is not a lockout', function (string $status) {
    subscribe($this->tenantA->uuid, $this->plan->id, $status);

    // Plan §12: billing failure degrades access, it never destroys or denies
    // a school its own records outright.
    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('finance.fees'))->toBeTrue();
    });
})->with(['trialing', 'active', 'past_due', 'grace']);

it('withdraws access once the subscription is cancelled or terminated', function (string $status) {
    subscribe($this->tenantA->uuid, $this->plan->id, $status);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('finance.fees'))->toBeFalse();
    });
})->with(['cancelled', 'terminated', 'pending', 'paused']);

it('throws when ensure() is called for a feature the tenant lacks', function () {
    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        $this->checker->ensure('finance.fees');
    });
})->throws(EntitlementDenied::class);

it('preserves the denial status and only exposes a valid configured upgrade URL', function () {
    config()->set('saas.billing.upgrade_url', 'https://platform.example.test/upgrade');

    $response = EntitlementDenied::withMessage('storage.export', 'Exports are unavailable.', 403)->render();

    expect($response->getStatusCode())->toBe(403)
        ->and($response->getData(true))->toMatchArray([
            'error' => 'entitlement_denied',
            'feature' => 'storage.export',
            'upgrade_url' => 'https://platform.example.test/upgrade',
        ]);

    config()->set('saas.billing.upgrade_url', 'javascript:alert(1)');

    expect(EntitlementDenied::forFeature('storage.export')->render()->getData(true)['upgrade_url'])->toBeNull();
});

it('keeps a customer on the plan version they subscribed to', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);

    // A newer version of the same plan drops a feature. The existing customer
    // must be unaffected — republishing a plan is not a contract rewrite.
    $v2 = Plan::create([
        'plan_code' => 'growth',
        'version' => 2,
        'display_name' => 'Growth',
        'billing_interval' => 'monthly',
        'currency' => 'USD',
        'price_cents' => 0,
    ]);
    PlanFeature::create(['plan_id' => $v2->id, 'feature_code' => 'finance.fees', 'enabled' => false]);

    app(FeatureEntitlementService::class)->flushCache($this->tenantA->uuid);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        expect($this->checker->has('finance.fees'))->toBeTrue();
    });
});

it('reflects a plan change once the cache is flushed', function () {
    subscribe($this->tenantA->uuid, $this->plan->id);
    $service = app(FeatureEntitlementService::class);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), fn () => expect($this->checker->has('finance.fees'))->toBeTrue());

    PlanFeature::where('plan_id', $this->plan->id)
        ->where('feature_code', 'finance.fees')
        ->update(['enabled' => false]);

    $service->flushCache($this->tenantA->uuid);

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), fn () => expect($this->checker->has('finance.fees'))->toBeFalse());
});
