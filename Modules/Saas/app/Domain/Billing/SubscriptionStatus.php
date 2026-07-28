<?php

namespace Modules\Saas\Domain\Billing;

/**
 * Canonical subscription lifecycle vocabulary shared by webhooks,
 * reconciliation, entitlements and platform APIs.
 */
enum SubscriptionStatus: string
{
    case Pending = 'pending';
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Grace = 'grace';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
    case Terminated = 'terminated';

    public static function fromProvider(string $status): ?self
    {
        $normalized = str_replace(['-', ' '], '_', strtolower(trim($status)));

        return match ($normalized) {
            'trialing', 'trial' => self::Trialing,
            'active', 'paid', 'current' => self::Active,
            'past_due', 'payment_failed' => self::PastDue,
            'unpaid' => self::Grace,
            'paused', 'on_hold' => self::Paused,
            'canceled', 'cancelled' => self::Cancelled,
            'terminated', 'expired', 'deleted', 'incomplete_expired' => self::Terminated,
            'incomplete', 'pending' => self::Pending,
            default => null,
        };
    }

    public function grantsEntitlements(): bool
    {
        return in_array($this, [
            self::Trialing,
            self::Active,
            self::PastDue,
            self::Grace,
        ], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Cancelled, self::Terminated], true);
    }

    /** @return list<string> */
    public static function reconcilableValues(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            [self::Pending, self::Trialing, self::Active, self::PastDue, self::Grace],
        );
    }

    /** @return list<string> */
    public static function entitlingValues(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            [self::Trialing, self::Active, self::PastDue, self::Grace],
        );
    }
}
