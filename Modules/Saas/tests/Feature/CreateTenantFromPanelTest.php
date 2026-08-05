<?php

use Modules\Saas\Models\Landlord\PlatformUser;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDatabase;
use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Services\ClusterTenantCredentialResolver;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Creating a school from the platform panel, on hosting where each database
 * comes with its own MySQL user.
 *
 * The credentials arrive over HTTP, get stored, and are later used to open a
 * database connection — so the assertions here are about what is accepted, what
 * is stored, and what is never echoed back.
 */
uses(TenancyTestCase::class);

function panelOperator(): PlatformUser
{
    return PlatformUser::create([
        'name' => 'Ops',
        'email' => 'ops-'.uniqid().'@platform.test',
        'password' => 'secret-password',
        'role' => 'admin',
        'status' => 'active',
    ]);
}

function submitTenant(array $overrides = []): \Illuminate\Testing\TestResponse
{
    // Platform routes register against `localhost` when no platform host is
    // configured, which is the case in tests; Init is skipped because it is the
    // legacy ERP initializer and has no business on the control plane.
    return test()
        ->withoutMiddleware(\App\Http\Middleware\Init::class)
        ->actingAs(panelOperator(), 'platform')
        ->post('http://localhost/platform/tenants', array_merge([
            'display_name' => 'Tamjeed School',
            'slug' => 'tamjeed',
            'locale' => 'en',
            'timezone' => 'UTC',
        ], $overrides));
}

beforeEach(function () {
    // Platform routes register against `localhost` in tests, and
    // RequireLandlordHost checks the configured host against the request's, so
    // both have to agree. Tenancy is off because ResolveTenant would otherwise
    // read `localhost` as an unknown tenant host and 404 the whole request —
    // creating a school is control-plane work either way.
    config()->set('saas.hosts.platform', 'localhost');
    config()->set('saas.tenancy.enabled', false);

    // The provisioning run is dispatched after creation; the panel's job here
    // ends at "recorded correctly", so keep the pipeline out of it.
    Illuminate\Support\Facades\Queue::fake();
});

it('stores an operator-supplied database with its own credentials', function () {
    submitTenant([
        'database_name' => 'u123456789_tamjeed',
        'database_username' => 'u123456789_tamjeed',
        'database_password' => 'hPanel-generated-pw',
    ]);

    $database = TenantDatabase::first();

    expect($database->database_name)->toBe('u123456789_tamjeed')
        ->and($database->db_username)->toBe('u123456789_tamjeed')
        ->and($database->secret_ref)->toBe(TenantDatabase::SECRET_REF_ROW)
        ->and($database->hasOwnCredentials())->toBeTrue();
});

it('encrypts the stored password rather than writing it in the clear', function () {
    submitTenant([
        'database_name' => 'u123456789_tamjeed',
        'database_username' => 'u123456789_tamjeed',
        'database_password' => 'hPanel-generated-pw',
    ]);

    $raw = DB::connection('landlord')->table('saas_tenant_databases')->value('db_password');

    // Anyone who dumps this table must not get a working credential from it.
    expect($raw)->not->toContain('hPanel-generated-pw')
        ->and(TenantDatabase::first()->db_password)->toBe('hPanel-generated-pw');
});

it('keeps the credentials out of the model\'s array form', function () {
    submitTenant([
        'database_name' => 'u123456789_tamjeed',
        'database_username' => 'u123456789_tamjeed',
        'database_password' => 'hPanel-generated-pw',
    ]);

    expect(array_keys(TenantDatabase::first()->toArray()))
        ->not->toContain('db_password')
        ->not->toContain('db_username')
        ->not->toContain('secret_ref');
});

it('resolves a tenant to its own credentials, not the shared cluster user', function () {
    config()->set('saas.clusters.default', [
        'host' => '10.0.0.5', 'port' => 3306,
        'username' => 'shared_cluster_user', 'password' => 'cluster-pw',
    ]);

    submitTenant([
        'database_name' => 'u123456789_tamjeed',
        'database_username' => 'u123456789_tamjeed',
        'database_password' => 'hPanel-generated-pw',
    ]);

    $context = Tenant::first()->toContext('tamjeed.intellschool.com');
    $credentials = app(ClusterTenantCredentialResolver::class)->resolveFor($context);

    expect($credentials['username'])->toBe('u123456789_tamjeed')
        ->and($credentials['password'])->toBe('hPanel-generated-pw')
        // Host and port are infrastructure and still come from the cluster.
        ->and($credentials['host'])->toBe('10.0.0.5');
});

it('falls back to the cluster user when only a database name is given', function () {
    config()->set('saas.clusters.default', [
        'host' => '10.0.0.5', 'port' => 3306,
        'username' => 'shared_cluster_user', 'password' => 'cluster-pw',
    ]);

    submitTenant(['database_name' => 'u123456789_tamjeed']);

    $database = TenantDatabase::first();
    $credentials = app(ClusterTenantCredentialResolver::class)
        ->resolveFor(Tenant::first()->toContext('tamjeed.intellschool.com'));

    expect($database->secret_ref)->toBe('env:SAAS_CLUSTER_DEFAULT')
        ->and($database->hasOwnCredentials())->toBeFalse()
        ->and($credentials['username'])->toBe('shared_cluster_user');
});

it('rejects a database name that is not a plain identifier', function (string $name) {
    submitTenant(['database_name' => $name])->assertSessionHasErrors('database_name');

    expect(Tenant::count())->toBe(0);
})->with([
    'backtick escape' => ['tnt`; DROP DATABASE `x'],
    'statement terminator' => ['u1_a; SELECT 1'],
    'hyphen' => ['u1-tamjeed'],
    'space' => ['u1 tamjeed'],
]);

it('refuses a username with no password, and a password with no username', function (array $fields, string $missing) {
    submitTenant($fields + ['database_name' => 'u123456789_tamjeed'])
        ->assertSessionHasErrors($missing);
})->with([
    'username alone' => [['database_username' => 'u1_tamjeed'], 'database_password'],
    'password alone' => [['database_password' => 'pw'], 'database_username'],
]);

it('refuses credentials with no database name to apply them to', function () {
    submitTenant([
        'database_username' => 'u123456789_tamjeed',
        'database_password' => 'pw',
    ])->assertSessionHasErrors('database_name');

    expect(Tenant::count())->toBe(0);
});

it('registers an issued subdomain as routable straight away', function () {
    submitTenant(['hostname' => 'tamjeed.intellschool.com', 'domain_type' => 'subdomain']);

    $domain = TenantDomain::where('hostname', 'tamjeed.intellschool.com')->first();

    expect($domain->is_primary)->toBeTrue()
        ->and($domain->isRoutable())->toBeTrue()
        ->and($domain->verification_token)->toBeNull();
});

it('holds a school-owned domain unroutable until DNS is proven', function () {
    submitTenant(['hostname' => 'tamjeed.com', 'domain_type' => 'custom']);

    $domain = TenantDomain::where('hostname', 'tamjeed.com')->first();

    // Entering it on the create form proves nothing about who controls the DNS.
    expect($domain->isRoutable())->toBeFalse()
        ->and($domain->verified_at)->toBeNull()
        ->and($domain->verification_token)->not->toBeNull();
});

it('refuses a hostname another tenant already holds', function () {
    submitTenant(['hostname' => 'tamjeed.com', 'domain_type' => 'custom']);

    submitTenant([
        'display_name' => 'Other School',
        'slug' => 'other',
        'hostname' => 'tamjeed.com',
        'domain_type' => 'custom',
    ])->assertSessionHasErrors('hostname');

    expect(TenantDomain::where('hostname', 'tamjeed.com')->count())->toBe(1);
});

it('normalises the hostname before storing it', function () {
    submitTenant(['hostname' => '  TAMJEED.COM.  ', 'domain_type' => 'custom']);

    expect(TenantDomain::where('hostname', 'tamjeed.com')->exists())->toBeTrue();
});
