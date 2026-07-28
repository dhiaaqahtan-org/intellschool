<?php

namespace Modules\Saas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvisioningRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $state = $this->state;

        return [
            'uuid' => $this->uuid,
            'tenant_uuid' => $this->tenant_uuid,
            'state' => $state instanceof \BackedEnum ? $state->value : $state,
            'step' => $this->step,
            'progress' => (int) $this->progress,
            'attempts' => (int) $this->attempts,
            'steps' => $this->steps ?? [],
            'error_summary' => $this->error_summary,
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
        ];
    }
}
