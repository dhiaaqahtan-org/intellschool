<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Saas\Models\Concerns\UsesLandlordConnection;

/**
 * Platform operator identity (plan §5.4).
 *
 * SaaS operators authenticate through a SEPARATE guard ('platform') and
 * are stored in the landlord database. They must never have implicit
 * access to tenant data — only through approved support sessions.
 *
 * This model extends Authenticatable (not LandlordModel) because it
 * needs to work with Laravel's authentication system.
 */
class PlatformUser extends Authenticatable
{
    use HasUuids, Notifiable, SoftDeletes;
    use UsesLandlordConnection;

    protected $table = 'saas_platform_users';

    protected $guard = 'platform';

    protected $fillable = [
        'uuid', 'name', 'email', 'password', 'role', 'status',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
        'last_login_at', 'last_login_ip',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }

    public function isSupport(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'support'], true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Accessor so `$user->is_active` works as well as `isActive()`.
     *
     * There is no is_active COLUMN — the table stores `status`. PlatformPolicy
     * reads `$user->is_active` in every one of its methods, and without this
     * accessor that resolves to null, so every authorization check returned
     * false and the whole platform panel was locked out. Keep this in step
     * with isActive() and scopeActive().
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['super_admin', 'admin']);
    }
}
