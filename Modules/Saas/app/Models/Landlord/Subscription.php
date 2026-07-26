<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscription lifecycle for a tenant (plan §9.2).
 *
 * States: pending → trialing → active → past_due → grace → paused →
 *         canceled → terminated
 *
 * Access is provisioned from verified webhook state, NEVER from the browser
 * redirect. The provider_subscription_id is the reconciliation anchor.
 */
class Subscription extends LandlordModel
{
    use HasUuids;

    protected $table = 'saas_subscriptions';

    protected $fillable = [
        'uuid', 'tenant_uuid', 'plan_id', 'provider', 'provider_customer_id',
        'provider_subscription_id', 'status', 'trial_ends_at',
        'current_period_start', 'current_period_end', 'grace_ends_at',
        'cancelled_at', 'terminated_at', 'cancel_reason',
        'cancel_at_period_end', 'provider_meta',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'grace_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'terminated_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'provider_meta' => 'array',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing'], true);
    }

    public function isInGracePeriod(): bool
    {
        return $this->status === 'grace'
            && $this->grace_ends_at !== null
            && $this->grace_ends_at->isFuture();
    }

    public function isTerminated(): bool
    {
        return in_array($this->status, ['canceled', 'terminated'], true);
    }

    public function scopeForTenant($query, string $tenantUuid)
    {
        return $query->where('tenant_uuid', $tenantUuid);
    }

    public function scopeActiveOrTrialing($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }
}
