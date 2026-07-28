<?php

namespace Modules\Saas\Domain\Provisioning;

use Modules\Saas\Enums\ProvisioningState;

/**
 * Ordered, persisted provisioning steps.
 */
enum ProvisioningStep: string
{
    case AllocateDatabase = 'allocate_database';
    case Migrate = 'migrate';
    case Seed = 'seed';
    case ConfigureDomain = 'configure_domain';
    case Verify = 'verify';

    public function state(): ProvisioningState
    {
        return match ($this) {
            self::AllocateDatabase => ProvisioningState::AllocatingDatabase,
            self::Migrate => ProvisioningState::Migrating,
            self::Seed => ProvisioningState::Seeding,
            self::ConfigureDomain => ProvisioningState::ConfiguringDomain,
            self::Verify => ProvisioningState::Verifying,
        };
    }

    public function progress(): int
    {
        return match ($this) {
            self::AllocateDatabase => 10,
            self::Migrate => 35,
            self::Seed => 60,
            self::ConfigureDomain => 80,
            self::Verify => 95,
        };
    }
}
