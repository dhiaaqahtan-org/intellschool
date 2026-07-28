<?php

use App\Http\Middleware\Init;
use Illuminate\Support\Facades\Bus;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Http\Middleware\RequireLandlordHost;
use Modules\Saas\Jobs\ProvisionTenantJob;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlatformUser;
use Modules\Saas\Models\Landlord\ProvisioningRun;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

function apiPlatformOperator(string $role = 'super_admin'): PlatformUser
{
    return PlatformUser::create([
        'name' => 'API '.$role,
        'email' => $role.'-'.uniqid().'@api.platform.test',
        'password' => 'secret-password',
        'role' => $role,
        'status' => 'active',
    ]);
}

beforeEach(function () {
    config()->set('saas.hosts.marketing', 'localhost');
    config()->set('saas.hosts.platform', 'localhost');
    $this->withHeader('Host', 'localhost');
    $this->withServerVariables(['HTTP_HOST' => 'localhost', 'SERVER_NAME' => 'localhost']);
    $this->withoutMiddleware(RequireLandlordHost::class);
    $this->withoutMiddleware(Init::class);
});

it('allows readonly operators to inspect but never mutate platform API tenants', function () {
    $tenant = $this->makeTenant('readonly-api', 'readonly-api.test');
    config()->set('saas.tenancy.enabled', false);

    $this->actingAs(apiPlatformOperator('readonly'), 'platform')
        ->getJson('/api/saas/platform/tenants')
        ->assertOk()
        ->assertJsonPath('data.0.uuid', $tenant->uuid);

    $this->postJson('/api/saas/platform/tenants', [
        'display_name' => 'Forbidden School',
    ])->assertForbidden();

    expect(Tenant::where('display_name', 'Forbidden School')->exists())->toBeFalse();
});

it('creates a real pending tenant and provisioning run through the platform API', function () {
    Bus::fake([ProvisionTenantJob::class]);
    config()->set('saas.tenancy.enabled', false);

    $response = $this->actingAs(apiPlatformOperator('admin'), 'platform')
        ->postJson('/api/saas/platform/tenants', [
            'display_name' => 'API Created School',
            'slug' => 'api-created-school',
            'owner_name' => 'School Owner',
            'owner_email' => 'owner@api-created.test',
            'locale' => 'ar',
            'timezone' => 'Asia/Aden',
        ]);

    $response
        ->assertStatus(202)
        ->assertJsonPath('tenant.slug', 'api-created-school')
        ->assertJsonPath('tenant.status', TenantStatus::Pending->value)
        ->assertJsonPath('provisioning.state', ProvisioningState::Queued->value);

    $tenant = Tenant::where('slug', 'api-created-school')->firstOrFail();

    expect($tenant->owners()->where('email', 'owner@api-created.test')->exists())->toBeTrue()
        ->and($tenant->provisioningRuns()->count())->toBe(1);

    Bus::assertDispatched(ProvisionTenantJob::class);
});

it('rejects invalid tenant lifecycle transitions with a conflict', function () {
    $tenant = $this->makeTenant('transition-api', 'transition-api.test');
    config()->set('saas.tenancy.enabled', false);

    $this->actingAs(apiPlatformOperator('admin'), 'platform')
        ->patchJson("/api/saas/platform/tenants/{$tenant->uuid}/status", [
            'status' => TenantStatus::Active->value,
            'reason' => 'No lifecycle change is required.',
        ])
        ->assertStatus(409)
        ->assertJsonPath('error', 'invalid_tenant_transition');

    expect($tenant->fresh()->status)->toBe(TenantStatus::Active);
});

it('returns the latest provisioning projection without database credentials', function () {
    $tenant = $this->makeTenant('progress-api', 'progress-api.test');
    $run = ProvisioningRun::create([
        'tenant_uuid' => $tenant->uuid,
        'idempotency_key' => 'progress-api-key',
        'state' => ProvisioningState::Migrating->value,
        'step' => 'migrate',
        'progress' => 35,
        'attempts' => 1,
    ]);
    config()->set('saas.tenancy.enabled', false);

    $response = $this->actingAs(apiPlatformOperator('readonly'), 'platform')
        ->getJson("/api/saas/platform/tenants/{$tenant->uuid}/provisioning");

    $response
        ->assertOk()
        ->assertJsonPath('provisioning.uuid', $run->uuid)
        ->assertJsonPath('provisioning.progress', 35)
        ->assertJsonMissingPath('provisioning.database_name')
        ->assertJsonMissingPath('provisioning.secret_ref');
});

it('fails public pricing closed and discovers only ready active tenants', function () {
    $tenant = $this->makeTenant('discoverable', 'discoverable.test');
    $tenant->domains()->update(['verified_at' => now()]);
    config()->set('saas.tenancy.enabled', false);
    config()->set('saas.claims.publish_pricing', false);

    $this->getJson('/api/saas/plans')->assertNotFound();

    Plan::create([
        'plan_code' => 'public-api',
        'version' => 1,
        'display_name' => 'Public API',
        'billing_interval' => 'monthly',
        'currency' => 'USD',
        'price_cents' => 4900,
        'active_from' => now()->subMinute(),
        'is_public' => true,
    ]);
    config()->set('saas.claims.publish_pricing', true);

    $this->getJson('/api/saas/plans')
        ->assertOk()
        ->assertJsonPath('plans.0.code', 'public-api');

    $this->getJson('/api/saas/discover/discoverable')
        ->assertOk()
        ->assertJsonPath('tenant.host', 'discoverable.test');

    $tenant->update(['status' => TenantStatus::Suspended->value]);

    $this->getJson('/api/saas/discover/discoverable')->assertNotFound();
});

it('creates plan versions as private drafts and rejects unknown feature codes', function () {
    config()->set('saas.tenancy.enabled', false);
    $operator = apiPlatformOperator('billing');

    $payload = [
        'plan_code' => 'api-growth',
        'display_name' => 'API Growth',
        'billing_interval' => 'monthly',
        'currency' => 'usd',
        'price_cents' => 9900,
        'features' => [
            ['feature_code' => 'students.core', 'enabled' => true],
        ],
    ];

    $this->actingAs($operator, 'platform')
        ->postJson('/api/saas/platform/plans', $payload)
        ->assertCreated()
        ->assertJsonPath('plan.version', 1)
        ->assertJsonPath('plan.currency', 'USD')
        ->assertJsonPath('plan.is_public', false);

    $payload['features'][0]['feature_code'] = 'unknown.unreleased';
    $this->postJson('/api/saas/platform/plans', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('features.0.feature_code');
});
