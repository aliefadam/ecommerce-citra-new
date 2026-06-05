<?php

namespace App\Services;

use App\Mail\TaxInvoiceAvailable;
use App\Models\TransactionStatusHistory;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TaxInvoiceDeliveryService
{
    public const DISK = 'local';

    public function upload(TransactionTaxInvoice $taxInvoice, UploadedFile $file, ?User $admin, array $data): TransactionTaxInvoice
    {
        $path = $file->storeAs(
            'tax-invoices/'.$taxInvoice->transaction_id,
            Str::uuid().'.pdf',
            self::DISK
        );

        $oldPath = $taxInvoice->tax_invoice_file_path;

        try {
            $updated = DB::transaction(function () use ($taxInvoice, $path, $admin, $data) {
                $fresh = TransactionTaxInvoice::query()
                    ->with('transaction')
                    ->lockForUpdate()
                    ->findOrFail($taxInvoice->id);

                $fromStatus = (string) $fresh->status;

                $fresh->update([
                    'status' => TransactionTaxInvoice::STATUS_ISSUED,
                    'tax_invoice_number' => $data['tax_invoice_number'] ?? $fresh->tax_invoice_number,
                    'tax_invoice_date' => $data['tax_invoice_date'] ?? $fresh->tax_invoice_date,
                    'tax_invoice_file_path' => $path,
                    'uploaded_by_admin_id' => $admin?->id,
                    'issued_at' => now(),
                    'admin_note' => $data['admin_note'] ?? $fresh->admin_note,
                    'email_failed_at' => null,
                    'email_failure_reason' => null,
                    'rejected_at' => null,
                    'rejected_reason' => null,
                ]);

                TransactionStatusHistory::create([
                    'transaction_id' => $fresh->transaction_id,
                    'user_id' => $admin?->id,
                    'from_status' => $fromStatus,
                    'to_status' => TransactionTaxInvoice::STATUS_ISSUED,
                    'type' => 'tax_invoice_status',
                    'note' => 'File faktur pajak diunggah.',
                ]);

                return $fresh->refresh();
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete($path);
            throw $e;
        }

        if ($oldPath && $oldPath !== $path) {
            Storage::disk(self::DISK)->delete($oldPath);
        }

        return $updated;
    }

    public function sendAvailableEmail(TransactionTaxInvoice $taxInvoice, ?User $admin): TransactionTaxInvoice
    {
        $taxInvoice->loadMissing('transaction.user');

        if (! $taxInvoice->tax_invoice_file_path || ! Storage::disk(self::DISK)->exists($taxInvoice->tax_invoice_file_path)) {
            throw new RuntimeException('File faktur pajak belum tersedia.');
        }

        $recipients = collect([
            $taxInvoice->transaction->user?->email,
            $taxInvoice->taxpayer_email,
        ])
            ->filter()
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            throw new RuntimeException('Email penerima faktur pajak tidak tersedia.');
        }

        try {
            Mail::to($recipients->all())->send(new TaxInvoiceAvailable($taxInvoice));
        } catch (Throwable $e) {
            $this->recordEmailFailure($taxInvoice, $admin, $e);

            throw new RuntimeException('Email faktur pajak gagal dikirim. File tetap tersedia untuk download.');
        }

        return DB::transaction(function () use ($taxInvoice, $admin) {
            $fresh = TransactionTaxInvoice::query()
                ->with('transaction')
                ->lockForUpdate()
                ->findOrFail($taxInvoice->id);

            $fromStatus = (string) $fresh->status;

            $fresh->update([
                'status' => TransactionTaxInvoice::STATUS_SENT,
                'sent_at' => now(),
                'email_failed_at' => null,
                'email_failure_reason' => null,
            ]);

            TransactionStatusHistory::create([
                'transaction_id' => $fresh->transaction_id,
                'user_id' => $admin?->id,
                'from_status' => $fromStatus,
                'to_status' => TransactionTaxInvoice::STATUS_SENT,
                'type' => 'tax_invoice_status',
                'note' => 'Email faktur pajak dikirim ke customer.',
            ]);

            return $fresh->refresh();
        });
    }

    public function recordDownload(TransactionTaxInvoice $taxInvoice, ?User $actor, string $actorType): void
    {
        DB::transaction(function () use ($taxInvoice, $actor, $actorType) {
            $fresh = TransactionTaxInvoice::query()
                ->lockForUpdate()
                ->findOrFail($taxInvoice->id);

            $fresh->update([
                'last_downloaded_at' => now(),
            ]);

            TransactionStatusHistory::create([
                'transaction_id' => $fresh->transaction_id,
                'user_id' => $actor?->id,
                'from_status' => $fresh->status,
                'to_status' => $fresh->status,
                'type' => 'tax_invoice_download',
                'note' => $actorType === 'admin'
                    ? 'File faktur pajak diunduh oleh admin.'
                    : 'File faktur pajak diunduh oleh customer.',
            ]);
        });
    }

    private function recordEmailFailure(TransactionTaxInvoice $taxInvoice, ?User $admin, Throwable $e): void
    {
        DB::transaction(function () use ($taxInvoice, $admin, $e) {
            $fresh = TransactionTaxInvoice::query()
                ->lockForUpdate()
                ->findOrFail($taxInvoice->id);

            $fresh->update([
                'email_failed_at' => now(),
                'email_failure_reason' => $this->safeFailureReason($e),
            ]);

            TransactionStatusHistory::create([
                'transaction_id' => $fresh->transaction_id,
                'user_id' => $admin?->id,
                'from_status' => $fresh->status,
                'to_status' => $fresh->status,
                'type' => 'tax_invoice_email',
                'note' => 'Email faktur pajak gagal dikirim. File tetap tersedia untuk download.',
            ]);
        });
    }

    private function safeFailureReason(Throwable $e): string
    {
        return Str::limit('Email delivery failed: '.$e::class, 180, '');
    }
}
