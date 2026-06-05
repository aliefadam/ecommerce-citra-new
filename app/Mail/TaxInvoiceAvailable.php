<?php

namespace App\Mail;

use App\Models\StoreSetting;
use App\Models\TransactionTaxInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaxInvoiceAvailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TransactionTaxInvoice $taxInvoice) {}

    public function envelope(): Envelope
    {
        $storeName = (string) (StoreSetting::values()['store_name'] ?? 'Ecommerce Citra');

        return new Envelope(
            subject: 'Faktur Pajak Tersedia untuk '.$this->taxInvoice->transaction->invoice_no.' - '.$storeName,
        );
    }

    public function content(): Content
    {
        $taxInvoice = $this->taxInvoice->loadMissing('transaction.details', 'transaction.user');

        return new Content(
            view: 'emails.tax-invoice-available',
            with: [
                'taxInvoice' => $taxInvoice,
                'transaction' => $taxInvoice->transaction,
                'downloadUrl' => route('frontend.profil.orders.tax-invoice.download', $taxInvoice->transaction),
            ],
        );
    }
}
