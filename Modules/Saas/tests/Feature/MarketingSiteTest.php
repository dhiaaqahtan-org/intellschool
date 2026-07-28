<?php

use App\Http\Middleware\Init;
use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\Marketing\PageController;
use Modules\Saas\Domain\Website\ClaimGate;
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
    config()->set('app.url', 'http://localhost');
    app('url')->forceRootUrl('http://localhost');
    config()->set('saas.hosts.marketing', 'localhost');
    $this->withServerVariables([
        'HTTP_HOST' => 'localhost',
        'SERVER_NAME' => 'localhost',
    ]);
});

function homeData(): array
{
    $controller = new ReflectionClass(PageController::class);
    $instance = $controller->newInstance();

    $facts = $controller->getMethod('facts');
    $facts->setAccessible(true);
    $modules = $controller->getMethod('moduleCoverage');
    $modules->setAccessible(true);
    $claimGate = app(ClaimGate::class);

    $roles = $controller->getMethod('roleCoverage');
    $roles->setAccessible(true);

    return [
        'claims' => [
            'pricing' => $claimGate->pricing(),
            'mobile_ga' => $claimGate->mobileGeneralAvailability(),
            'uptime' => $claimGate->uptime(),
            'certifications' => $claimGate->certifications(),
        ],
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

it('renders the localized demo form through the complete HTTP stack', function () {
    $response = $this->get('/ar/demo');

    $response
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl"', false)
        ->assertSee('name="email"', false)
        ->assertSee(__('saas::marketing.form.submit', locale: 'ar'));

    preg_match('/<script type="application\/json" data-props>(.*?)<\/script>/s', $response->getContent(), $matches);
    $props = json_decode($matches[1] ?? '', true, flags: JSON_THROW_ON_ERROR);

    expect($props['action'])->toEndWith('/ar/demo')
        ->and($props['t']['name'])->toBe(__('saas::marketing.form.name', locale: 'ar'))
        ->and($props['sizes'][0]['label'])->toBe(__('saas::marketing.form.size_options.up_to_300', locale: 'ar'));
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
    // The provider already baked the configured marketing domain into these routes.

    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_starts_with((string) $r->baseName(), 'saas.marketing.'));

    expect($routes)->not->toBeEmpty();

    // If marketing `/` were unconstrained it would shadow every tenant's own
    // school website, which routes/site.php serves at the same path.
    $home = $routes->first(fn ($r) => $r->baseName() === 'saas.marketing.home');
    expect($home)->not->toBeNull()
        ->and($home->gatherMiddleware())->toContain('saas.landlord-host');
});

it('keeps the demo endpoint rate limited', function () {
    $post = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->baseName() === 'saas.marketing.demo.store');

    expect($post)->not->toBeNull()
        ->and($post->gatherMiddleware())->toContain('throttle:saas-leads');
});

it('detects English from the customer browser on the first visit', function () {
    $this->withoutMiddleware(Init::class);

    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
        ->get('/');

    $response->assertRedirect('http://localhost/en')
        ->assertCookie('locale');
});

it('detects Arabic regional preferences from the customer browser', function () {
    $this->withoutMiddleware(Init::class);

    $response = $this->withHeader('Accept-Language', 'ar-SA,ar;q=0.9,en;q=0.5')
        ->get('/');

    $response->assertRedirect('http://localhost/ar')
        ->assertCookie('locale');
});

it('uses English as the fallback for unsupported browser languages', function () {
    $this->withoutMiddleware(Init::class);

    $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
        ->get('/')
        ->assertRedirect('http://localhost/en');
});

it('lets an explicit localized URL override the browser preference', function () {
    $this->withoutMiddleware(Init::class);

    $this->withHeader('Accept-Language', 'ar-SA,ar;q=0.9')
        ->get('/en')
        ->assertOk()
        ->assertSee('<html lang="en" dir="ltr"', false)
        ->assertSee(__('saas::marketing.hero.title_a', locale: 'en'));

    $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
        ->get('/ar')
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl"', false)
        ->assertSee(__('saas::marketing.hero.title_a', locale: 'ar'));
});

it('persists a manual language choice in the session', function () {
    $this->withoutMiddleware(Init::class);

    $this->withSession(['locale' => 'ar'])
        ->get('/demo')
        ->assertRedirect('http://localhost/ar/demo');
});

it('publishes canonical language URLs and a working switcher without query hacks', function () {
    $this->withoutMiddleware(Init::class);

    $response = $this->get('/en');

    $response->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/en">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="http://localhost/en">', false)
        ->assertSee('<link rel="alternate" hreflang="ar" href="http://localhost/ar">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="http://localhost">', false)
        ->assertSee('href="http://localhost/ar"', false)
        ->assertDontSee('?lang=', false);
});

it('keeps the legacy school and admin locale routes outside package localization', function () {
    expect(Route::has('site.locale'))->toBeTrue()
        ->and(Route::has('admin.locale'))->toBeTrue()
        ->and(Route::hasLocalized('site.locale'))->toBeFalse()
        ->and(Route::hasLocalized('admin.locale'))->toBeFalse();
});

it('exposes the built asset manifest so production does not need a dev server', function () {
    // Must sit at the build-dir root, which is where Laravel's @vite() looks.
    $manifest = base_path('public/build-saas/manifest.json');

    expect(file_exists($manifest))->toBeTrue();

    $entries = json_decode(file_get_contents($manifest), true);

    expect($entries)->toHaveKey('resources/assets/js/marketing/app.js')
        ->and($entries)->toHaveKey('resources/assets/css/marketing.css');
});
