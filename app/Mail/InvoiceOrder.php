<?php

namespace App\Mail;

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
        return new Envelope(
            subject: 'Invoice Pesanan #' . $this->transaction->invoice_no . ' - Citra Ecommerce',
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
