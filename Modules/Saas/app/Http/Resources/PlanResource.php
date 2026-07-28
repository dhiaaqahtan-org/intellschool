<?php

namespace Modules\Saas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->plan_code,
            'version' => (int) $this->version,
            'name' => $this->display_name,
            'description' => $this->description,
            'billing_interval' => $this->billing_interval,
            'currency' => $this->currency,
            'price_cents' => (int) $this->price_cents,
            'trial_days' => (int) $this->trial_days,
            'is_public' => (bool) $this->is_public,
            'active_from' => $this->active_from?->toIso8601String(),
            'active_until' => $this->active_until?->toIso8601String(),
            'features' => $this->whenLoaded('features', fn () => $this->features->map(fn ($feature) => [
                'code' => $feature->feature_code,
                'enabled' => (bool) $feature->enabled,
                'limit_value' => $feature->limit_value,
                'limit_type' => $feature->limit_type,
            ])->values()),
        ];
    }
}
