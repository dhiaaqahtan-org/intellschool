<?php

namespace Modules\Saas\Domain\Support;

enum SupportScope: string
{
    case Read = 'read';
    case Write = 'write';

    public function permitsWrites(): bool
    {
        return $this === self::Write;
    }
}
