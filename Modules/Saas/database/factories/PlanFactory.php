<?php

namespace Modules\Saas\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Saas\Models\Landlord\Plan;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $code = fake()->unique()->lexify('plan_????');

        return [
            'uuid' => (string) Str::uuid(),
            'plan_code' => $code,
            'version' => 1,
            'display_name' => Str::headline($code),
            'description' => fake()->sentence(),
            'billing_interval' => 'monthly',
            'currency' => 'USD',
            'price_cents' => fake()->numberBetween(1000, 50000),
            'trial_days' => 14,
            'active_from' => now()->subDay(),
            'active_until' => null,
            'is_public' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['is_public' => true]);
    }
}
