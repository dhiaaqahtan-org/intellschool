<?php

namespace Modules\Saas\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Saas\Enums\ProvisioningState;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Models\Landlord\Tenant;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'uuid' => (string) Str::uuid(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'display_name' => $name,
            'status' => TenantStatus::Pending->value,
            'provisioning_state' => ProvisioningState::Queued->value,
            'tier' => 'standard',
            'region' => 'me',
            'locale' => 'en',
            'timezone' => 'UTC',
            'meta' => [],
        ];
    }

    public function ready(): static
    {
        return $this->state(fn () => [
            'status' => TenantStatus::Active->value,
            'provisioning_state' => ProvisioningState::Ready->value,
        ]);
    }
}
