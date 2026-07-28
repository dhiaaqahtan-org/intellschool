<?php

namespace Modules\Saas\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Saas\Models\Landlord\PlatformUser;


class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SAAS_PLATFORM_ADMIN_EMAIL');
        $name = env('SAAS_PLATFORM_ADMIN_NAME', 'Platform Administrator');
        $password = env('SAAS_PLATFORM_ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            if (app()->environment('production')) {
                throw new \RuntimeException(
                    'SAAS_PLATFORM_ADMIN_EMAIL and SAAS_PLATFORM_ADMIN_PASSWORD are required.'
                );
            }

            $this->command?->warn('Platform admin not seeded: set SAAS_PLATFORM_ADMIN_EMAIL and SAAS_PLATFORM_ADMIN_PASSWORD.');
            return;
        }

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
    }
}
