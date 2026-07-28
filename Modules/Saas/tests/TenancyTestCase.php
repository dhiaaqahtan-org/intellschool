<?php

namespace Modules\Saas\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\TenantCredentialResolver;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDatabase;
use Modules\Saas\Models\Landlord\TenantDomain;
use Tests\CreatesApplication;

/**
 * Base case for tenancy tests.
 *
 * Uses real, separate SQLite files rather than one in-memory database with a
 * filter. That matters: the whole claim under test is "a bug in the
 * application cannot cross the boundary because the boundary is physical". A
 * test that shares one database and trusts a where-clause would pass while
 * proving nothing.
 *
 * Each tenant therefore gets its own file on disk, and the credential resolver
 * is swapped for one that points the tenant connection at it.
 */
abstract class TenancyTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected string $tenantDbPath;

    protected string $landlordFile;

    protected ?string $cacheDir = null;

    /** @var array<string, string> tenant uuid => sqlite file path */
    protected array $tenantFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Separate concurrent Pest/PHP processes. A shared landlord.sqlite
        // lets one process truncate another process's open database.
        $this->tenantDbPath = storage_path('framework/testing/tenants/'.getmypid());
        File::ensureDirectoryExists($this->tenantDbPath);

        config()->set('saas.tenancy.enabled', true);

        // Keep the real connection NAMES and repoint them at SQLite, rather
        // than inventing test-only names. Some migrations reference the
        // 'landlord' connection literally, so a renamed connection would send
        // half the schema somewhere else and the suite would test a shape the
        // application never actually runs.
        config()->set('saas.database.landlord_connection', 'landlord');
        config()->set('saas.database.tenant_connection', 'tenant');
        config()->set('saas.database.tenant_template', 'tenant');

        // Landlord: its own file, entirely separate from any tenant file. If a
        // landlord query ever ran through a tenant connection the table simply
        // would not exist there — which is what makes that test meaningful.
        $this->landlordFile = $this->tenantDbPath.DIRECTORY_SEPARATOR.'landlord.sqlite';
        File::put($this->landlordFile, '');

        config()->set('database.connections.landlord', [
            'driver' => 'sqlite',
            'database' => $this->landlordFile,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        config()->set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => null, // overridden per tenant, must never fall back
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('landlord');
        DB::purge('tenant');

        $this->swapCredentialResolver();
        $this->migrateLandlord();
    }

    protected function tearDown(): void
    {
        app(CurrentTenant::class)->forget();

        foreach ($this->tenantFiles as $path) {
            File::delete($path);
        }

        File::delete($this->landlordFile);
        $this->tenantFiles = [];
        File::deleteDirectory($this->tenantDbPath);

        if ($this->cacheDir !== null) {
            File::deleteDirectory($this->cacheDir);
            $this->cacheDir = null;
        }

        parent::tearDown();
    }

    /**
     * Switch to a file-backed cache for tests that must prove key separation
     * rather than benefit from an in-memory store being emptied.
     */
    protected function useFileCache(): void
    {
        $path = storage_path('framework/testing/cache');

        File::deleteDirectory($path);
        File::ensureDirectoryExists($path);

        config()->set('cache.default', 'file');
        config()->set('cache.stores.file', ['driver' => 'file', 'path' => $path]);

        $this->cacheDir = $path;
    }

    /**
     * SQLite has no host/port/user; the "credential" is the file path, which
     * the connection manager writes into the `database` key.
     */
    protected function swapCredentialResolver(): void
    {
        $this->app->bind(
            TenantCredentialResolver::class,
            fn () => new class implements TenantCredentialResolver
            {
                public function resolveFor(TenantContext $context): array
                {
                    return ['host' => '', 'port' => 0, 'username' => '', 'password' => ''];
                }
            }
        );
    }

    protected function migrateLandlord(): void
    {
        $this->artisan('migrate', [
            '--database' => 'landlord',
            '--path' => 'Modules/Saas/database/migrations/landlord',
            '--realpath' => false,
        ])->run();
    }

    /**
     * Create a fully provisioned tenant backed by its own SQLite file, with a
     * couple of ERP-shaped tables so cross-tenant reads can be attempted.
     */
    protected function makeTenant(string $slug, string $host, TenantStatus $status = TenantStatus::Active): Tenant
    {
        $uuid = (string) Str::uuid();
        $file = $this->tenantDbPath.DIRECTORY_SEPARATOR.$slug.'.sqlite';

        File::put($file, '');
        $this->tenantFiles[$uuid] = $file;

        $tenant = Tenant::create([
            'uuid' => $uuid,
            'slug' => $slug,
            'display_name' => ucfirst($slug).' School',
            'status' => $status,
            'provisioning_state' => ProvisioningState::Ready,
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        TenantDatabase::create([
            'tenant_uuid' => $uuid,
            'cluster' => 'default',
            // The connection manager reads databaseName off the context, so
            // for SQLite the "name" is the file path.
            'database_name' => $file,
            'secret_ref' => 'vault://secret/tenant-'.$slug,
        ]);

        TenantDomain::create([
            'tenant_uuid' => $uuid,
            'hostname' => $host,
            'type' => TenantDomain::TYPE_SUBDOMAIN,
            'is_primary' => true,
        ]);

        $this->seedTenantSchema($tenant->fresh(['database', 'domains']), $host);

        return $tenant->fresh(['database', 'domains']);
    }

    /**
     * Minimal stand-in for the ERP schema. Deliberately uses overlapping
     * numeric IDs across tenants so that an ID guessed in tenant A exists in
     * tenant B — which is exactly the attack the isolation tests attempt.
     */
    protected function seedTenantSchema(Tenant $tenant, string $host): void
    {
        app(CurrentTenant::class)->runFor(
            $tenant->toContext($host),
            function () use ($tenant) {
                Schema::create('students', function ($table) {
                    $table->id();
                    $table->string('name');
                });

                Schema::create('tenant_installations', function ($table) {
                    $table->id();
                    $table->uuid('tenant_uuid');
                    $table->string('tenant_slug');
                });

                \DB::table('students')->insert([
                    ['id' => 1, 'name' => $tenant->slug.'-student-one'],
                    ['id' => 2, 'name' => $tenant->slug.'-student-two'],
                ]);

                \DB::table('tenant_installations')->insert([
                    'tenant_uuid' => $tenant->uuid,
                    'tenant_slug' => $tenant->slug,
                ]);
            }
        );
    }
}
