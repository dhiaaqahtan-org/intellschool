<?php

use Modules\Saas\Domain\Tenancy\HostNormalizer;

/**
 * Host handling is where multi-tenant systems get broken into, so these are
 * adversarial rather than illustrative (plan §17.1: "custom-domain
 * verification and host-header attacks").
 */

it('lowercases and strips port and trailing dot', function (string $input, string $expected) {
    expect(HostNormalizer::normalize($input))->toBe($expected);
})->with([
    ['Alpha.Test', 'alpha.test'],
    ['alpha.test:8443', 'alpha.test'],
    ['alpha.test.', 'alpha.test'],
    ['  ALPHA.TEST:80  ', 'alpha.test'],
]);

it('rejects hosts that cannot be a hostname', function (?string $input) {
    expect(HostNormalizer::normalize($input))->toBeNull();
})->with([
    [null],
    [''],
    ['   '],
    ['-leading-hyphen.test'],
    ['trailing-hyphen-.test'],
    ['has space.test'],
    ['under_score.test'],
    ['a..b.test'],
    [str_repeat('a', 64).'.test'],          // label over 63 chars
    [str_repeat('a.', 200).'test'],          // host over 253 chars
]);

it('folds unicode to punycode so lookalike domains cannot diverge', function () {
    // Cyrillic "а" (U+0430) renders identically to Latin "a". Without folding,
    // two visually identical hostnames would be two different cache keys and
    // two different lookups.
    $cyrillic = HostNormalizer::normalize('аlpha.test');

    expect($cyrillic)->not->toBe('alpha.test');
    expect($cyrillic)->toStartWith('xn--');
})->skip(! function_exists('idn_to_ascii'), 'ext-intl not installed');

it('does not let a lookalike domain suffix-match a real one', function () {
    // The danger is a resolver that uses str_ends_with or LIKE. Normalisation
    // must keep these distinct strings so an exact match cannot conflate them.
    $real = HostNormalizer::normalize('school.example.com');
    $evil = HostNormalizer::normalize('school.example.com.attacker.test');

    expect($real)->not->toBe($evil);
});

it('extracts a tenant subdomain only one label deep', function (string $host, ?string $expected) {
    expect(HostNormalizer::extractSubdomain($host, '.product.example'))->toBe($expected);
})->with([
    ['alpha.product.example', 'alpha'],
    // Nested subdomains are not an addressing scheme we support; accepting
    // them silently widens the attack surface for no benefit.
    ['a.b.product.example', null],
    // The bare suffix is not a tenant.
    ['product.example', null],
    // A different registrable domain that merely ends similarly.
    ['alpha.notproduct.example', null],
    ['evil-product.example', null],
]);

it('returns null for a subdomain when no suffix is configured', function () {
    expect(HostNormalizer::extractSubdomain('alpha.product.example', null))->toBeNull()
        ->and(HostNormalizer::extractSubdomain('alpha.product.example', ''))->toBeNull();
});

it('accepts ip literals without treating them as tenants', function () {
    expect(HostNormalizer::normalize('127.0.0.1:8000'))->toBe('127.0.0.1')
        ->and(HostNormalizer::normalize('[::1]:8000'))->toBe('[::1]');
});
