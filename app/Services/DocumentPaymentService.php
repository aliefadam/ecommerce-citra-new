<?php

namespace App\Services;

use App\Models\DocumentPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Shared payment ledger logic for any model exposing grand_total/paid_amount/
 * outstanding_amount/status columns plus a documentPayments() morphMany relation
 * (ProformaInvoice, and later B2bInvoice). Recalculates paid/outstanding/status
 * under a row lock every time a payment is recorded or deleted.
 */
class DocumentPaymentService
{
    public function record(Model $payable, array $data, ?int $recordedByAdminId): DocumentPayment
    {
        return DB::transaction(function () use ($payable, $data, $recordedByAdminId) {
            $locked = $payable::query()->lockForUpdate()->findOrFail($payable->id);

            $outstanding = max(0, (int) $locked->grand_total - (int) $locked->documentPayments()->sum('amount'));
            $amount = (int) $data['amount'];

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Nominal pembayaran harus lebih dari 0.']);
            }
            if ($amount > $outstanding) {
                throw ValidationException::withMessages(['amount' => 'Nominal melebihi sisa piutang (Rp '.number_format($outstanding, 0, ',', '.').').']);
            }

            $payment = $locked->documentPayments()->create([
                'amount' => $amount,
                'payment_date' => $data['payment_date'],
                'note' => $data['note'] ?? null,
                'proof_path' => $data['proof_path'] ?? null,
                'source' => $data['source'] ?? DocumentPayment::SOURCE_MANUAL,
                'recorded_by_admin_id' => $recordedByAdminId,
            ]);

            $this->recalculate($locked);

            return $payment;
        });
    }

    public function delete(DocumentPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->source !== DocumentPayment::SOURCE_MANUAL) {
                throw ValidationException::withMessages(['payment' => 'Baris kredit otomatis tidak bisa dihapus manual.']);
            }

            $payableClass = $payment->payable_type;
            $payable = $payableClass::query()->lockForUpdate()->findOrFail($payment->payable_id);
            $payment->delete();

            $this->recalculate($payable);
        });
    }

    public function recalculate(Model $payable): void
    {
        $paid = (int) $payable->documentPayments()->sum('amount');
        $outstanding = max(0, (int) $payable->grand_total - $paid);

        $status = $payable->status;
        if ($status !== 'cancelled') {
            $status = match (true) {
                $paid <= 0 => 'issued',
                $outstanding <= 0 => 'paid',
                default => 'partially_paid',
            };
        }

        $payable->update([
            'paid_amount' => $paid,
            'outstanding_amount' => $outstanding,
            'status' => $status,
        ]);
    }
}
