<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Helpers\SysHelper;
use App\Models\Config\Config;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provision a ready-to-use deployment without any installation wizard.
 *
 * This reproduces everything the removed installer used to do, so a fresh copy
 * of the application works after nothing more than:
 *
 *     php artisan migrate --force
 *     php artisan db:seed --force        (DatabaseSeeder calls this)
 *
 * Every step is idempotent — safe to run again on an existing database.
 */
class ApplicationSeeder extends Seeder
{
    /** Default super-admin credentials (change from the profile after login). */
    private const ADMIN_NAME = 'Administrator';

    private const ADMIN_USERNAME = 'admin';

    private const ADMIN_EMAIL = 'admin@gmail.com';

    private const ADMIN_PASSWORD = '12345678';

    public function run(): void
    {
        activity()->disableLogging();
        SysHelper::setTeam(null);

        // 1. Baseline settings so both the API and the client have real values
        //    (SetSystemConfig replaces runtime config with these DB rows).
        $this->seedConfig();

        // 2. ACL permissions from resources/var/permission.json.
        $this->call(PermissionSeeder::class);

        // 3. The default team. TeamObserver adds default ledger types + options.
        $team = Team::query()->firstOrCreate([
            'name' => config('provisioning.team_name', 'Default'),
        ]);

        // 4. A default academic period. Most application pages require one.
        $this->call(DefaultAcademicPeriodSeeder::class);

        // 5. Roles (incl. the global "admin" role) + their permission grants.
        SysHelper::setTeam($team->id);
        $this->call(RoleSeeder::class);
        $this->call(AssignPermissionSeeder::class);

        // 6. Notification / document templates.
        $this->call(TemplateSeeder::class);

        // 7. Public Arabic CMS pages and their required navigation.
        //    The seeder preserves content that has already been customised.
        $this->call(DefaultPageSeeder::class);
        $this->call(SchoolWebsiteSeeder::class);

        // 8. The super-admin account, created here in the seeder as requested.
        $admin = $this->ensureAdmin($team);

        // 9. Grant the admin role within the team and clear the ACL cache.
        SysHelper::setTeam($team->id);
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
        SysHelper::setTeam(null);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        activity()->enableLogging();

        $this->command?->info('Setup complete. Login with '.self::ADMIN_EMAIL.' / '.self::ADMIN_PASSWORD);
    }

    /**
     * Seed one global config row per section using the defaults defined in
     * resources/var/config.json. firstOrCreate keeps any values an admin has
     * already customised when this runs again.
     */
    private function seedConfig(): void
    {
        foreach (Arr::getVar('config') as $section => $items) {
            $value = collect($items)
                ->filter(fn ($item) => Arr::has($item, 'name'))
                ->pluck('value', 'name')
                ->all();

            Config::query()->firstOrCreate(
                ['name' => $section, 'team_id' => null],
                ['value' => $value]
            );
        }
    }

    /**
     * Create (or normalise) the default super-admin. Flagged is_default so it
     * bypasses team permission scoping and can administer every team.
     */
    private function ensureAdmin(Team $team): User
    {
        $admin = User::query()->where('email', self::ADMIN_EMAIL)->first();

        if (! $admin) {
            $admin = new User;
            $admin->forceFill([
                'name' => self::ADMIN_NAME,
                'username' => self::ADMIN_USERNAME,
                'email' => self::ADMIN_EMAIL,
                'password' => bcrypt(self::ADMIN_PASSWORD),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVATED->value,
                'meta' => [
                    'is_default' => true,
                    'current_team_id' => $team->id,
                ],
            ]);
            $admin->save();

            return $admin;
        }

        // Existing account — keep it usable, privileged and pointed at the team.
        $admin->forceFill([
            'password' => bcrypt(self::ADMIN_PASSWORD),
            'status' => UserStatus::ACTIVATED->value,
        ])->save();
        $admin->setMeta([
            'is_default' => true,
            'current_team_id' => $team->id,
        ], true);

        return $admin;
    }
}
