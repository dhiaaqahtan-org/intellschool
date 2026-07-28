<?php

namespace Modules\Saas\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Saas\Models\Landlord\DemoRequest;

class DemoRequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly DemoRequest $demoRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New SaaS demo request: '.$this->demoRequest->school);
    }

    public function content(): Content
    {
        return new Content(view: 'saas::mail.demo-request-received');
    }
}
