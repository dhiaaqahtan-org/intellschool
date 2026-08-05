<?php

use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Services\TenantProvisioner;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Shared hosting cannot CREATE DATABASE and forces its own name prefix, so a
 * tenant there points at a database an operator made by hand.
 *
 * The name reaches a PDO DSN and a backtick-quoted SQL identifier, neither of
 * which can be parameterised — so these concentrate on what happens to a name
 * that should never have been accepted.
 */
uses(TenancyTestCase::class);

function adoptedNameOf(?string $name): ?string
{
    $tenant = new Tenant();
    $tenant->forceFill([
        'uuid' => 'aaaaaaaa-1111-4111-8111-111111111111',
        'slug' => 'tamjeed',
        'meta' => $name === null ? null : ['database_name' => $name],
    ]);

    $method = new ReflectionMethod(TenantProvisioner::class, 'adoptedDatabaseName');
    $method->setAccessible(true);

    return $method->invoke(app(TenantProvisioner::class), $tenant);
}

it('adopts a control-panel database name', function () {
    expect(adoptedNameOf('u123456789_tamjeed'))->toBe('u123456789_tamjeed');
});

it('falls back to the derived name when none was supplied', function (?string $given) {
    expect(adoptedNameOf($given))->toBeNull();
})->with([
    'no meta at all' => [null],
    'empty string' => [''],
    'whitespace only' => ['   '],
]);

it('refuses a name that is not a plain identifier', function (string $name) {
    expect(fn () => adoptedNameOf($name))
        ->toThrow(InvalidArgumentException::class, 'not a valid MySQL identifier');
})->with([
    'backtick escape' => ['tnt`; DROP DATABASE `x'],
    'statement terminator' => ['tnt_a; SELECT 1'],
    'wildcard' => ['tnt_%'],
    'hyphen' => ['tnt-a'],
    'space' => ['tnt a'],
    'dot traversal' => ['mysql.user'],
    'over 64 chars' => [str_repeat('a', 65)],
]);

it('trims incidental whitespace rather than rejecting a pasted name', function () {
    expect(adoptedNameOf('  u123456789_tamjeed  '))->toBe('u123456789_tamjeed');
});

it('records the supplied name on the tenant at creation', function () {
    $result = app(TenantProvisioner::class)->createTenant([
        'display_name' => 'Tamjeed School',
        'slug' => 'tamjeed',
        'database_name' => 'u123456789_tamjeed',
    ]);

    expect($result['tenant']->meta['database_name'])->toBe('u123456789_tamjeed');
});

it('leaves meta null when no database name is supplied', function () {
    $result = app(TenantProvisioner::class)->createTenant([
        'display_name' => 'Noor School',
        'slug' => 'noor',
    ]);

    expect($result['tenant']->meta)->toBeNull();
});

it('keeps any other meta the caller passed', function () {
    $result = app(TenantProvisioner::class)->createTenant([
        'display_name' => 'Amal School',
        'slug' => 'amal',
        'meta' => ['onboarded_by' => 'ops'],
        'database_name' => 'u123456789_amal',
    ]);

    expect($result['tenant']->meta)
        ->toMatchArray(['onboarded_by' => 'ops', 'database_name' => 'u123456789_amal']);
});
