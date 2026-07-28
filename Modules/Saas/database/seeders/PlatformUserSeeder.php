<?php

namespace Modules\Saas\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Saas\Models\Landlord\PlatformUser;


class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'dhiaa@admin.com';
        $name = 'dhiaa';
        $password = 12345678;

        

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
