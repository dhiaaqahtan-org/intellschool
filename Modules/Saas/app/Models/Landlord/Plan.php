<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Saas\Database\Factories\PlanFactory;

/**
 * Versioned plan definition (plan §9.3).
 *
 * A price or feature change creates a NEW version; it never silently
 * rewrites an existing customer contract. Subscriptions reference a
 * specific plan_id (version), not just a plan_code.
 */
class Plan extends LandlordModel
{
    use HasFactory;
    use HasUuids;

    protected $table = 'saas_plans';

    protected $fillable = [
        'uuid', 'plan_code', 'version', 'display_name', 'description',
        'billing_interval', 'currency', 'price_cents', 'trial_days',
        'active_from', 'active_until', 'is_public',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'trial_days' => 'integer',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
        'is_public' => 'boolean',
    ];

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('active_from')->orWhere('active_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('active_until')->orWhere('active_until', '>=', now());
        });
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function isCurrentlyActive(): bool
    {
        $now = now();

        return ($this->active_from === null || $this->active_from->lte($now))
            && ($this->active_until === null || $this->active_until->gte($now));
    }
}
