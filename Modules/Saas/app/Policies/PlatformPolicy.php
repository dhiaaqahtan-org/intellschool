<?php

namespace Modules\Saas\Policies;

use Modules\Saas\Models\Landlord\PlatformUser;
use Modules\Saas\Models\Landlord\Tenant;

/**
 * Platform authorization policy (plan §5.4).
 *
 * Deny-by-default: every action requires an explicit role grant.
 * Platform operators manage the control plane ONLY — they never have
 * implicit access to tenant school data.
 *
 * Roles (hierarchical):
 *   super_admin > admin > support > billing > readonly
 */
class PlatformPolicy
{
    /**
     * Role hierarchy — higher index = more privilege.
     */
    private const ROLE_HIERARCHY = [
        'readonly' => 1,
        'billing' => 2,
        'support' => 3,
        'admin' => 4,
        'super_admin' => 5,
    ];

    /**
     * Super-admin gate: only super_admin can manage platform users.
     */
    public function manageUsers(PlatformUser $user): bool
    {
        return $this->hasRole($user, 'super_admin');
    }

    /**
     * Tenant creation requires admin or above.
     */
    public function createTenant(PlatformUser $user): bool
    {
        return $this->hasMinimumRole($user, 'admin');
    }

    /**
     * Tenant suspension/cancellation requires admin or above.
     */
    public function suspendTenant(PlatformUser $user, Tenant $tenant): bool
    {
        return $this->hasMinimumRole($user, 'admin');
    }

    /**
     * Tenant reactivation requires admin or above.
     */
    public function reactivateTenant(PlatformUser $user, Tenant $tenant): bool
    {
        return $this->hasMinimumRole($user, 'admin');
    }

    /**
     * Viewing tenants is available to all authenticated platform users.
     */
    public function viewAnyTenant(PlatformUser $user): bool
    {
        return $user->is_active;
    }

    /**
     * Viewing a single tenant.
     */
    public function viewTenant(PlatformUser $user, Tenant $tenant): bool
    {
        return $user->is_active;
    }

    /**
     * Updating tenant metadata requires admin or above.
     */
    public function updateTenant(PlatformUser $user, Tenant $tenant): bool
    {
        return $this->hasMinimumRole($user, 'admin');
    }

    /**
     * Domain management requires admin or above.
     */
    public function manageDomains(PlatformUser $user, Tenant $tenant): bool
    {
        return $this->hasMinimumRole($user, 'admin');
    }

    /**
     * Triggering provisioning requires admin or above.
     */
    public function provision(PlatformUser $user, Tenant $tenant): bool
    {
        return $this->hasMinimumRole($user, 'admin');
    }

    /**
     * Billing operations (plan changes, subscription management).
     */
    public function manageBilling(PlatformUser $user): bool
    {
        return $this->hasMinimumRole($user, 'billing');
    }

    /**
     * Support session access requires support role or above.
     */
    public function accessSupport(PlatformUser $user): bool
    {
        return $this->hasMinimumRole($user, 'support');
    }

    /**
     * Audit log viewing requires admin or above.
     */
    public function viewAuditLog(PlatformUser $user): bool
    {
        return $this->hasMinimumRole($user, 'admin');
    }

    /**
     * Check if user has at least the given role level.
     */
    private function hasMinimumRole(PlatformUser $user, string $requiredRole): bool
    {
        if (! $user->is_active) {
            return false;
        }

        $userLevel = self::ROLE_HIERARCHY[$user->role] ?? 0;
        $requiredLevel = self::ROLE_HIERARCHY[$requiredRole] ?? 99;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Check if user has exactly the given role.
     */
    private function hasRole(PlatformUser $user, string $role): bool
    {
        return $user->is_active && $user->role === $role;
    }
}
