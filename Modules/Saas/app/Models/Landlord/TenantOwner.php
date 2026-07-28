<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant ownership record (plan §5.4).
 *
 * Links a tenant to its owner(s). The actual user record lives in the
 * TENANT database, not here. We store a reference (UUID + email) for
 * cross-database lookup, but there is no foreign key constraint.
 */
class TenantOwner extends LandlordModel
{
    use HasUuids;

    protected $table = 'saas_tenant_owners';

    protected $fillable = [
        'uuid', 'tenant_uuid', 'tenant_user_uuid', 'name', 'email', 'role',
        'status', 'invited_at', 'accepted_at', 'removed_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    public function isPrimaryOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'invited';
    }

    public function accept(): void
    {
        $this->update([
            'status' => 'active',
            'accepted_at' => now(),
        ]);
    }

    public function remove(): void
    {
        $this->update([
            'status' => 'removed',
            'removed_at' => now(),
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'invited');
    }

    public function scopeForTenant($query, string $tenantUuid)
    {
        return $query->where('tenant_uuid', $tenantUuid);
    }
}
