<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A feature included in a specific plan version.
 */
class PlanFeature extends LandlordModel
{
    protected $table = 'saas_plan_features';

    protected $fillable = [
        'plan_id', 'feature_code', 'enabled', 'limit_value', 'limit_type',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'limit_value' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function isUnlimited(): bool
    {
        return $this->limit_value === null;
    }

    public function isHardLimit(): bool
    {
        return $this->limit_type === 'hard';
    }
}
