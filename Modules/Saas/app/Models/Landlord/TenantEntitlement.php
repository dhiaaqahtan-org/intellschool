<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A per-tenant feature override (plan §8).
 *
 * Sits above plan features in the resolution order, so it is how negotiated
 * enterprise terms, goodwill grants and temporary trials of a paid feature are
 * expressed without minting a bespoke plan for one customer.
 *
 * Every row carries a source and reason. An override that silently enables a
 * paid feature with no recorded justification is indistinguishable from a bug
 * or an abuse of platform access.
 */
class TenantEntitlement extends LandlordModel
{
    protected $table = 'saas_tenant_entitlements';

    protected $fillable = [
        'tenant_uuid', 'feature_code', 'enabled', 'limit_value',
        'source', 'reason', 'valid_from', 'valid_until', 'granted_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'limit_value' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    /**
     * Only overrides currently inside their validity window.
     *
     * An expired override must stop applying on its own. Relying on a cleanup
     * job to delete stale rows means a missed job silently extends a customer's
     * entitlements past what they are paying for.
     */
    public function scopeInEffect(Builder $query, ?\DateTimeInterface $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $at))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $at));
    }
}
