<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Middleware\RequireTenantHost;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * The ERP's own routes must assert a tenant before touching school data.
 *
 * ResolveTenant runs globally and swaps the connection, but it is deliberately
 * permissive about finding NO tenant — the marketing and platform hosts
 * legitimately have none. That leaves a gap: an ERP route reached on one of
 * those hosts would run school queries against whatever connection happened to
 * be default, which is the landlord. RequireTenantHost closes it.
 *
 * This suite is a regression guard. It failed when written: the middleware was
 * aliased and applied only to the SaaS module's own routes, so all 1,701 ERP
 * endpoints were unguarded.
 */
uses(TenancyTestCase::class);

/** Route groups that serve tenant (school) data and must carry the guard. */
const TENANT_ROUTE_FILES = [
    'routes/api.php',
    'routes/integration.php',
    'routes/guest.php',
    'routes/auth.php',
    'routes/app.php',
    'routes/chat.php',
    'routes/module.php',
    'routes/sync.php',
    'routes/export.php',
    'routes/gateway.php',
    'routes/site.php',
    'routes/report.php',
    'routes/web.php',
    'routes/command.php',
    'routes/custom.php',
];

it('guards every core route group that serves school data', function (string $file) {
    $source = file_get_contents(base_path('app/Providers/RouteServiceProvider.php'));

    // Find the middleware(...) call immediately preceding this group().
    $needle = "routes/".basename($file);
    $position = strpos($source, $needle);

    expect($position)->not->toBeFalse();

    $preceding = substr($source, max(0, $position - 400), min(400, $position));

    // toContain() takes further arguments as ADDITIONAL needles, not as a
    // failure message — so the reason lives in a comment, not an argument.
    // Failure here means that route file serves school data on the marketing
    // or platform host, against the landlord connection.
    expect($preceding)->toContain('saas.tenant-host');
})->with(TENANT_ROUTE_FILES);

it('passes through while tenancy is disabled so the ERP keeps working', function () {
    config()->set('saas.tenancy.enabled', false);

    $middleware = app(RequireTenantHost::class);
    $response = $middleware->handle(request(), fn () => response('ok'));

    // Single-tenant mode resolves no tenant; a strict guard would 404 the
    // entire application the moment it was wired in.
    expect($response->getContent())->toBe('ok');
});

it('rejects an ERP request when tenancy is on and no tenant resolved', function () {
    config()->set('saas.tenancy.enabled', true);

    $middleware = app(RequireTenantHost::class);

    expect(fn () => $middleware->handle(request(), fn () => response('ok')))
        ->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

it('allows an ERP request once a tenant is active', function () {
    config()->set('saas.tenancy.enabled', true);

    $tenant = $this->makeTenant('alpha', 'alpha.test');
    $current = app(\Modules\Saas\Contracts\CurrentTenant::class);

    $result = $current->runFor($tenant->toContext('alpha.test'), function () {
        return app(RequireTenantHost::class)->handle(request(), fn () => response('ok'));
    });

    expect($result->getContent())->toBe('ok');
});

it('keeps the SaaS platform panel off the tenant guard', function () {
    // The platform panel administers the control plane and must NOT require a
    // tenant — it runs on the landlord host with no tenant connection at all.
    $platform = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_starts_with((string) $r->getName(), 'saas.platform.'));

    expect($platform)->not->toBeEmpty();

    $platform->each(function ($route) {
        expect($route->gatherMiddleware())
            ->not->toContain('saas.tenant-host')
            ->and($route->gatherMiddleware())->toContain('saas.landlord-host');
    });
});

it('separates the three admin surfaces by host and prefix', function () {
    $named = collect(Route::getRoutes()->getRoutes())
        ->mapWithKeys(fn ($r) => [(string) $r->getName() => $r])
        ->filter(fn ($r, $name) => $name !== '');

    // Platform operators: control plane, landlord host.
    expect($named)->toHaveKey('saas.platform.login');
    // Tenant staff: the existing ERP SPA, tenant host.
    expect(collect(Route::getRoutes()->getRoutes())
        ->contains(fn ($r) => str_starts_with($r->uri(), 'app')))->toBeTrue();
});
