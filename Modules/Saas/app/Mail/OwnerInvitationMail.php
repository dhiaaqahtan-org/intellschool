<?php

namespace Modules\Saas\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $ownerName,
        public readonly string $schoolName,
        public readonly string $invitationUrl,
        public readonly int $expiresInDays,
    ) {}

    public function build(): self
    {
        return $this
            ->subject(__('saas::marketing.signup.invitation.email_subject', ['school' => $this->schoolName]))
            ->view('saas::mail.owner-invitation');
    }
}
