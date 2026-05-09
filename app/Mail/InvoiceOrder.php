<?php

namespace App\Mail;

use App\Models\StoreSetting;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceOrder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction) {}

    public function envelope(): Envelope
    {
        $storeName = (string) (StoreSetting::values()['store_name'] ?? 'Ecommerce Citra');

        return new Envelope(
            subject: 'Invoice Pesanan #' . $this->transaction->invoice_no . ' - ' . $storeName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-order',
            with: [
                'transaction' => $this->transaction->load('details', 'user'),
            ],
        );
    }
}
