<?php

namespace Modules\Saas\Services\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\SupportSession;
use Modules\Saas\Models\Landlord\Tenant;

/**
 * Manages platform operator support access to tenant environments (plan §7, §12).
 *
 * Rules:
 *  - Access requires an explicit reason and (optionally) tenant owner approval.
 *  - Sessions are time-limited (default 60 minutes, configurable).
 *  - Default scope is read-only; write access requires explicit justification.
 *  - A visible banner is shown to tenant users during active sessions.
 *  - Every session start, action, and end is audited.
 *  - The existing `is_default` bypass is NOT used for support access.
 */
class SupportAccessService
{
    private const CACHE_PREFIX = 'saas:support_active:';

    /**
     * Request a new support access session.
     */
    public function requestAccess(
        string $tenantUuid,
        int $operatorId,
        string $operatorEmail,
        string $reason,
        string $scope = 'read',
    ): SupportSession {
        $tenant = Tenant::where('uuid', $tenantUuid)->firstOrFail();

        // Validate scope.
        if (! in_array($scope, ['read', 'write'], true)) {
            $scope = 'read';
        }

        // Write access requires stronger justification.
        if ($scope === 'write' && strlen($reason) < 20) {
            throw new \InvalidArgumentException(
                'Write access requires a detailed reason (minimum 20 characters).'
            );
        }

        $maxDuration = config('saas.support.max_duration', 60);
        $requiresApproval = config('saas.support.requires_approval', true);

        $session = SupportSession::create([
            'tenant_uuid' => $tenantUuid,
            'operator_id' => $operatorId,
            'operator_email' => $operatorEmail,
            'reason' => $reason,
            'scope' => $scope,
            'requested_at' => now(),
            'expires_at' => now()->addMinutes($maxDuration),
            'status' => $requiresApproval ? 'requested' : 'approved',
        ]);

        $this->audit($tenantUuid, 'support.requested', [
            'session_uuid' => $session->uuid,
            'operator_email' => $operatorEmail,
            'scope' => $scope,
            'reason' => $reason,
        ]);

        return $session;
    }

    /**
     * Approve a pending support session.
     */
    public function approve(
        SupportSession $session,
        int $approverId,
        string $approverEmail,
    ): SupportSession {
        if ($session->status !== 'requested') {
            throw new \InvalidArgumentException(
                "Cannot approve a session in '{$session->status}' state."
            );
        }

        $session->update([
            'status' => 'approved',
            'approver_id' => $approverId,
            'approver_email' => $approverEmail,
            'approved_at' => now(),
        ]);

        $this->audit($session->tenant_uuid, 'support.approved', [
            'session_uuid' => $session->uuid,
            'approver_email' => $approverEmail,
        ]);

        return $session;
    }

    /**
     * Start an approved session (operator begins working).
     */
    public function start(SupportSession $session): SupportSession
    {
        if ($session->status !== 'approved') {
            throw new \InvalidArgumentException(
                "Cannot start a session in '{$session->status}' state."
            );
        }

        if ($session->isExpired()) {
            $session->update(['status' => 'expired']);

            throw new \InvalidArgumentException('Session has expired. Request a new one.');
        }

        $session->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        // Cache the active session for quick middleware checks.
        Cache::put(
            self::CACHE_PREFIX.$session->tenant_uuid,
            $session->uuid,
            $session->expires_at
        );

        $this->audit($session->tenant_uuid, 'support.started', [
            'session_uuid' => $session->uuid,
            'operator_email' => $session->operator_email,
            'scope' => $session->scope,
        ]);

        return $session;
    }

    /**
     * Revoke an active session (operator or admin ends it early).
     */
    public function revoke(SupportSession $session, ?string $revokedBy = null): void
    {
        $session->revoke($revokedBy);

        Cache::forget(self::CACHE_PREFIX.$session->tenant_uuid);

        $this->audit($session->tenant_uuid, 'support.revoked', [
            'session_uuid' => $session->uuid,
            'revoked_by' => $revokedBy,
        ]);
    }

    /**
     * Check if a tenant currently has an active support session.
     * Used by middleware to show the banner and enforce scope.
     */
    public function getActiveSession(string $tenantUuid): ?SupportSession
    {
        $sessionUuid = Cache::get(self::CACHE_PREFIX.$tenantUuid);

        if (! $sessionUuid) {
            return null;
        }

        $session = SupportSession::where('uuid', $sessionUuid)
            ->where('tenant_uuid', $tenantUuid)
            ->first();

        if ($session && $session->isActive()) {
            return $session;
        }

        // Stale cache entry.
        Cache::forget(self::CACHE_PREFIX.$tenantUuid);

        return null;
    }

    /**
     * Check if write access is currently granted for a tenant.
     */
    public function hasActiveWriteAccess(string $tenantUuid): bool
    {
        $session = $this->getActiveSession($tenantUuid);

        return $session !== null && ! $session->isReadOnly();
    }

    /**
     * Clean up expired sessions. Called by scheduler.
     */
    public function cleanupExpired(): int
    {
        $expired = SupportSession::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $session) {
            $session->update(['status' => 'expired']);
            Cache::forget(self::CACHE_PREFIX.$session->tenant_uuid);

            $this->audit($session->tenant_uuid, 'support.expired', [
                'session_uuid' => $session->uuid,
            ]);
        }

        return $expired->count();
    }

    /**
     * Record an audit event for support access.
     */
    private function audit(string $tenantUuid, string $action, array $context = []): void
    {
        AuditEvent::record(
            action: $action,
            tenantUuid: $tenantUuid,
            context: $context,
            actorType: 'platform_operator',
            actorId: $context['operator_email'] ?? $context['approver_email'] ?? 'system',
        );

        Log::info("Support access: {$action}", array_merge(
            ['tenant_uuid' => $tenantUuid],
            $context
        ));
    }
}
