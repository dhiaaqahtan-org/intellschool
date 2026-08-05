<?php

use Modules\Saas\Bootstrappers\SessionBootstrapper;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Enums\TenantStatus;

/**
 * A shared session directory lets a copied cookie authenticate as a different
 * person at a different school, because the payload carries a bare numeric user
 * id and numeric ids collide across tenant databases by design. These assert
 * the separation, and — just as important — that it is fully undone, since a
 * pooled worker serves the next tenant with the same process.
 */
uses(Tests\TestCase::class);

function sessionContextFor(string $uuid): TenantContext
{
    return new TenantContext(
        uuid: $uuid,
        slug: 'school-'.substr($uuid, 0, 4),
        status: TenantStatus::Active,
        databaseName: 'tnt_'.substr($uuid, 0, 4),
        connectionName: 'tenant',
        host: substr($uuid, 0, 4).'.intellschool.com',
    );
}

beforeEach(function () {
    config()->set('session.driver', 'file');
    config()->set('session.files', storage_path('framework/sessions'));
});

it('gives each tenant its own session directory', function () {
    $bootstrapper = app(SessionBootstrapper::class);
    $shared = config('session.files');

    $bootstrapper->bootstrap(sessionContextFor('aaaaaaaa-1111-4111-8111-111111111111'));
    $first = config('session.files');

    $bootstrapper->revert();

    $bootstrapper->bootstrap(sessionContextFor('bbbbbbbb-2222-4222-8222-222222222222'));
    $second = config('session.files');

    expect($first)->not->toBe($second)
        ->and($first)->not->toBe($shared)
        ->and($first)->toEndWith('aaaaaaaa-1111-4111-8111-111111111111')
        ->and($second)->toEndWith('bbbbbbbb-2222-4222-8222-222222222222');
});

it('restores the shared path so the next request does not inherit a tenant', function () {
    $bootstrapper = app(SessionBootstrapper::class);
    $original = config('session.files');

    $bootstrapper->bootstrap(sessionContextFor('cccccccc-3333-4333-8333-333333333333'));
    $bootstrapper->revert();

    expect(config('session.files'))->toBe($original);
});

it('does not nest one tenant path inside another across repeated bootstraps', function () {
    $bootstrapper = app(SessionBootstrapper::class);
    $original = config('session.files');

    // Without the ??= capture, a second bootstrap would append to the first
    // tenant's path and quietly build storage/.../<uuid-a>/<uuid-b>.
    $bootstrapper->bootstrap(sessionContextFor('dddddddd-4444-4444-8444-444444444444'));
    $bootstrapper->bootstrap(sessionContextFor('eeeeeeee-5555-4555-8555-555555555555'));

    expect(config('session.files'))
        ->toBe($original.DIRECTORY_SEPARATOR.'eeeeeeee-5555-4555-8555-555555555555');
});

it('leaves the database session path alone because it already follows the tenant connection', function () {
    config()->set('session.driver', 'database');
    $original = config('session.files');

    app(SessionBootstrapper::class)->bootstrap(sessionContextFor('ffffffff-6666-4666-8666-666666666666'));

    expect(config('session.files'))->toBe($original);
});

it('gives each tenant a distinct session cookie name, on every driver', function (string $driver) {
    config()->set('session.driver', $driver);
    config()->set('session.cookie', 'instikit_session');

    $bootstrapper = app(SessionBootstrapper::class);

    $bootstrapper->bootstrap(sessionContextFor('aaaaaaaa-1111-4111-8111-111111111111'));
    $first = config('session.cookie');
    $bootstrapper->revert();

    $bootstrapper->bootstrap(sessionContextFor('bbbbbbbb-2222-4222-8222-222222222222'));
    $second = config('session.cookie');

    expect($first)->not->toBe($second)
        ->and($first)->toStartWith('instikit_session_')
        ->and($second)->toStartWith('instikit_session_');
})->with(['file', 'database', 'redis']);

it('restores the shared cookie name so the control plane is unaffected', function () {
    config()->set('session.cookie', 'instikit_session');
    $bootstrapper = app(SessionBootstrapper::class);

    $bootstrapper->bootstrap(sessionContextFor('cccccccc-3333-4333-8333-333333333333'));
    $bootstrapper->revert();

    expect(config('session.cookie'))->toBe('instikit_session');
});

it('keeps the cookie name stable across requests for the same tenant', function () {
    // A name that changed per request would log everyone out on every click.
    config()->set('session.cookie', 'instikit_session');
    $bootstrapper = app(SessionBootstrapper::class);
    $uuid = 'dddddddd-4444-4444-8444-444444444444';

    $bootstrapper->bootstrap(sessionContextFor($uuid));
    $first = config('session.cookie');
    $bootstrapper->revert();

    $bootstrapper->bootstrap(sessionContextFor($uuid));

    expect(config('session.cookie'))->toBe($first);
});

it('derives the cookie from the uuid, not the slug or host', function () {
    // Slugs get renamed and schools move to custom domains. Either would
    // silently invalidate every session if the name were derived from them.
    config()->set('session.cookie', 'instikit_session');
    $bootstrapper = app(SessionBootstrapper::class);
    $uuid = 'eeeeeeee-5555-4555-8555-555555555555';

    $a = new TenantContext(
        uuid: $uuid, slug: 'tamjeed', status: TenantStatus::Active,
        databaseName: 'tnt_a', connectionName: 'tenant',
        host: 'tamjeed.intellschool.com',
    );
    $b = new TenantContext(
        uuid: $uuid, slug: 'tamjeed-school', status: TenantStatus::Active,
        databaseName: 'tnt_a', connectionName: 'tenant',
        host: 'tamjeed.com',
    );

    $bootstrapper->bootstrap($a);
    $viaSubdomain = config('session.cookie');
    $bootstrapper->revert();

    $bootstrapper->bootstrap($b);

    expect(config('session.cookie'))->toBe($viaSubdomain);
});

it('writes the session payload into the tenant directory, not the shared one', function () {
    $uuid = '99999999-7777-4777-8777-777777777777';
    $shared = config('session.files');

    // Resolve a driver FIRST. The file handler captures its directory at
    // construction, so a bootstrapper that only rewrote config would leave this
    // stale instance writing to the shared path — isolated on paper, shared on
    // disk. This is the assertion that tells those two apart.
    app('session')->driver();

    app(SessionBootstrapper::class)->bootstrap(sessionContextFor($uuid));

    $session = app('session')->driver();
    $session->setId('0123456789abcdef0123456789abcdef01234567');
    $session->put('login_web_test', 5);
    $session->save();

    expect(file_exists($shared.DIRECTORY_SEPARATOR.$session->getId()))->toBeFalse()
        ->and(file_exists($shared.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR.$session->getId()))->toBeTrue();
});
