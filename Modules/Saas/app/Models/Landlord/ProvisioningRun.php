<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Saas\Enums\ProvisioningState;

class ProvisioningRun extends LandlordModel
{
    use HasUuids;

    protected $table = 'saas_provisioning_runs';

    protected $fillable = [
        'uuid', 'tenant_uuid', 'idempotency_key', 'state', 'step',
        'attempts', 'progress', 'started_at', 'finished_at',
        'error_summary', 'steps',
    ];

    protected $casts = [
        'state' => ProvisioningState::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'steps' => 'array',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    /**
     * Record the outcome of one step.
     *
     * Steps accumulate rather than overwrite, so a resumed run keeps the
     * history of what already succeeded — that history is what makes the
     * resume safe rather than a blind replay.
     */
    public function recordStep(string $step, bool $ok, ?string $error = null): void
    {
        $steps = $this->steps ?? [];
        $existing = $steps[$step] ?? ['attempts' => 0, 'started_at' => null];

        $steps[$step] = [
            'attempts' => ($existing['attempts'] ?? 0) + 1,
            'started_at' => $existing['started_at'] ?? now()->toIso8601String(),
            'finished_at' => now()->toIso8601String(),
            'ok' => $ok,
            'error' => $error,
        ];

        $this->steps = $steps;
        $this->step = $step;
        $this->save();
    }

    public function hasCompleted(string $step): bool
    {
        return ($this->steps[$step]['ok'] ?? false) === true;
    }
}
