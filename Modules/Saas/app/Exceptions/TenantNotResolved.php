<?php

namespace Modules\Saas\Exceptions;

use RuntimeException;

/**
 * Thrown when code needs a tenant and none is active.
 *
 * This is always a bug or an attack, never a user error, so it must be loud.
 * Fail closed: never fall back to a default connection.
 */
class TenantNotResolved extends RuntimeException
{
    public static function forHost(?string $host): self
    {
        return new self(sprintf(
            'No active tenant could be resolved for host [%s].',
            $host ?? 'unknown'
        ));
    }

    public static function inContext(string $what): self
    {
        return new self(sprintf(
            '[%s] requires an active tenant but none is initialised.',
            $what
        ));
    }
}
