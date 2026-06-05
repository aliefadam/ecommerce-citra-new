<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TaxInvoiceDeliveryService;
use App\Services\TaxInvoiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CustomerTaxInvoiceController extends Controller
{
    public function store(Request $request, Transaction $transaction, TaxInvoiceRequestService $taxInvoiceService)
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);

        try {
            $taxInvoiceService->requestForTransaction($transaction, $request->user(), array_merge(
                $request->all(),
                ['requested' => true]
            ));
        } catch (Throwable $e) {
            return back()->withErrors(['tax_invoice' => $e->getMessage()]);
        }

        return redirect()
            ->route('frontend.profil', ['tab' => 'pesanan'])
            ->with('success', 'Permintaan faktur pajak berhasil dikirim. Admin akan memproses data pajak Anda.');
    }

    public function download(Request $request, Transaction $transaction, TaxInvoiceDeliveryService $deliveryService)
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);

        $taxInvoice = $transaction->taxInvoice;

        abort_unless($taxInvoice?->tax_invoice_file_path, 404);
        abort_unless(Storage::disk(TaxInvoiceDeliveryService::DISK)->exists($taxInvoice->tax_invoice_file_path), 404);

        $invoiceNo = preg_replace('/[^A-Za-z0-9\-]+/', '-', (string) $transaction->invoice_no) ?: $transaction->id;

        $deliveryService->recordDownload($taxInvoice, $request->user(), 'customer');

        return Storage::disk(TaxInvoiceDeliveryService::DISK)->download(
            $taxInvoice->tax_invoice_file_path,
            'faktur-pajak-'.$invoiceNo.'.pdf'
        );
    }
}
