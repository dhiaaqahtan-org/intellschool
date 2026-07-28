<?php

namespace Modules\Saas\Domain\Identity;

/**
 * One-time invitation credential. Only the SHA-256 digest is persisted.
 */
final readonly class InvitationToken
{
    private function __construct(public string $plainText) {}

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(32)));
    }

    public static function fromPlainText(string $plainText): self
    {
        return new self($plainText);
    }

    public function digest(): string
    {
        return hash('sha256', $this->plainText);
    }
}
