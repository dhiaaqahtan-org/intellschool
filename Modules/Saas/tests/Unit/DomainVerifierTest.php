<?php

use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Services\DomainVerifier;
use Modules\Saas\Services\TenantResolver;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Verification is what stands between "a school typed a hostname" and "we route
 * that hostname's traffic". These lean on the refusal cases: a check that
 * passes when it should not hands one party's domain to another.
 *
 * Uses the tenancy base case so the landlord connection is a throwaway SQLite
 * file — verify() persists on success, and a unit test must not write that into
 * a real database.
 */
uses(TenancyTestCase::class);

function customDomainFor(string $hostname, array $attributes = []): TenantDomain
{
    $tenant = test()->makeTenant('alpha', 'alpha.intellschool.com');

    return TenantDomain::create(array_merge([
        'tenant_uuid' => $tenant->uuid,
        'hostname' => $hostname,
        'type' => TenantDomain::TYPE_CUSTOM,
        'is_primary' => false,
        'verification_token' => str_repeat('a', 64),
    ], $attributes));
}

function verifierReturning(array $records): DomainVerifier
{
    return new DomainVerifier(
        app(TenantResolver::class),
        fn (string $name) => $records,
    );
}

beforeEach(function () {
    config()->set('saas.domains.verification_prefix', '_saas-verify');
});

it('builds the record name the school has to publish', function () {
    expect(verifierReturning([])->recordName(customDomainFor('tamjeed.com')))
        ->toBe('_saas-verify.tamjeed.com');
});

it('rejects a domain with no matching TXT record', function () {
    $result = verifierReturning([])->verify(customDomainFor('tamjeed.com'));

    expect($result['verified'])->toBeFalse()
        ->and($result['reason'])->toContain('No TXT record found');
});

it("rejects a TXT record holding someone else's token", function () {
    $result = verifierReturning([['txt' => str_repeat('b', 64)]])
        ->verify(customDomainFor('tamjeed.com'));

    expect($result['verified'])->toBeFalse()
        ->and($result['reason'])->toContain('none of its values match');
});

it('refuses an empty token rather than matching an empty record', function () {
    // An empty expected value against an empty TXT record would otherwise be a
    // match, verifying a domain nobody proved they own.
    $domain = customDomainFor('tamjeed.com', ['verification_token' => '']);
    $result = verifierReturning([['txt' => '']])->verify($domain);

    expect($result['verified'])->toBeFalse()
        ->and($result['reason'])->toContain('no verification token');
});

it('accepts a TXT record that matches the token and makes it routable', function () {
    $domain = customDomainFor('tamjeed.com');

    expect($domain->isRoutable())->toBeFalse();

    $result = verifierReturning([['txt' => str_repeat('a', 64)]])->verify($domain);

    expect($result['verified'])->toBeTrue()
        ->and($domain->fresh()->verified_at)->not->toBeNull()
        ->and($domain->fresh()->isRoutable())->toBeTrue();
});

it('reads a token that DNS split across multiple strings', function () {
    // Long tokens are exactly the ones a resolver chunks, so reading only the
    // `txt` key would fail on the records most likely to be split.
    $result = verifierReturning([[
        'txt' => 'ignored',
        'entries' => [str_repeat('a', 40), str_repeat('a', 24)],
    ]])->verify(customDomainFor('tamjeed.com'));

    expect($result['verified'])->toBeTrue();
});

it('ignores unrelated TXT records sitting alongside the right one', function () {
    $result = verifierReturning([
        ['txt' => 'v=spf1 include:_spf.google.com ~all'],
        ['txt' => str_repeat('a', 64)],
    ])->verify(customDomainFor('tamjeed.com'));

    expect($result['verified'])->toBeTrue();
});

it('treats an issued subdomain as trusted without any lookup', function () {
    $domain = customDomainFor('tamjeed.intellschool.com', [
        'type' => TenantDomain::TYPE_SUBDOMAIN,
        'verification_token' => null,
    ]);

    $verifier = new DomainVerifier(
        app(TenantResolver::class),
        fn (string $name) => throw new RuntimeException('DNS must not be queried for an issued subdomain'),
    );

    expect($verifier->verify($domain)['verified'])->toBeTrue();
});

it('does not re-check a domain that is already verified', function () {
    $domain = customDomainFor('tamjeed.com', ['verified_at' => now()]);

    $verifier = new DomainVerifier(
        app(TenantResolver::class),
        fn (string $name) => throw new RuntimeException('should not re-query DNS'),
    );

    expect($verifier->verify($domain)['reason'])->toBe('Already verified.');
});

it('honours a changed verification prefix', function () {
    config()->set('saas.domains.verification_prefix', '_intellschool-verify');

    expect(verifierReturning([])->recordName(customDomainFor('tamjeed.com')))
        ->toBe('_intellschool-verify.tamjeed.com');
});

it('resolves a verified custom domain to its tenant, and refuses it before', function () {
    $domain = customDomainFor('tamjeed.com');
    $resolver = app(TenantResolver::class);

    // Before verification the host is a tenant candidate that resolves to
    // nothing, which is what makes ResolveTenant 404 it.
    expect($resolver->resolve('tamjeed.com'))->toBeNull();

    verifierReturning([['txt' => str_repeat('a', 64)]])->verify($domain);

    expect($resolver->resolve('tamjeed.com')?->uuid)->toBe($domain->tenant_uuid);
});
