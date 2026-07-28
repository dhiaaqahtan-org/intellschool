<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Models\Landlord\PlatformUser;
use Modules\Saas\Models\Landlord\Tenant;
use Tests\TestCase;

uses(TestCase::class);

function saasRoute(string $name)
{
    $route = Route::getRoutes()->getByName($name);

    expect($route)->not->toBeNull("Missing route [{$name}].");

    return $route;
}

it('keeps platform JSON on session auth and mobile JSON on Sanctum', function () {
    $platform = saasRoute('saas.api.platform.tenants.index')->gatherMiddleware();
    $tenant = saasRoute('saas.api.tenant.show')->gatherMiddleware();
    $public = saasRoute('saas.api.discover')->gatherMiddleware();

    expect($platform)->toContain('web', 'auth:platform', 'saas.landlord-host')
        ->and($platform)->not->toContain('api')
        ->and($tenant)->toContain('api', 'auth:sanctum', 'saas.tenant-host', 'saas.tenant-active')
        ->and($tenant)->not->toContain('web')
        ->and($public)->toContain('api', 'saas.landlord-host', 'throttle:30,1');
});

it('requires signatures and tenant lifecycle middleware for every tenant download', function (string $routeName) {
    $middleware = saasRoute($routeName)->gatherMiddleware();

    expect($middleware)->toContain(
        'web',
        'saas.tenant-host',
        'saas.tenant-active',
        'signed',
    );
})->with([
    'asset' => 'saas.tenant.asset',
    'download' => 'saas.tenant.download',
]);

it('pins all control-plane identities to the configured landlord connection', function () {
    config()->set('saas.database.landlord_connection', 'control_plane_test');

    expect((new Tenant)->getConnectionName())->toBe('control_plane_test')
        ->and((new PlatformUser)->getConnectionName())->toBe('control_plane_test');
});
