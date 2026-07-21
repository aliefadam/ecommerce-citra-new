<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\DocumentPayment;
use App\Services\DocumentPaymentService;

/**
 * Shared endpoint for deleting/correcting a payment ledger row, regardless of
 * which payable it belongs to (ProformaInvoice, B2bInvoice). See
 * App\Services\DocumentPaymentService for the actual recalculation logic.
 */
class DocumentPaymentController extends Controller
{
    use ScopesToActiveCompany;

    public function __construct(private readonly DocumentPaymentService $paymentService) {}

    public function destroy(DocumentPayment $payment)
    {
        $payable = $payment->payable;
        abort_if($payable === null, 404);
        $this->guardCompanyOwnership($payable->company_id);

        $this->paymentService->delete($payment);

        return back()->with('success', 'Baris pembayaran berhasil dihapus.');
    }
}
