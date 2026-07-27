<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * The two-tenant adversarial suite (plan §17.1).
 *
 * Tenant A and Tenant B are created with DELIBERATELY OVERLAPPING numeric IDs
 * and distinct UUIDs. Every test below tries to reach B's data while executing
 * as A. Each one must fail to.
 *
 * These are launch-blocking assertions, not nice-to-haves: plan §19 lists
 * "missed tenant context causes cross-school access" as a Blocker.
 */
uses(TenancyTestCase::class);

beforeEach(function () {
    $this->tenantA = $this->makeTenant('alpha', 'alpha.test');
    $this->tenantB = $this->makeTenant('beta', 'beta.test');
    $this->tenant = app(CurrentTenant::class);
});

it('reads only its own rows when IDs collide across tenants', function () {
    // Both tenants have a student with id=1. A guessed ID must not cross.
    $inA = $this->tenant->runFor(
        $this->tenantA->toContext('alpha.test'),
        fn () => DB::table('students')->find(1)->name
    );

    $inB = $this->tenant->runFor(
        $this->tenantB->toContext('beta.test'),
        fn () => DB::table('students')->find(1)->name
    );

    expect($inA)->toBe('alpha-student-one')
        ->and($inB)->toBe('beta-student-one');
});

it('cannot write into the other tenant while inside one', function () {
    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        DB::table('students')->where('id', 1)->update(['name' => 'overwritten-by-alpha']);
    });

    $inB = $this->tenant->runFor(
        $this->tenantB->toContext('beta.test'),
        fn () => DB::table('students')->find(1)->name
    );

    expect($inB)->toBe('beta-student-one');
});

it('points the open connection at the tenant it claims to be in', function () {
    // Guards against the context saying A while the PDO handle is still B's —
    // the failure mode purge()/reconnect exists to prevent.
    foreach ([[$this->tenantA, 'alpha.test', 'alpha'], [$this->tenantB, 'beta.test', 'beta']] as [$tenant, $host, $slug]) {
        $installed = $this->tenant->runFor(
            $tenant->toContext($host),
            fn () => DB::table('tenant_installations')->value('tenant_slug')
        );

        expect($installed)->toBe($slug);
    }
});

it('does not reuse the previous tenant connection after switching', function () {
    // Switch A -> B -> A on one process, as a queue worker would.
    $seen = [];

    foreach (['alpha', 'beta', 'alpha'] as $slug) {
        $tenant = $slug === 'alpha' ? $this->tenantA : $this->tenantB;

        $seen[] = $this->tenant->runFor(
            $tenant->toContext($slug.'.test'),
            fn () => DB::table('tenant_installations')->value('tenant_slug')
        );
    }

    expect($seen)->toBe(['alpha', 'beta', 'alpha']);
});

it('clears tenant context after the callback, including when it throws', function () {
    expect($this->tenant->has())->toBeFalse();

    try {
        $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
            throw new RuntimeException('boom');
        });
    } catch (RuntimeException) {
        // expected
    }

    // A worker that survives an exception must not stay inside the tenant.
    expect($this->tenant->has())->toBeFalse();
});

it('restores the outer tenant after a nested switch', function () {
    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), function () {
        $this->tenant->runFor($this->tenantB->toContext('beta.test'), fn () => null);

        expect($this->tenant->uuid())->toBe($this->tenantA->uuid)
            ->and(DB::table('tenant_installations')->value('tenant_slug'))->toBe('alpha');
    });
});

it('separates cache keys so one tenant cannot read the other', function () {
    // Deliberately NOT the array driver. Swapping the cache prefix forces the
    // resolved store to be rebuilt, which empties an in-memory array store —
    // so with `array` this test would pass without proving anything. A file
    // store survives the rebuild, so the separation has to come from the
    // prefix, which is the thing under test.
    $this->useFileCache();

    $this->tenant->runFor(
        $this->tenantA->toContext('alpha.test'),
        fn () => Cache::put('shared_key', 'alpha-value', 60)
    );

    $fromB = $this->tenant->runFor(
        $this->tenantB->toContext('beta.test'),
        fn () => Cache::get('shared_key')
    );

    // This is the `query_config_list_all` class of bug the plan calls out:
    // a global key name shared by every school on the platform.
    expect($fromB)->toBeNull();

    $backInA = $this->tenant->runFor(
        $this->tenantA->toContext('alpha.test'),
        fn () => Cache::get('shared_key')
    );

    expect($backInA)->toBe('alpha-value');
});

it('restores the original cache prefix once no tenant is active', function () {
    $before = config('cache.prefix');

    $this->tenant->runFor($this->tenantA->toContext('alpha.test'), fn () => null);

    expect(config('cache.prefix'))->toBe($before);
});

it('separates storage so a guessed filename cannot cross tenants', function () {
    config()->set('saas.storage.tenant_disks', ['local']);

    $this->tenant->runFor(
        $this->tenantA->toContext('alpha.test'),
        fn () => Storage::disk('local')->put('report.txt', 'alpha-confidential')
    );

    $fromB = $this->tenant->runFor(
        $this->tenantB->toContext('beta.test'),
        fn () => Storage::disk('local')->exists('report.txt')
    );

    expect($fromB)->toBeFalse();
});

it('namespaces the cache and storage prefixes by uuid, not by slug or id', function () {
    // Slugs are renameable and numeric IDs collide; only the UUID is a safe
    // namespace. Asserting the shape stops someone "simplifying" it later.
    $context = $this->tenantA->toContext('alpha.test');

    expect($context->cachePrefix())->toContain($this->tenantA->uuid)
        ->and($context->storagePrefix())->toBe('tenants/'.$this->tenantA->uuid)
        ->and($context->cachePrefix())->not->toContain('alpha');
});

it('keeps landlord models on the landlord connection during a tenant request', function () {
    // The tenant databases have no saas_tenants table. If landlord models
    // followed the swapped default connection this would throw.
    $count = $this->tenant->runFor(
        $this->tenantA->toContext('alpha.test'),
        fn () => \Modules\Saas\Models\Landlord\Tenant::query()->count()
    );

    expect($count)->toBe(2);
});

it('exposes no secret reference in the loggable context', function () {
    $context = $this->tenantA->toContext('alpha.test');
    $logged = $context->toLogContext();

    expect($logged)->not->toHaveKey('secretRef')
        ->and($logged)->not->toHaveKey('databaseName')
        ->and($logged)->not->toHaveKey('cluster')
        // Use a value that cannot appear incidentally in the host or slug.
        ->and(json_encode($logged))->not->toContain('vault://secret/tenant-alpha');
});

it('sends only the uuid over the queue, never connection details', function () {
    // A payload carrying a database name would let a tampered job point
    // itself at an arbitrary database.
    $payload = $this->tenantA->toContext('alpha.test')->toQueuePayload();

    expect($payload)->toBe(['tenant_uuid' => $this->tenantA->uuid]);
});

it('refuses to serve a tenant that is not ready', function () {
    $pending = $this->makeTenant('gamma', 'gamma.test', TenantStatus::Pending);

    expect($pending->isServable())->toBeFalse();
});

it('blocks writes for a suspended tenant but still allows reads', function () {
    $suspended = $this->makeTenant('delta', 'delta.test', TenantStatus::Suspended);
    $context = $suspended->toContext('delta.test');

    // Plan §12: a payment failure must never delete or corrupt school data.
    expect($context->canWrite())->toBeFalse()
        ->and($suspended->status->canServeRequests())->toBeTrue();
});
