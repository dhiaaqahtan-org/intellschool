<?php

use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Models\Landlord\ProvisioningRun;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantDomain;
use Modules\Saas\Services\TenantProvisioner;
use Modules\Saas\Tests\TenancyTestCase;

/**
 * Provisioning (plan §11, §17.2).
 *
 * The property under test is idempotency: replaying any step must not create a
 * second tenant, database, domain or run. A signup retried by an impatient
 * customer, a duplicated queue message and a resumed failed run all take the
 * same path.
 */
uses(TenancyTestCase::class);

beforeEach(function () {
    $this->provisioner = app(TenantProvisioner::class);
    config()->set('saas.hosts.tenant_suffix', '.product.example');
});

it('creates a pending tenant with a queued run', function () {
    ['tenant' => $tenant, 'run' => $run] = $this->provisioner->createTenant([
        'display_name' => 'Al Noor School',
    ]);

    expect($tenant->status)->toBe(TenantStatus::Pending)
        ->and($tenant->provisioning_state)->toBe(ProvisioningState::Queued)
        ->and($tenant->slug)->toBe('al-noor-school')
        ->and($run->tenant_uuid)->toBe($tenant->uuid);
});

it('gives each run a unique idempotency key', function () {
    ['run' => $run] = $this->provisioner->createTenant(['display_name' => 'One']);

    // The unique constraint is what makes a replayed provisioning request
    // reuse the run instead of allocating a second database.
    expect(fn () => ProvisioningRun::create([
        'tenant_uuid' => $run->tenant_uuid,
        'idempotency_key' => $run->idempotency_key,
        'state' => ProvisioningState::Queued->value,
    ]))->toThrow(Illuminate\Database\QueryException::class);
});

it('refuses to reuse a slug that is already taken', function () {
    $this->provisioner->createTenant(['display_name' => 'Al Noor']);

    expect(fn () => $this->provisioner->createTenant(['display_name' => 'Al Noor']))
        ->toThrow(InvalidArgumentException::class);
});

it('refuses a slug belonging to a soft-deleted tenant', function () {
    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => 'Gone School']);
    $tenant->delete();

    // Reissuing a deleted tenant's slug would let a new customer inherit the
    // old one's subdomain, and any link or bookmark pointing at it.
    expect(fn () => $this->provisioner->createTenant(['display_name' => 'Gone School']))
        ->toThrow(InvalidArgumentException::class);
});

it('never issues a reserved slug verbatim', function (string $reserved) {
    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => $reserved]);

    // `app`, `www`, `admin` etc. would shadow a platform host.
    expect($tenant->slug)->not->toBe($reserved)
        ->and($tenant->slug)->toStartWith($reserved.'-');
})->with(['www', 'app', 'admin', 'api', 'billing']);

it('generates a fallback slug when the name yields nothing usable', function () {
    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => '!!!']);

    expect(strlen($tenant->slug))->toBeGreaterThanOrEqual(2)
        ->and($tenant->slug)->toStartWith('school-');
});

it('builds the subdomain with a dot separator', function () {
    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => 'Alpha']);

    invokePrivate($this->provisioner, 'configureDomain', [$tenant]);

    // Regression: ltrim() strips the leading dot from ".product.example", so
    // naive concatenation produced "alphaproduct.example".
    expect($tenant->fresh()->primaryDomain()->hostname)->toBe('alpha.product.example');
});

it('falls back to .localhost when no suffix is configured', function () {
    config()->set('saas.hosts.tenant_suffix', null);

    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => 'Alpha']);
    invokePrivate($this->provisioner, 'configureDomain', [$tenant]);

    expect($tenant->fresh()->primaryDomain()->hostname)->toBe('alpha.localhost');
});

it('does not create a second domain when the step is replayed', function () {
    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => 'Alpha']);

    invokePrivate($this->provisioner, 'configureDomain', [$tenant]);
    invokePrivate($this->provisioner, 'configureDomain', [$tenant->fresh()]);

    expect(TenantDomain::where('tenant_uuid', $tenant->uuid)->count())->toBe(1);
});

it('records each step with an attempt count', function () {
    ['run' => $run] = $this->provisioner->createTenant(['display_name' => 'Alpha']);

    $run->recordStep('allocate_database', true);
    $run->recordStep('migrate', false, 'connection refused');
    $run->recordStep('migrate', true);

    $steps = $run->fresh()->steps;

    expect($steps['allocate_database']['ok'])->toBeTrue()
        ->and($steps['migrate']['ok'])->toBeTrue()
        // History accumulates rather than being overwritten — that history is
        // what makes a resume safe rather than a blind replay.
        ->and($steps['migrate']['attempts'])->toBe(2);
});

it('reports a completed step so the pipeline can skip it', function () {
    ['run' => $run] = $this->provisioner->createTenant(['display_name' => 'Alpha']);

    expect($run->hasCompleted('migrate'))->toBeFalse();

    $run->recordStep('migrate', false, 'boom');
    expect($run->fresh()->hasCompleted('migrate'))->toBeFalse();

    $run->recordStep('migrate', true);
    expect($run->fresh()->hasCompleted('migrate'))->toBeTrue();
});

it('redacts credentials from the operator-facing error summary', function () {
    $error = invokePrivate($this->provisioner, 'safeError', [
        new RuntimeException('SQLSTATE[HY000] password=hunter2 could not connect to mysql://root:s3cr3t@10.0.0.4/db'),
    ]);

    expect($error)->not->toContain('hunter2')
        ->and($error)->not->toContain('s3cr3t')
        ->and($error)->toContain('[REDACTED]');
});

it('derives the database name from the uuid and never from input', function () {
    $name = \Modules\Saas\Models\Landlord\TenantDatabase::nameFor('11111111-2222-3333-4444-555555555555');

    expect($name)->toStartWith(config('saas.database.tenant_prefix', 'tnt_'))
        ->and(strlen($name))->toBeLessThanOrEqual(64)
        // Deterministic, so a replayed allocation targets the same database.
        ->and($name)->toBe(\Modules\Saas\Models\Landlord\TenantDatabase::nameFor('11111111-2222-3333-4444-555555555555'))
        ->and($name)->not->toBe(\Modules\Saas\Models\Landlord\TenantDatabase::nameFor('99999999-2222-3333-4444-555555555555'));
});

it('writes an audit event when a tenant is created', function () {
    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => 'Alpha']);

    $event = \Modules\Saas\Models\Landlord\AuditEvent::where('tenant_uuid', $tenant->uuid)
        ->where('action', 'tenant.created')
        ->first();

    expect($event)->not->toBeNull();
});

it('refuses to modify or delete an audit event', function () {
    ['tenant' => $tenant] = $this->provisioner->createTenant(['display_name' => 'Alpha']);
    $event = \Modules\Saas\Models\Landlord\AuditEvent::where('tenant_uuid', $tenant->uuid)->firstOrFail();

    // If application code can rewrite the audit log, it is not evidence.
    expect(fn () => $event->update(['action' => 'tampered']))->toThrow(LogicException::class);
    expect(fn () => $event->delete())->toThrow(LogicException::class);
});

/** Reach a private step directly, so idempotency can be tested without a live MySQL server. */
function invokePrivate(object $object, string $method, array $args = []): mixed
{
    $ref = new ReflectionMethod($object, $method);
    $ref->setAccessible(true);

    return $ref->invokeArgs($object, $args);
}
