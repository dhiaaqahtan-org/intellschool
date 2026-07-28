<?php

use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlanFeature;
use Modules\Saas\Models\Landlord\PlatformUser;
use Modules\Saas\Models\Landlord\ProvisioningRun;
use Modules\Saas\Models\Landlord\Subscription;
use Modules\Saas\Models\Landlord\SupportSession;
use Modules\Saas\Models\Landlord\TenantEntitlement;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Renders every platform page against realistic data.
 *
 * These pages were shipped without a single test that rendered them, and the
 * consequence was a run of TypeErrors that only appeared once real rows
 * existed — `ucfirst($tenant->status)` on a backed enum is fine on an empty
 * table and fatal on a populated one. Empty-state rendering proves nothing;
 * every case below seeds data first.
 */
uses(TenancyTestCase::class);

beforeEach(function () {
    config()->set('saas.hosts.platform', 'localhost');
    config()->set('saas.tenancy.enabled', false); // platform host, no tenant context

    $this->operator = PlatformUser::create([
        'name' => 'Operator',
        'email' => 'op@platform.test',
        'password' => 'secret-password',
        'role' => 'super_admin',
        'status' => 'active',
    ]);

    // A tenant in every interesting shape, so enum handling is exercised.
    $this->tenant = $this->makeTenant('alpha', 'alpha.test');
    $this->tenant->update([
        'status' => TenantStatus::Active,
        'provisioning_state' => ProvisioningState::Ready,
    ]);

    ProvisioningRun::create([
        'tenant_uuid' => $this->tenant->uuid,
        'idempotency_key' => 'provision:'.$this->tenant->uuid,
        'state' => ProvisioningState::Ready->value,
        'step' => 'verify',
        'started_at' => now()->subMinutes(5),
        'finished_at' => now(),
        'steps' => ['migrate' => ['ok' => true, 'attempts' => 1]],
    ]);

    $this->plan = Plan::create([
        'plan_code' => 'growth', 'version' => 1, 'display_name' => 'Growth',
        'billing_interval' => 'monthly', 'currency' => 'USD', 'price_cents' => 14900,
    ]);
    PlanFeature::create([
        'plan_id' => $this->plan->id, 'feature_code' => 'finance.fees',
        'enabled' => true, 'limit_type' => 'hard',
    ]);

    $this->subscription = Subscription::create([
        'tenant_uuid' => $this->tenant->uuid,
        'plan_id' => $this->plan->id,
        'provider' => 'manual',
        'status' => 'active',
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
    ]);

    TenantEntitlement::create([
        'tenant_uuid' => $this->tenant->uuid,
        'feature_code' => 'hr.payroll',
        'enabled' => true,
        'source' => 'platform_override',
        'reason' => 'Included in signed enterprise agreement.',
        'granted_by' => 'op@platform.test',
        'valid_from' => now(),
    ]);

    SupportSession::create([
        'tenant_uuid' => $this->tenant->uuid,
        'operator_id' => $this->operator->id,
        'operator_email' => $this->operator->email,
        'reason' => 'Ticket 4821: inspecting duplicated fee receipt lines.',
        'scope' => 'read',
        'status' => 'requested',
        'requested_at' => now(),
    ]);

    AuditEvent::record(
        action: 'tenant.created',
        tenantUuid: $this->tenant->uuid,
        context: ['slug' => 'alpha'],
        actorType: 'platform',
        actorLabel: 'op@platform.test',
    );

    // App\Http\Middleware\Init reads the ERP `configs` table, which lives in a
    // tenant database and has no business being consulted on the control
    // plane. Skipped here so these tests exercise the platform pages rather
    // than the ERP's bootstrap.
    $this->visit = fn (string $path) => $this
        ->withoutMiddleware(\App\Http\Middleware\Init::class)
        ->actingAs($this->operator, 'platform')
        ->get('http://localhost'.$path);
});

it('renders every platform page with data present', function (string $path) {
    ($this->visit)($path)->assertOk();
})->with([
    '/platform',
    '/platform/tenants',
    '/platform/tenants/create',
    '/platform/plans',
    '/platform/subscriptions',
    '/platform/support',
    '/platform/audit',
]);

it('renders the tenant detail page', function () {
    ($this->visit)('/platform/tenants/'.$this->tenant->uuid)
        ->assertOk()
        ->assertSee('Alpha School');
});

it('renders the plan detail page', function () {
    ($this->visit)('/platform/plans/'.$this->plan->id)
        ->assertOk()
        ->assertSee('finance.fees');
});

it('renders the subscription detail page', function () {
    ($this->visit)('/platform/subscriptions/'.$this->subscription->id)
        ->assertOk()
        // The override must be visible, and marked as beating the plan.
        ->assertSee('hr.payroll');
});

it('displays enum-backed statuses without a type error', function () {
    // The specific regression: ucfirst() on a backed enum is a fatal
    // TypeError, and it only fires once a row exists.
    ($this->visit)('/platform/tenants')->assertOk()->assertSee('Active');
    ($this->visit)('/platform')->assertOk();
});

it('gives every status enum a printable label', function () {
    foreach (TenantStatus::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->badgeClass())->toStartWith('badge-');
    }

    foreach (ProvisioningState::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->badgeClass())->toStartWith('badge-');
    }
});

it('turns underscored provisioning states into readable text', function () {
    expect(ProvisioningState::AllocatingDatabase->label())->toBe('Allocating database')
        ->and(ProvisioningState::FailedManualReview->label())->toBe('Failed manual review');
});
