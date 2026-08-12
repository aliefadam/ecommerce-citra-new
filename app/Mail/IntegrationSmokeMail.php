<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IntegrationSmokeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $certificationId) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[SMOKE TEST] Ecommerce Citra email integration');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.integration-smoke',
            with: ['certificationId' => $this->certificationId],
        );
    }
}
