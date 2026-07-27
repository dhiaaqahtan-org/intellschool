<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Marketing website (plan §10).
 *
 * Renders the views directly rather than driving the HTTP stack, because the
 * `web` group pulls in the ERP's Init/UserConfig middleware, which expects a
 * migrated school database. What is being asserted here is the module's own
 * contract: meaningful server-rendered content, working host constraints, and
 * the claim gates actually gating.
 */
uses(TenancyTestCase::class);

beforeEach(function () {
    // This application's default locale is `ar`. Pin it so assertions about
    // English copy are testing the content, not the environment.
    app()->setLocale('en');
});

function homeData(): array
{
    $controller = new ReflectionClass(\Modules\Saas\Http\Controllers\Marketing\PageController::class);
    $instance = $controller->newInstance();

    $facts = $controller->getMethod('facts');
    $facts->setAccessible(true);
    $modules = $controller->getMethod('moduleCoverage');
    $modules->setAccessible(true);
    $roles = $controller->getMethod('roleCoverage');
    $roles->setAccessible(true);

    return [
        'facts' => $facts->invoke($instance),
        'modules' => $modules->invoke($instance),
        'roles' => $roles->invoke($instance),
    ];
}

it('renders the home page with meaningful server-rendered content', function () {
    $html = view('saas::marketing.home', homeData())->render();

    // Core proposition, module coverage and security story must all be in the
    // HTML itself — not assembled by JavaScript after load (plan §10.5).
    expect($html)->toContain('34')          // module count
        ->and($html)->toContain('1,701')     // endpoint count
        ->and($html)->toContain('644')       // permission count
        ->and($html)->toContain('own database');
});

it('renders the 3D product stage as real markup, not an image', function () {
    $html = view('saas::marketing.home', homeData())->render();

    // The "screenshots" are live HTML so they stay crisp, animate, and carry
    // no image weight.
    expect($html)->toContain('stage__scene')
        ->and($html)->toContain('panel--main')
        ->and($html)->toContain('app__nav');
});

it('marks every decorative element as hidden from assistive tech', function () {
    $html = view('saas::marketing.home', homeData())->render();

    expect(substr_count($html, 'aria-hidden="true"'))->toBeGreaterThan(5);
});

it('does not publish pricing while the claim gate is closed', function () {
    config()->set('saas.claims.publish_pricing', false);

    $html = view('saas::marketing.home', homeData())->render();

    // Publishing unapproved pricing is a contractual exposure (plan §9.3).
    expect($html)->toContain(__('saas::marketing.pricing.placeholder'))
        ->and($html)->not->toContain('$49')
        ->and($html)->not->toContain('/ month');
});

it('does not publish uptime or certification claims without evidence', function () {
    config()->set('saas.claims.publish_uptime', false);
    config()->set('saas.claims.publish_certifications', false);

    $html = view('saas::marketing.home', homeData())->render();

    expect($html)->toContain(__('saas::marketing.isolation.no_claims'))
        ->and($html)->not->toContain('99.9%')
        ->and($html)->not->toContain('ISO 27001')
        ->and($html)->not->toContain('SOC 2');
});

it('does not present the mobile app as released while it is unverified', function () {
    config()->set('saas.claims.publish_mobile_ga', false);

    $html = view('saas::marketing.home', homeData())->render();

    // Plan §13: no store links or offline guarantees until the client builds
    // and passes its two-tenant tests.
    expect($html)->toContain(__('saas::marketing.mobile.status'))
        ->and($html)->not->toContain('play.google.com')
        ->and($html)->not->toContain('apps.apple.com');
});

it('renders in Arabic with a right-to-left document direction', function () {
    app()->setLocale('ar');

    $html = view('saas::marketing.layouts.app')->render();

    expect($html)->toContain('dir="rtl"')
        ->and($html)->toContain('lang="ar"');
});

it('has an Arabic translation for every marketing string', function () {
    $en = require module_path('Saas', 'resources/lang/en/marketing.php');
    $ar = require module_path('Saas', 'resources/lang/ar/marketing.php');

    $flatten = function (array $a, string $prefix = '') use (&$flatten) {
        $out = [];
        foreach ($a as $k => $v) {
            $key = $prefix === '' ? (string) $k : "{$prefix}.{$k}";
            $out = array_merge($out, is_array($v) ? $flatten($v, $key) : [$key]);
        }

        return $out;
    };

    // A half-translated marketing page is worse than an untranslated one:
    // it looks finished and silently falls back to English mid-sentence.
    expect(array_diff($flatten($en), $flatten($ar)))->toBe([]);
});

it('carries no-JS fallbacks so content survives a failed bundle', function () {
    $html = view('saas::marketing.layouts.app')->render();

    expect($html)->toContain('class="no-js"');
});

it('registers marketing routes only on the marketing host', function () {
    config()->set('saas.hosts.marketing', 'www.product.test');

    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_starts_with((string) $r->getName(), 'saas.marketing.'));

    expect($routes)->not->toBeEmpty();

    // If marketing `/` were unconstrained it would shadow every tenant's own
    // school website, which routes/site.php serves at the same path.
    $home = $routes->first(fn ($r) => $r->getName() === 'saas.marketing.home');
    expect($home)->not->toBeNull()
        ->and($home->gatherMiddleware())->toContain('saas.landlord-host');
});

it('keeps the demo endpoint rate limited', function () {
    $post = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->getName() === 'saas.marketing.demo.store');

    expect($post)->not->toBeNull()
        ->and($post->gatherMiddleware())->toContain('throttle:saas-leads');
});

it('exposes the built asset manifest so production does not need a dev server', function () {
    // Must sit at the build-dir root, which is where Laravel's @vite() looks.
    $manifest = base_path('public/build-saas/manifest.json');

    expect(file_exists($manifest))->toBeTrue();

    $entries = json_decode(file_get_contents($manifest), true);

    expect($entries)->toHaveKey('resources/assets/js/marketing/app.js')
        ->and($entries)->toHaveKey('resources/assets/css/marketing.css');
});
