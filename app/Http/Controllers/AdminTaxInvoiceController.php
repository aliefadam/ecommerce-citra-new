<?php

namespace App\Http\Controllers;

use App\Models\TransactionStatusHistory;
use App\Models\TransactionTaxInvoice;
use App\Services\TaxInvoiceDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminTaxInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:30'],
            'transaction_date' => ['nullable', 'date'],
            'request_date' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $taxInvoices = TransactionTaxInvoice::query()
            ->with(['transaction.user', 'requestedByUser'])
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['request_date']), fn ($query) => $query->whereDate('requested_at', $filters['request_date']))
            ->when(! empty($filters['transaction_date']), function ($query) use ($filters) {
                $query->whereHas('transaction', fn ($txQuery) => $txQuery->whereDate('created_at', $filters['transaction_date']));
            })
            ->when(! empty($filters['q']), function ($query) use ($filters) {
                $keyword = strtolower(trim((string) $filters['q']));
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery
                        ->whereRaw('LOWER(taxpayer_name) LIKE ?', ['%'.$keyword.'%'])
                        ->orWhereRaw('LOWER(taxpayer_number) LIKE ?', ['%'.$keyword.'%'])
                        ->orWhereRaw('LOWER(taxpayer_email) LIKE ?', ['%'.$keyword.'%'])
                        ->orWhereHas('transaction', function ($txQuery) use ($keyword) {
                            $txQuery
                                ->whereRaw('LOWER(invoice_no) LIKE ?', ['%'.$keyword.'%'])
                                ->orWhereRaw('LOWER(order_id) LIKE ?', ['%'.$keyword.'%'])
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(name) LIKE ?', ['%'.$keyword.'%'])
                                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.$keyword.'%']));
                        });
                });
            })
            ->latest('requested_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'requested' => TransactionTaxInvoice::query()->where('status', TransactionTaxInvoice::STATUS_REQUESTED)->count(),
            'processing' => TransactionTaxInvoice::query()->where('status', TransactionTaxInvoice::STATUS_PROCESSING)->count(),
            'issued' => TransactionTaxInvoice::query()->whereIn('status', [TransactionTaxInvoice::STATUS_ISSUED, TransactionTaxInvoice::STATUS_SENT])->count(),
            'rejected' => TransactionTaxInvoice::query()->where('status', TransactionTaxInvoice::STATUS_REJECTED)->count(),
        ];

        return view('backend.tax-invoices.index', [
            'taxInvoices' => $taxInvoices,
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    public function show(Request $request, TransactionTaxInvoice $taxInvoice)
    {
        $taxInvoice->load([
            'transaction.user',
            'transaction.details',
            'transaction.statusHistories.user',
            'requestedByUser',
            'uploadedByAdmin',
        ]);

        $statusHistories = $taxInvoice->transaction->statusHistories
            ->filter(fn ($history) => str_starts_with((string) $history->type, 'tax_invoice_'))
            ->sortByDesc('created_at')
            ->values();

        return view('backend.tax-invoices.show', [
            'taxInvoice' => $taxInvoice,
            'transaction' => $taxInvoice->transaction,
            'statusHistories' => $statusHistories,
            'canViewSensitive' => $request->user()?->hasAdminPermission('tax_invoices.view_sensitive') ?? false,
        ]);
    }

    public function process(Request $request, TransactionTaxInvoice $taxInvoice)
    {
        $this->transitionStatus(
            $taxInvoice,
            TransactionTaxInvoice::STATUS_PROCESSING,
            $request->user()?->id,
            'Permintaan faktur pajak ditandai sedang diproses.'
        );

        return back()->with('success', 'Permintaan faktur pajak ditandai sedang diproses.');
    }

    public function reject(Request $request, TransactionTaxInvoice $taxInvoice)
    {
        $validated = $request->validate([
            'rejected_reason' => ['required', 'string', 'max:1000'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->transitionStatus(
            $taxInvoice,
            TransactionTaxInvoice::STATUS_REJECTED,
            $request->user()?->id,
            $validated['rejected_reason'],
            [
                'rejected_reason' => $validated['rejected_reason'],
                'admin_note' => $validated['admin_note'] ?? $taxInvoice->admin_note,
                'rejected_at' => now(),
            ]
        );

        return back()->with('success', 'Permintaan faktur pajak ditolak.');
    }

    public function upload(Request $request, TransactionTaxInvoice $taxInvoice, TaxInvoiceDeliveryService $deliveryService)
    {
        $validated = $request->validate([
            'tax_invoice_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'tax_invoice_number' => ['nullable', 'string', 'max:100'],
            'tax_invoice_date' => ['nullable', 'date'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $taxInvoice = $deliveryService->upload(
            $taxInvoice,
            $request->file('tax_invoice_file'),
            $request->user(),
            $validated
        );

        if ($request->boolean('send_email')) {
            try {
                $deliveryService->sendAvailableEmail($taxInvoice, $request->user());
            } catch (\Throwable $e) {
                return back()->with('warning', 'File faktur pajak berhasil diunggah, tetapi email belum berhasil dikirim. File tetap tersedia untuk download.');
            }
        }

        return back()->with('success', 'File faktur pajak berhasil diunggah.');
    }

    public function send(Request $request, TransactionTaxInvoice $taxInvoice, TaxInvoiceDeliveryService $deliveryService)
    {
        try {
            $deliveryService->sendAvailableEmail($taxInvoice, $request->user());
        } catch (\Throwable $e) {
            return back()->withErrors(['tax_invoice_email' => 'Email faktur pajak gagal dikirim. File tetap tersedia untuk download.']);
        }

        return back()->with('success', 'Email faktur pajak berhasil dikirim.');
    }

    public function download(Request $request, TransactionTaxInvoice $taxInvoice, TaxInvoiceDeliveryService $deliveryService)
    {
        abort_unless($taxInvoice->tax_invoice_file_path, 404);
        abort_unless(Storage::disk(TaxInvoiceDeliveryService::DISK)->exists($taxInvoice->tax_invoice_file_path), 404);

        $deliveryService->recordDownload($taxInvoice, $request->user(), 'admin');

        return Storage::disk(TaxInvoiceDeliveryService::DISK)->download(
            $taxInvoice->tax_invoice_file_path,
            $this->downloadFileName($taxInvoice)
        );
    }

    private function transitionStatus(TransactionTaxInvoice $taxInvoice, string $toStatus, ?int $adminId, string $note, array $extra = []): void
    {
        DB::transaction(function () use ($taxInvoice, $toStatus, $adminId, $note, $extra) {
            $freshTaxInvoice = TransactionTaxInvoice::query()
                ->with('transaction')
                ->lockForUpdate()
                ->findOrFail($taxInvoice->id);

            $fromStatus = (string) $freshTaxInvoice->status;
            $updates = array_merge([
                'status' => $toStatus,
            ], $extra);

            if ($toStatus === TransactionTaxInvoice::STATUS_PROCESSING) {
                $updates['processing_at'] = $freshTaxInvoice->processing_at ?: now();
            }

            $freshTaxInvoice->update($updates);

            TransactionStatusHistory::create([
                'transaction_id' => $freshTaxInvoice->transaction_id,
                'user_id' => $adminId,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'type' => 'tax_invoice_status',
                'note' => $note,
            ]);
        });
    }

    private function downloadFileName(TransactionTaxInvoice $taxInvoice): string
    {
        $invoiceNo = preg_replace('/[^A-Za-z0-9\-]+/', '-', (string) $taxInvoice->transaction?->invoice_no) ?: $taxInvoice->id;

        return 'faktur-pajak-'.$invoiceNo.'.pdf';
    }
}
