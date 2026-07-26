<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant migration run record (plan §5.1, Phase 4).
 *
 * Tracks schema migration history per-tenant for canary rollouts,
 * failure isolation, and audit.
 */
class MigrationRun extends LandlordModel
{
    protected $table = 'saas_migration_runs';

    protected $fillable = [
        'tenant_uuid', 'release', 'application_version', 'status',
        'started_at', 'finished_at', 'migrations_run', 'error_summary',
        'rollback_to_version', 'rolled_back_at', 'batch_number',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'rolled_back_at' => 'datetime',
        'migrations_run' => 'integer',
        'batch_number' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_uuid', 'uuid');
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markCompleted(int $migrationsRun): void
    {
        $this->update([
            'status' => 'completed',
            'finished_at' => now(),
            'migrations_run' => $migrationsRun,
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_summary' => substr($error, 0, 1000),
        ]);
    }

    public function markRolledBack(string $toVersion): void
    {
        $this->update([
            'status' => 'rolled_back',
            'rolled_back_at' => now(),
            'rollback_to_version' => $toVersion,
        ]);
    }

    public function scopeForTenant($query, string $tenantUuid)
    {
        return $query->where('tenant_uuid', $tenantUuid);
    }

    public function scopeForRelease($query, string $release)
    {
        return $query->where('release', $release);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeBatch($query, int $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }
}
