<?php

namespace Modules\Saas\Enums;

/**
 * States of a provisioning run (plan §11).
 *
 * Every step records start, end, attempt count and a safe failure summary.
 * Retrying a step must never create a duplicate tenant, database, owner,
 * subscription or domain — idempotency is enforced by the run's idempotency
 * key, not by hoping the step is not re-entered.
 */
enum ProvisioningState: string
{
    case PendingVerification = 'pending_verification';
    case PendingBilling      = 'pending_billing';
    case Queued              = 'queued';
    case AllocatingDatabase  = 'allocating_database';
    case Migrating           = 'migrating';
    case Seeding             = 'seeding';
    case ConfiguringDomain   = 'configuring_domain';
    case Verifying           = 'verifying';
    case Ready               = 'ready';
    case FailedRecoverable   = 'failed_recoverable';
    case FailedManualReview  = 'failed_manual_review';

    /**
     * Human-readable label. See TenantStatus::label() — views must never call
     * ucfirst()/str_replace() on the enum object itself.
     */
    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }

    /** Badge class for the platform panel. */
    public function badgeClass(): string
    {
        return match (true) {
            $this === self::Ready => 'badge-success',
            $this->isFailure() => 'badge-danger',
            default => 'badge-warning',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Ready, self::FailedManualReview], true);
    }

    public function isFailure(): bool
    {
        return in_array($this, [self::FailedRecoverable, self::FailedManualReview], true);
    }

    public function isRetryable(): bool
    {
        return $this === self::FailedRecoverable;
    }

    /**
     * Ordered pipeline. Used to decide what runs next and to reject a
     * transition that would move a run backwards.
     */
    public static function pipeline(): array
    {
        return [
            self::Queued,
            self::AllocatingDatabase,
            self::Migrating,
            self::Seeding,
            self::ConfiguringDomain,
            self::Verifying,
            self::Ready,
        ];
    }

    public function next(): ?self
    {
        $pipeline = self::pipeline();
        $index = array_search($this, $pipeline, true);

        if ($index === false || $index === count($pipeline) - 1) {
            return null;
        }

        return $pipeline[$index + 1];
    }
}
