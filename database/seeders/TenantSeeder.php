<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Helpers\SysHelper;
use App\Models\Config\Config;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tenant database seeder — called during provisioning (plan §11, step 3).
 *
 * Seeds the minimum structural data a school needs to be functional:
 *   1. System configuration defaults
 *   2. ACL permissions
 *   3. Default team (campus)
 *   4. Default academic period
 *   5. Roles + permission grants
 *   6. Notification/document templates
 *   7. Owner user account (from landlord tenant_owners record)
 *
 * This seeder runs INSIDE the tenant database connection (the provisioner
 * sets the default connection before calling it). It is idempotent.
 */
class TenantSeeder extends Seeder
{
    /**
     * Tenant metadata passed from the provisioner.
     */
    private string $tenantDisplayName = 'Default School';

    private string $tenantSlug = 'default';

    private string $tenantUuid = '';

    private ?string $ownerEmail = null;

    private ?string $ownerName = null;

    public function run(): void
    {
        // Pull tenant context from the provisioning state if available.
        $this->resolveTenantContext();

        activity()->disableLogging();
        SysHelper::setTeam(null);

        // 1. Baseline configuration.
        $this->seedConfig();

        // 2. ACL permissions.
        $this->seedPermissions();

        // 3. Default team (campus).
        $team = $this->seedTeam();

        // 4. Default academic period.
        $this->seedAcademicPeriod($team);

        // 5. Roles + permission assignments.
        SysHelper::setTeam($team->id);
        $this->seedRoles();
        $this->seedRolePermissions();

        // 6. Templates.
        $this->seedTemplates();

        // 7. Owner account.
        $this->seedOwner($team);

        SysHelper::setTeam(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        activity()->enableLogging();
    }

    /**
     * Resolve tenant metadata from the tenant_installations table or
     * fall back to sensible defaults.
     */
    private function resolveTenantContext(): void
    {
        try {
            if (Schema::hasTable('tenant_installations')) {
                $installation = DB::table('tenant_installations')->first();
                if ($installation) {
                    $this->tenantUuid = $installation->tenant_uuid ?? '';
                    $this->tenantSlug = $installation->tenant_slug ?? 'default';
                }
            }
        } catch (\Throwable) {
            // Non-fatal — use defaults.
        }

        // Try to get display name from landlord (cross-database read).
        if ($this->tenantUuid !== '') {
            try {
                $tenant = DB::connection(config('saas.database.landlord_connection', 'landlord'))
                    ->table('saas_tenants')
                    ->where('uuid', $this->tenantUuid)
                    ->first();

                if ($tenant) {
                    $this->tenantDisplayName = $tenant->display_name ?? $this->tenantSlug;
                }

                // Get owner info.
                $owner = DB::connection(config('saas.database.landlord_connection', 'landlord'))
                    ->table('saas_tenant_owners')
                    ->where('tenant_uuid', $this->tenantUuid)
                    ->where('role', 'owner')
                    ->first();

                if ($owner) {
                    $this->ownerEmail = $owner->email;
                    $this->ownerName = $owner->name;
                }
            } catch (\Throwable) {
                // Cross-database read failed — continue with defaults.
            }
        }
    }

    private function seedConfig(): void
    {
        try {
            $configData = Arr::getVar('config');

            if (isset($configData['general']) && is_array($configData['general'])) {
                foreach ($configData['general'] as &$item) {
                    if (isset($item['name']) && $item['name'] === 'app_name') {
                        $item['value'] = $this->tenantDisplayName;
                    }
                    if (isset($item['name']) && $item['name'] === 'meta_author') {
                        $item['value'] = $this->tenantDisplayName;
                    }
                }
            }

            foreach ($configData as $section => $items) {
                $value = collect($items)
                    ->filter(fn ($item) => Arr::has($item, 'name'))
                    ->pluck('value', 'name')
                    ->all();

                Config::query()->firstOrCreate(
                    ['name' => $section, 'team_id' => null],
                    ['value' => $value]
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function seedPermissions(): void
    {
        try {
            $this->call(PermissionSeeder::class);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function seedTeam(): Team
    {
        $team = Team::query()->first();

        if (! $team) {
            $team = Team::create([
                'name' => $this->tenantDisplayName,
            ]);
        }

        return $team;
    }

    private function seedAcademicPeriod(Team $team): void
    {
        try {
            // Use the existing DefaultAcademicPeriodSeeder logic.
            $this->call(DefaultAcademicPeriodSeeder::class);
        } catch (\Throwable $e) {
            // Fallback: create period directly.
            try {
                $startYear = now()->month >= 7 ? now()->year : now()->year - 1;
                $endYear = $startYear + 1;

                DB::table('periods')->insertOrIgnore([
                    'team_id' => $team->id,
                    'name' => "Academic Year {$startYear}-{$endYear}",
                    'code' => "AY-{$startYear}-{$endYear}",
                    'shortcode' => "{$startYear}-{$endYear}",
                    'alias' => "Academic Year {$startYear}-{$endYear}",
                    'start_date' => "{$startYear}-07-01",
                    'end_date' => "{$endYear}-06-30",
                    'is_default' => true,
                    'description' => 'Default academic period created during tenant provisioning.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $inner) {
                report($inner);
            }
        }
    }

    private function seedRoles(): void
    {
        try {
            $this->call(RoleSeeder::class);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function seedRolePermissions(): void
    {
        try {
            $this->call(AssignPermissionSeeder::class);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function seedTemplates(): void
    {
        try {
            $this->call(TemplateSeeder::class);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Create the owner's user account in the tenant database.
     * The owner email comes from the landlord's tenant_owners table.
     */
    private function seedOwner(Team $team): void
    {
        $email = $this->ownerEmail ?? 'admin@'.$this->tenantSlug.'.example.com';
        $name = $this->ownerName ?? $this->tenantDisplayName.' Admin';

        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            // Ensure the existing user has admin role.
            SysHelper::setTeam($team->id);
            if (! $existing->hasRole('admin')) {
                $existing->assignRole('admin');
            }

            $this->linkOwnerToTenantUser($existing);

            return;
        }

        $user = new User;
        $user->forceFill([
            'name' => $name,
            'username' => Str::slug($this->tenantSlug, '_').'_admin',
            'email' => $email,
            'password' => bcrypt(Str::random(16)), // Owner sets password via invitation
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVATED->value,
            'meta' => [
                'is_default' => true,
                'current_team_id' => $team->id,
                'provisioned_by' => 'saas_tenant_seeder',
            ],
        ]);
        $user->save();

        // Assign admin role.
        SysHelper::setTeam($team->id);
        $user->assignRole('admin');
        $this->linkOwnerToTenantUser($user);
    }

    private function linkOwnerToTenantUser(User $user): void
    {
        if ($this->tenantUuid === '' || $this->ownerEmail === null) {
            return;
        }

        try {
            DB::connection(config('saas.database.landlord_connection', 'landlord'))
                ->table('saas_tenant_owners')
                ->where('tenant_uuid', $this->tenantUuid)
                ->where('email', $this->ownerEmail)
                ->update([
                    'tenant_user_uuid' => $user->uuid,
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
