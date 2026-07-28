<?php

use App\Http\Middleware\Init;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\File;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\TenantStorage;
use Modules\Saas\Contracts\TenantUrlGenerator;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

beforeEach(function () {
    $storageRoot = $this->tenantDbPath.DIRECTORY_SEPARATOR.'tenant-storage';
    File::ensureDirectoryExists($storageRoot);

    config()->set('filesystems.default', 'local');
    config()->set('filesystems.disks.local', [
        'driver' => 'local',
        'root' => $storageRoot,
        'throw' => false,
    ]);
    config()->set('saas.storage.tenant_disks', ['local']);
    config()->set('saas.hosts.marketing', 'marketing.test');
    config()->set('saas.hosts.platform', 'platform.test');
    config()->set('saas.hosts.tenant_suffix', '.test');

    app(FilesystemManager::class)->forgetDisk('local');
    $this->withoutMiddleware(Init::class);
});

it('generates a tenant-host signed URL and streams only the tenant-rooted file', function () {
    $tenant = $this->makeTenant('alpha-files', 'alpha-files.test');

    $url = app(CurrentTenant::class)->runFor(
        $tenant->toContext('alpha-files.test'),
        function () {
            $storage = app(TenantStorage::class);
            $storage->disk()->put('reports/summary.txt', 'alpha private report');

            return $storage->temporaryUrl('reports/summary.txt', 10);
        },
    );

    expect(parse_url($url, PHP_URL_HOST))->toBe('alpha-files.test')
        ->and($url)->toContain('/saas/asset?');

    $this->get($url)
        ->assertOk()
        ->assertHeader('content-disposition')
        ->assertStreamedContent('alpha private report');
});

it('rejects a signed link replayed on another tenant host', function () {
    $alpha = $this->makeTenant('alpha-replay', 'alpha-replay.test');
    $this->makeTenant('beta-replay', 'beta-replay.test');

    $url = app(CurrentTenant::class)->runFor(
        $alpha->toContext('alpha-replay.test'),
        fn () => app(TenantUrlGenerator::class)->signedAsset('exports/data.csv'),
    );

    $replayed = preg_replace(
        '#^https?://alpha-replay\.test#',
        'http://beta-replay.test',
        $url,
    );

    $this->get($replayed)->assertForbidden();
});

it('turns signed traversal attempts into a bounded client error', function () {
    $tenant = $this->makeTenant('alpha-traversal', 'alpha-traversal.test');

    $url = app(CurrentTenant::class)->runFor(
        $tenant->toContext('alpha-traversal.test'),
        fn () => app(TenantUrlGenerator::class)->signedAsset('../other-tenant/secret.txt'),
    );

    $this->get($url)->assertBadRequest();
});
