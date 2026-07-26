<?php

namespace Modules\Saas\Enums;

/**
 * Lifecycle status of a tenant.
 *
 * Deliberately separate from subscription status: a tenant can be `active`
 * while its subscription is `past_due`. Billing state decides entitlements;
 * this decides whether the tenant may be served at all.
 */
enum TenantStatus: string
{
    /** Created in the control plane, database not yet usable. */
    case Pending = 'pending';

    /** Provisioned and serving traffic normally. */
    case Active = 'active';

    /**
     * Read-only. Reached through non-payment or an operator action.
     * Writes are blocked; export and billing stay available (plan §12).
     */
    case Suspended = 'suspended';

    /** Cancelled but inside the retention window. Data still exists. */
    case Cancelled = 'cancelled';

    /** Irreversible deletion in progress or complete. Must never serve. */
    case Terminated = 'terminated';

    /**
     * May the tenant serve HTTP requests at all?
     *
     * Suspended tenants still resolve — they need to reach the billing and
     * export screens. What they may *do* is decided by canWrite().
     */
    public function canServeRequests(): bool
    {
        return match ($this) {
            self::Active, self::Suspended, self::Cancelled => true,
            self::Pending, self::Terminated => false,
        };
    }

    public function canWrite(): bool
    {
        return $this === self::Active;
    }

    /**
     * Should a request to this tenant be answered with 404 rather than an
     * explanatory error? Never confirm that a terminated tenant existed.
     */
    public function shouldMasqueradeAsMissing(): bool
    {
        return $this === self::Terminated;
    }
}
