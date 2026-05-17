<?php

namespace App\Mail;

use App\Models\StoreSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaign extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $messageBody,
        public ?string $ctaLabel = null,
        public ?string $ctaUrl = null,
        public ?string $heroImageUrl = null,
        public ?string $unsubscribeUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = (string) (StoreSetting::values()['store_name'] ?? 'Ecommerce Citra');

        return new Envelope(
            subject: $this->subjectLine . ' - ' . $storeName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-campaign',
            with: [
                'subjectLine' => $this->subjectLine,
                'messageBody' => $this->messageBody,
                'ctaLabel' => $this->ctaLabel,
                'ctaUrl' => $this->ctaUrl,
                'heroImageUrl' => $this->heroImageUrl,
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'storeName' => (string) (StoreSetting::values()['store_name'] ?? 'Ecommerce Citra'),
            ],
        );
    }
}
