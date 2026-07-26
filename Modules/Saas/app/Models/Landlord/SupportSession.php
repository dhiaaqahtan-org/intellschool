<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A time-limited, approved, fully audited support access session (plan §7, §12).
 *
 * Platform operators may access a tenant ONLY through this mechanism.
 * The existing `is_default` bypass must NOT be used as a universal
 * cross-tenant superuser.
 *
 * Lifecycle:
 *   requested → approved → active → expired/revoked
 *
 * Every session is visible to the tenant owner (banner) and recorded
 * in the audit log.
 */
class SupportSession extends LandlordModel
{
    use HasUuids;

    protected $table = 'saas_support_sessions';

    protected $fillable = [
        'uuid', 'tenant_uuid', 'operator_id', 'operator_email',
        'approver_id', 'approver_email', 'reason', 'scope',
        'requested_at', 'approved_at', 'started_at', 'expires_at',
        'revoked_at', 'revoked_by', 'status',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->started_at !== null
            && $this->expires_at !== null
            && $this->expires_at->isFuture()
            && $this->revoked_at === null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isReadOnly(): bool
    {
        return $this->scope === 'read';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    public function scopeForTenant($query, string $tenantUuid)
    {
        return $query->where('tenant_uuid', $tenantUuid);
    }

    /**
     * Mark this session as revoked.
     */
    public function revoke(?string $revokedBy = null): void
    {
        $this->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoked_by' => $revokedBy,
        ]);
    }
}
