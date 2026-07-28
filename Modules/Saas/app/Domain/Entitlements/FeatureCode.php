<?php

namespace Modules\Saas\Domain\Entitlements;

use DomainException;

/**
 * Validated entitlement identifier.
 *
 * Feature codes cross route, cache, database, and mobile-client boundaries.
 * Keeping validation here prevents arbitrary route input from becoming cache
 * keys or silently creating unsupported commercial features.
 */
final readonly class FeatureCode
{
    private function __construct(
        public string $value,
    ) {}

    /**
     * @param  list<string>|null  $supported
     */
    public static function from(string $value, ?array $supported = null): self
    {
        $value = strtolower(trim($value));
        $supported ??= array_values(config('saas.entitlements.feature_codes', []));

        if (! self::hasValidFormat($value) || ! in_array($value, $supported, true)) {
            throw new DomainException("Unsupported entitlement feature [{$value}].");
        }

        return new self($value);
    }

    /**
     * @param  list<string>|null  $supported
     */
    public static function tryFrom(string $value, ?array $supported = null): ?self
    {
        try {
            return self::from($value, $supported);
        } catch (DomainException) {
            return null;
        }
    }

    public static function hasValidFormat(string $value): bool
    {
        return preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $value) === 1
            && strlen($value) <= 100;
    }
}
