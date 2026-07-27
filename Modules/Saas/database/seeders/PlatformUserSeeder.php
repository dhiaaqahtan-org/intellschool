<?php

namespace Modules\Saas\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Saas\Models\Landlord\PlatformUser;

/**
 * Seed the initial platform super-admin.
 *
 * Run on the LANDLORD connection:
 *   php artisan db:seed --class="Modules\Saas\Database\Seeders\PlatformUserSeeder"
 *
 * Credentials come from env vars or fall back to development defaults.
 * Change immediately after first login in production.
 */
class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SAAS_PLATFORM_ADMIN_EMAIL', 'platform@admin.local');
        $name = env('SAAS_PLATFORM_ADMIN_NAME', 'Platform Administrator');
        $password = env('SAAS_PLATFORM_ADMIN_PASSWORD', 'platform-admin-2026');

        PlatformUser::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                // The column is `status`, not `is_active`. PlatformUser's
                // scopeActive() and the login check both compare against
                // 'active'; seeding anything else creates an account that
                // exists but can never sign in.
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info("Platform admin seeded: {$email}");
        $this->command?->warn('Change the default password in production immediately.');
    }
}
