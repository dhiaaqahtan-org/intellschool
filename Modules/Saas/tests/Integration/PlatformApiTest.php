<?php

use App\Http\Middleware\Init;
use Illuminate\Support\Facades\Bus;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Jobs\ProvisionTenantJob;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlatformUser;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

function apiOperator(string $role = 'admin'): PlatformUser
{
    return PlatformUser::create([
        'name' => 'API '.$role,
        'email' => $role.'-'.uniqid().'@api.test',
        'password' => 'secret-password',
        'role' => $role,
        'status' => 'active',
    ]);
}

beforeEach(function () {
    config()->set('saas.hosts.marketing', 'localhost');
    config()->set('saas.hosts.platform', 'localhost');
    config()->set('app.url', 'http://localhost');
    app('url')->forceRootUrl('http://localhost');
    $this->withoutMiddleware(Init::class);
});

it('creates a real pending tenant and queues its provisioning run', function () {
    Bus::fake([ProvisionTenantJob::class]);

    $response = $this->actingAs(apiOperator(), 'platform')
        ->postJson('http://localhost/api/saas/platform/tenants', [
            'display_name' => 'API School',
            'slug' => 'api-school',
            'owner_name' => 'School Owner',
            'owner_email' => 'owner@api-school.test',
            'locale' => 'ar',
            'timezone' => 'Asia/Aden',
        ]);

    $response->assertAccepted()
        ->assertJsonPath('tenant.slug', 'api-school')
        ->assertJsonPath('tenant.status', 'pending')
        ->assertJsonPath('tenant.provisioning_state', 'queued')
        ->assertJsonPath('provisioning.state', 'queued');

    $tenant = Tenant::where('slug', 'api-school')->firstOrFail();

    expect($tenant->owners()->where('email', 'owner@api-school.test')->exists())->toBeTrue()
        ->and($tenant->provisioningRuns()->count())->toBe(1);

    Bus::assertDispatched(
        ProvisionTenantJob::class,
        fn (ProvisionTenantJob $job) => $job->runUuid === $tenant->provisioningRuns()->first()->uuid,
    );
});

it('keeps tenant creation and lifecycle mutation away from readonly operators', function () {
    Bus::fake([ProvisionTenantJob::class]);
    $tenant = $this->makeTenant('readonly-school', 'readonly-school.test');
    $operator = apiOperator('readonly');

    $this->actingAs($operator, 'platform')
        ->postJson('http://localhost/api/saas/platform/tenants', [
            'display_name' => 'Forbidden School',
            'slug' => 'forbidden-school',
        ])
        ->assertForbidden();

    $this->actingAs($operator, 'platform')
        ->patchJson('http://localhost/api/saas/platform/tenants/'.$tenant->uuid.'/status', [
            'status' => 'suspended',
            'reason' => 'Readonly operators must not mutate tenants.',
        ])
        ->assertForbidden();
});

it('returns sanitized tenant metadata without database credential references', function () {
    $tenant = $this->makeTenant('safe-school', 'safe-school.test');

    $response = $this->actingAs(apiOperator('readonly'), 'platform')
        ->getJson('http://localhost/api/saas/platform/tenants/'.$tenant->uuid);

    $response->assertOk()
        ->assertJsonPath('uuid', $tenant->uuid)
        ->assertJsonPath('database.cluster', 'default')
        ->assertJsonMissingPath('database.secret_ref')
        ->assertJsonMissing(['vault://secret/tenant-safe-school']);
});

it('validates and applies supported tenant lifecycle transitions', function () {
    $tenant = $this->makeTenant('lifecycle-school', 'lifecycle-school.test');
    $operator = apiOperator('admin');

    $this->actingAs($operator, 'platform')
        ->patchJson('http://localhost/api/saas/platform/tenants/'.$tenant->uuid.'/status', [
            'status' => 'suspended',
            'reason' => 'The compliance review requires temporary read-only access.',
        ])
        ->assertOk()
        ->assertJsonPath('tenant.status', 'suspended');

    expect($tenant->refresh()->status)->toBe(TenantStatus::Suspended)
        ->and($tenant->suspended_at)->not->toBeNull();

    $this->actingAs($operator, 'platform')
        ->patchJson('http://localhost/api/saas/platform/tenants/'.$tenant->uuid.'/status', [
            'status' => 'read_only',
            'reason' => 'This is not a valid persisted lifecycle state.',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('fails closed on public pricing until the commercial claim gate is enabled', function () {
    Plan::create([
        'plan_code' => 'approved',
        'version' => 1,
        'display_name' => 'Approved',
        'billing_interval' => 'monthly',
        'currency' => 'USD',
        'price_cents' => 1000,
        'active_from' => now()->subMinute(),
        'is_public' => true,
    ]);

    config()->set('saas.claims.publish_pricing', false);

    $this->getJson('http://localhost/api/saas/plans')->assertNotFound();

    config()->set('saas.claims.publish_pricing', true);

    $this->getJson('http://localhost/api/saas/plans')
        ->assertOk()
        ->assertJsonPath('plans.0.code', 'approved');
});

it('discovers only ready active tenants and accepts issued subdomains without fake verification', function () {
    $active = $this->makeTenant('discoverable', 'discoverable.test');
    $suspended = $this->makeTenant('not-discoverable', 'not-discoverable.test', TenantStatus::Suspended);

    expect($active->domains->first()->verified_at)->toBeNull();

    $this->getJson('http://localhost/api/saas/discover/discoverable')
        ->assertOk()
        ->assertJsonPath('found', true)
        ->assertJsonPath('tenant.host', 'discoverable.test');

    $this->getJson('http://localhost/api/saas/discover/'.$suspended->slug)
        ->assertNotFound();
});
