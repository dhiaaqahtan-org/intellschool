<?php

use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Services\TenantResolver;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Resolution must FAIL CLOSED (plan §6). There is never a fallback to
 * "the default tenant" — that fallback is how one school ends up looking at
 * another school's dashboard.
 */
uses(TenancyTestCase::class);

beforeEach(function () {
    config()->set('saas.hosts.marketing', 'www.product.test');
    config()->set('saas.hosts.platform', 'app.product.test');

    $this->tenantA = $this->makeTenant('alpha', 'alpha.product.test');
    $this->resolver = app(TenantResolver::class);
});

it('resolves a known verified host to its tenant', function () {
    $context = $this->resolver->resolve('alpha.product.test');

    expect($context)->not->toBeNull()
        ->and($context->uuid)->toBe($this->tenantA->uuid);
});

it('returns nothing for an unknown host', function () {
    expect($this->resolver->resolve('nobody.product.test'))->toBeNull();
});

it('does not resolve a host that merely ends with a known host', function () {
    // Catches a resolver that used LIKE or str_ends_with instead of an exact match.
    expect($this->resolver->resolve('evil-alpha.product.test'))->toBeNull()
        ->and($this->resolver->resolve('alpha.product.test.attacker.test'))->toBeNull();
});

it('classifies the marketing and platform hosts as control plane', function () {
    expect($this->resolver->classify('www.product.test')->isControlPlane())->toBeTrue()
        ->and($this->resolver->classify('app.product.test')->isControlPlane())->toBeTrue()
        ->and($this->resolver->classify('alpha.product.test')->isTenantCandidate())->toBeTrue();
});

it('never resolves a tenant on the marketing host', function () {
    expect($this->resolver->resolve('www.product.test'))->toBeNull();
});

it('marks a malformed host invalid rather than guessing', function () {
    expect($this->resolver->classify('has space.test')->isInvalid())->toBeTrue()
        ->and($this->resolver->classify('')->isInvalid())->toBeTrue();
});

it('refuses to route an unverified custom domain', function () {
    TenantDomain::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'hostname' => 'school.customer.test',
        'type' => TenantDomain::TYPE_CUSTOM,
        'is_primary' => false,
        'verification_token' => 'pending-token',
        'verified_at' => null,
    ]);

    // Anyone can point DNS at us; ownership must be proven before traffic
    // for that name reaches a tenant.
    expect($this->resolver->resolve('school.customer.test'))->toBeNull();
});

it('routes a custom domain once verified', function () {
    TenantDomain::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'hostname' => 'school.customer.test',
        'type' => TenantDomain::TYPE_CUSTOM,
        'is_primary' => false,
        'verified_at' => now(),
    ]);

    $this->resolver->forget('school.customer.test');

    expect($this->resolver->resolve('school.customer.test')?->uuid)->toBe($this->tenantA->uuid);
});

it('stops resolving once a tenant stops being servable', function () {
    expect($this->resolver->resolve('alpha.product.test'))->not->toBeNull();

    $this->tenantA->update(['provisioning_state' => \Modules\Saas\Enums\ProvisioningState::Migrating]);

    // The host->uuid mapping may still be cached, but the tenant record is
    // re-checked on every resolve, so a mid-TTL status change takes effect.
    expect($this->resolver->resolve('alpha.product.test'))->toBeNull();
});

it('normalises the incoming host before lookup', function () {
    expect($this->resolver->resolve('ALPHA.Product.Test:8080')?->uuid)->toBe($this->tenantA->uuid)
        ->and($this->resolver->resolve('alpha.product.test.')?->uuid)->toBe($this->tenantA->uuid);
});

it('stores hostnames normalised so the unique index actually holds', function () {
    $domain = TenantDomain::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'hostname' => '  MiXeD.Product.Test.  ',
        'type' => TenantDomain::TYPE_SUBDOMAIN,
    ]);

    expect($domain->fresh()->hostname)->toBe('mixed.product.test');
});

it('rejects an unparseable hostname at write time', function () {
    TenantDomain::create([
        'tenant_uuid' => $this->tenantA->uuid,
        'hostname' => 'not a host',
        'type' => TenantDomain::TYPE_SUBDOMAIN,
    ]);
})->throws(InvalidArgumentException::class);
