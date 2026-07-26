<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

/**
 * Append-only control-plane audit trail.
 *
 * Updates and deletes are blocked at the model level. If application code can
 * rewrite the audit log, the audit log is not evidence. Retention trimming is
 * an operational task with its own authorisation, not something a controller
 * does by calling delete().
 */
class AuditEvent extends LandlordModel
{
    use HasUuids;

    protected $table = 'saas_audit_events';

    public $timestamps = false;

    protected $fillable = [
        'uuid', 'tenant_uuid', 'action', 'actor_type', 'actor_id', 'actor_label',
        'subject_type', 'subject_id', 'ip_hash', 'correlation_id', 'context', 'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $event) {
            $event->created_at ??= now();
        });

        static::updating(fn () => throw new \LogicException('Audit events are immutable.'));
        static::deleting(fn () => throw new \LogicException('Audit events cannot be deleted by application code.'));
    }

    /**
     * @param  array  $context  Must already be redacted — no credentials, no
     *                          raw billing payloads, no student data.
     */
    public static function record(
        string $action,
        ?string $tenantUuid = null,
        array $context = [],
        string $actorType = 'system',
        ?string $actorId = null,
        ?string $actorLabel = null,
        ?string $ip = null,
    ): self {
        return static::create([
            'tenant_uuid' => $tenantUuid,
            'action' => $action,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'actor_label' => $actorLabel,
            // Hashed, not stored raw: enough to correlate abuse, not enough to
            // become a personal-data retention problem on its own.
            'ip_hash' => $ip ? hash('sha256', $ip) : null,
            'correlation_id' => Str::limit((string) (request()?->header('X-Request-Id') ?: Str::uuid()), 64, ''),
            'context' => $context ?: null,
        ]);
    }
}
