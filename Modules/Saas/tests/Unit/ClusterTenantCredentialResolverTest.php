<?php

use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Services\ClusterTenantCredentialResolver;

/**
 * The resolver decides which credentials a tenant request connects with, so
 * every branch that could hand back the WRONG credentials — or fall back to the
 * landlord's — is asserted here rather than left to integration coverage.
 */
uses(Tests\TestCase::class);

function credentialContextFor(string $secretRef, string $cluster = 'default'): TenantContext
{
    return new TenantContext(
        uuid: '11111111-1111-4111-8111-111111111111',
        slug: 'alpha',
        status: TenantStatus::Active,
        databaseName: 'tnt_alpha',
        connectionName: 'tenant',
        host: 'alpha.intellschool.com',
        cluster: $cluster,
        secretRef: $secretRef,
    );
}

beforeEach(function () {
    config()->set('saas.clusters', [
        'default' => [
            'host' => '10.0.0.5',
            'port' => 3306,
            'username' => 'saas_tenant',
            'password' => 's3cret',
        ],
        'eu1' => [
            'host' => '10.0.1.5',
            'port' => 3307,
            'username' => 'eu_tenant',
            'password' => 'eu-s3cret',
        ],
    ]);
});

it('resolves the env pointer the provisioner writes', function () {
    $resolver = app(ClusterTenantCredentialResolver::class);

    expect($resolver->resolveFor(credentialContextFor('env:SAAS_CLUSTER_DEFAULT')))
        ->toBe([
            'host' => '10.0.0.5',
            'port' => 3306,
            'username' => 'saas_tenant',
            'password' => 's3cret',
        ]);
});

it('routes a tenant to its own cluster, not the default one', function (string $ref) {
    $resolver = app(ClusterTenantCredentialResolver::class);

    expect($resolver->resolveFor(credentialContextFor($ref, 'eu1'))['username'])
        ->toBe('eu_tenant');
})->with([
    'env pointer' => ['env:SAAS_CLUSTER_EU1'],
    'cluster pointer' => ['cluster:eu1'],
    'no pointer, context cluster' => [''],
]);

it('refuses an unknown pointer scheme instead of serving default credentials', function () {
    $resolver = app(ClusterTenantCredentialResolver::class);

    // A vault:// pointer means the operator intended a secret manager. Handing
    // back cluster credentials would be working-but-wrong.
    expect(fn () => $resolver->resolveFor(credentialContextFor('vault://tenant/alpha')))
        ->toThrow(RuntimeException::class, 'Unsupported tenant credential pointer scheme');
});

it('fails closed when the cluster is not configured at all', function () {
    $resolver = app(ClusterTenantCredentialResolver::class);

    expect(fn () => $resolver->resolveFor(credentialContextFor('cluster:apac1', 'apac1')))
        ->toThrow(RuntimeException::class, 'No credentials configured for tenant database cluster [apac1]');
});

it('fails closed on a missing username rather than connecting anonymously', function () {
    config()->set('saas.clusters.default.username', '');

    expect(fn () => app(ClusterTenantCredentialResolver::class)->resolveFor(credentialContextFor('')))
        ->toThrow(RuntimeException::class, 'credential [username] is missing');
});

it('refuses an empty database password in production', function () {
    config()->set('saas.clusters.default.password', '');
    app()->instance('env', 'production');

    expect(fn () => app(ClusterTenantCredentialResolver::class)->resolveFor(credentialContextFor('')))
        ->toThrow(RuntimeException::class, 'empty database password');
});

it('still allows a passwordless local database outside production', function () {
    config()->set('saas.clusters.default.password', '');

    expect(app(ClusterTenantCredentialResolver::class)->resolveFor(credentialContextFor(''))['password'])
        ->toBe('');
});

it('never falls back to the landlord connection credentials', function () {
    config()->set('database.connections.landlord.username', 'landlord_user');
    config()->set('saas.clusters', []);

    // The landlord user can read every tenant database. Silently borrowing it
    // would turn a misconfiguration into a cross-tenant read.
    expect(fn () => app(ClusterTenantCredentialResolver::class)->resolveFor(credentialContextFor('')))
        ->toThrow(RuntimeException::class);
});
