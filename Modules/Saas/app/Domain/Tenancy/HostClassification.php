<?php

namespace Modules\Saas\Domain\Tenancy;

/**
 * What kind of host is this request for?
 *
 * Classification happens before any database work so that marketing and
 * platform traffic never triggers a tenant lookup, and so an unparseable Host
 * header is rejected before it reaches a query.
 */
final readonly class HostClassification
{
    public const MARKETING = 'marketing';
    public const PLATFORM = 'platform';
    public const TENANT = 'tenant';
    public const RESERVED = 'reserved';
    public const INVALID = 'invalid';

    private function __construct(
        public string $kind,
        public ?string $host,
        public ?string $raw = null,
    ) {
    }

    public static function marketing(string $host): self
    {
        return new self(self::MARKETING, $host);
    }

    public static function platform(string $host): self
    {
        return new self(self::PLATFORM, $host);
    }

    public static function tenant(string $host): self
    {
        return new self(self::TENANT, $host);
    }

    /** A platform-owned host that is deliberately not a tenant. */
    public static function reserved(string $host): self
    {
        return new self(self::RESERVED, $host);
    }

    /** Host header was missing, malformed, or not a plausible hostname. */
    public static function invalid(?string $raw): self
    {
        return new self(self::INVALID, null, $raw);
    }

    public function isTenantCandidate(): bool
    {
        return $this->kind === self::TENANT;
    }

    public function isControlPlane(): bool
    {
        return in_array($this->kind, [self::MARKETING, self::PLATFORM, self::RESERVED], true);
    }

    public function isInvalid(): bool
    {
        return $this->kind === self::INVALID;
    }
}
