<?php

namespace Modules\Saas\Domain\Tenancy;

use DomainException;
use Modules\Saas\Enums\TenantStatus;

/**
 * Allowed operator-driven tenant lifecycle transitions.
 *
 * Provisioning owns pending-to-active and deletion tooling owns termination;
 * neither transition is available through a generic status API.
 */
final class TenantLifecycle
{
    public function assertTransition(TenantStatus $from, TenantStatus $to): void
    {
        if ($from === $to) {
            throw new DomainException("Tenant is already {$to->value}.");
        }

        $allowed = match ($from) {
            TenantStatus::Pending => [],
            TenantStatus::Active => [TenantStatus::Suspended, TenantStatus::Cancelled],
            TenantStatus::Suspended => [TenantStatus::Active, TenantStatus::Cancelled],
            TenantStatus::Cancelled => [TenantStatus::Active],
            TenantStatus::Terminated => [],
        };

        if (! in_array($to, $allowed, true)) {
            throw new DomainException(
                "Tenant status cannot transition from {$from->value} to {$to->value} through the operator API."
            );
        }
    }
}
