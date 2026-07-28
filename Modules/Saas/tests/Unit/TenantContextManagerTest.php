<?php

use Modules\Saas\Contracts\TenantBootstrapper;
use Modules\Saas\Contracts\TenantConnectionManager;
use Modules\Saas\Domain\Tenancy\TenantContext;
use Modules\Saas\Domain\Tenancy\TenantContextManager;
use Modules\Saas\Enums\TenantStatus;

function contextFor(string $tenant): TenantContext
{
    return new TenantContext(
        uuid: $tenant,
        slug: $tenant,
        status: TenantStatus::Active,
        databaseName: $tenant.'.sqlite',
        connectionName: 'tenant',
        host: $tenant.'.test',
    );
}

function contextManagerFixture(): array
{
    $events = new ArrayObject();

    $connections = new class($events) implements TenantConnectionManager
    {
        private bool $connected = false;

        public function __construct(private readonly ArrayObject $events)
        {
        }

        public function connect(TenantContext $context): void
        {
            $this->events->append('connect:'.$context->uuid);
            $this->connected = true;
        }

        public function release(): void
        {
            $this->events->append('release');
            $this->connected = false;
        }

        public function connectionName(): string
        {
            return 'tenant';
        }

        public function isConnected(): bool
        {
            return $this->connected;
        }
    };

    $bootstrapper = new class($events) implements TenantBootstrapper
    {
        private ?string $activeTenant = null;

        public function __construct(private readonly ArrayObject $events)
        {
        }

        public function bootstrap(TenantContext $context): void
        {
            expect($this->activeTenant)->toBeNull();
            $this->activeTenant = $context->uuid;
            $this->events->append('bootstrap:'.$context->uuid);
        }

        public function revert(): void
        {
            $this->events->append('revert:'.($this->activeTenant ?? 'none'));
            $this->activeTenant = null;
        }
    };

    return [
        new TenantContextManager($connections, [$bootstrapper]),
        $events,
    ];
}

it('fully unwinds one tenant before switching to another and restores the previous tenant', function () {
    [$manager, $events] = contextManagerFixture();

    $alpha = contextFor('alpha');
    $beta = contextFor('beta');

    $manager->set($alpha);

    $result = $manager->runFor($beta, function () use ($manager, $beta) {
        expect($manager->get())->toBe($beta);

        return 'ok';
    });

    expect($result)->toBe('ok')
        ->and($manager->get())->toBe($alpha)
        ->and($events->getArrayCopy())->toBe([
            'connect:alpha',
            'bootstrap:alpha',
            'revert:alpha',
            'release',
            'connect:beta',
            'bootstrap:beta',
            'revert:beta',
            'release',
            'connect:alpha',
            'bootstrap:alpha',
        ]);
});

it('restores the previous tenant even when nested tenant work throws', function () {
    [$manager] = contextManagerFixture();

    $alpha = contextFor('alpha');
    $manager->set($alpha);

    expect(fn () => $manager->runFor(
        contextFor('beta'),
        fn () => throw new RuntimeException('tenant task failed'),
    ))->toThrow(RuntimeException::class, 'tenant task failed')
        ->and($manager->get())->toBe($alpha);
});
