<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDatabase;
use Modules\Saas\Services\TenantMigrationRunner;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

it('runs tenant support migrations through the standard isolated connection lifecycle', function () {
    $uuid = (string) Str::uuid();
    $file = $this->tenantDbPath.DIRECTORY_SEPARATOR.'migration-runner.sqlite';
    File::put($file, '');
    $this->tenantFiles[$uuid] = $file;

    $tenant = Tenant::create([
        'uuid' => $uuid,
        'slug' => 'migration-runner',
        'display_name' => 'Migration Runner School',
        'status' => TenantStatus::Pending,
        'provisioning_state' => ProvisioningState::Migrating,
        'locale' => 'en',
        'timezone' => 'UTC',
    ]);

    TenantDatabase::create([
        'tenant_uuid' => $uuid,
        'cluster' => 'default',
        'database_name' => $file,
        'secret_ref' => 'vault://tenant/migration-runner',
    ]);

    $result = app(TenantMigrationRunner::class)->migrateTenantSupportTables($tenant->fresh('database'));

    expect($result['success'])->toBeTrue()
        ->and(app(CurrentTenant::class)->has())->toBeFalse();

    app(CurrentTenant::class)->runFor($tenant->fresh('database')->toContext('migration-runner.test'), function () {
        expect(Schema::hasTable('tenant_installations'))->toBeTrue();
    });
});
