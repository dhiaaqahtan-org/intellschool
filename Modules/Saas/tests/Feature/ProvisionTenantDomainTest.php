<?php

use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * The command drives a root-owned script through sudo, so the gate in front of
 * it is the security boundary. Certbot proves control of whatever domain points
 * at this server — without the registration check, this command would be a way
 * to mint certificates for hosts nobody added.
 *
 * The sudo call itself is not exercised: --dry-run stops before it, which is
 * also the only way these can run on a machine with no nginx.
 */
uses(TenancyTestCase::class);

function domainCommand(array $args): int
{
    return Artisan::call('saas:provision-domain', $args + ['--dry-run' => true]);
}

it('refuses a domain that belongs to no tenant', function () {
    expect(domainCommand(['domain' => 'not-ours.com']))->toBe(1)
        ->and(Artisan::output())->toContain('is not registered to any tenant');
});

it('refuses a custom domain that has not passed DNS verification', function () {
    $tenant = $this->makeTenant('tamjeed', 'tamjeed.intellschool.com');

    TenantDomain::create([
        'tenant_uuid' => $tenant->uuid,
        'hostname' => 'tamjeed.com',
        'type' => TenantDomain::TYPE_CUSTOM,
        'verification_token' => str_repeat('a', 64),
    ]);

    expect(domainCommand(['domain' => 'tamjeed.com']))->toBe(1)
        ->and(Artisan::output())->toContain('has not passed DNS verification');
});

it('proceeds for a verified custom domain', function () {
    $tenant = $this->makeTenant('tamjeed', 'tamjeed.intellschool.com');

    TenantDomain::create([
        'tenant_uuid' => $tenant->uuid,
        'hostname' => 'tamjeed.com',
        'type' => TenantDomain::TYPE_CUSTOM,
        'verification_token' => str_repeat('a', 64),
        'verified_at' => now(),
    ]);

    expect(domainCommand(['domain' => 'tamjeed.com']))->toBe(0)
        ->and(Artisan::output())->toContain('tenant-domain-provision');
});

it('lets an operator override verification deliberately', function () {
    $tenant = $this->makeTenant('tamjeed', 'tamjeed.intellschool.com');

    TenantDomain::create([
        'tenant_uuid' => $tenant->uuid,
        'hostname' => 'tamjeed.com',
        'type' => TenantDomain::TYPE_CUSTOM,
        'verification_token' => str_repeat('a', 64),
    ]);

    expect(domainCommand(['domain' => 'tamjeed.com', '--allow-unverified' => true]))->toBe(0);
});

it('normalises the host before looking it up', function () {
    $tenant = $this->makeTenant('tamjeed', 'tamjeed.intellschool.com');

    TenantDomain::create([
        'tenant_uuid' => $tenant->uuid,
        'hostname' => 'tamjeed.com',
        'type' => TenantDomain::TYPE_CUSTOM,
        'verified_at' => now(),
    ]);

    // Stored hostnames are normalised on save, so the lookup has to normalise
    // too or a capitalised argument silently looks like an unknown domain.
    expect(domainCommand(['domain' => 'TAMJEED.com.']))->toBe(0);
});

it('rejects an argument that is not a hostname at all', function (string $bad) {
    expect(domainCommand(['domain' => $bad]))->toBe(1);
})->with([
    'scheme and path' => ['https://tamjeed.com/x'],
    'shell metacharacters' => ['tamjeed.com; rm -rf /'],
    'space' => ['tam jeed.com'],
    'empty' => [''],
]);

it('passes flags through as separate arguments rather than a shell string', function () {
    $tenant = $this->makeTenant('tamjeed', 'tamjeed.intellschool.com');

    TenantDomain::create([
        'tenant_uuid' => $tenant->uuid,
        'hostname' => 'tamjeed.com',
        'type' => TenantDomain::TYPE_CUSTOM,
        'verified_at' => now(),
    ]);

    domainCommand([
        'domain' => 'tamjeed.com',
        '--email' => 'ops@intellschool.com',
        '--staging' => true,
    ]);

    expect(Artisan::output())
        ->toContain('--email=ops@intellschool.com')
        ->toContain('--staging')
        ->toContain('sudo -n');
});
