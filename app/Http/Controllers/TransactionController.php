<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StoreLocation;
use App\Models\Transaction;
use App\Models\TransactionStatusHistory;
use App\Models\UserNotification;
use App\Services\LoyaltyPointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    use ScopesToActiveCompany;

    public function index()
    {
        $transactions = Transaction::query()
            ->where('company_id', $this->activeCompanyId())
            ->with(['user', 'createdByAdmin', 'details', 'statusHistories.user'])
            ->latest()
            ->get();

        $transactions->transform(function ($tx) {
            $tx->details->transform(function ($d) {
                $image = (string) ($d->image ?? '');
                $image = $this->resolveImageUrl($image);
                $d->image_url = $image;

                return $d;
            });

            return $tx;
        });

        return view('backend.transactions.index', compact('transactions'));
    }

    public function process(Request $request, Transaction $transaction)
    {
        $this->guardCompanyOwnership($transaction->company_id);

        try {
            $transaction = DB::transaction(function () use ($transaction, $request) {
                $lockedTransaction = Transaction::query()
                    ->where('company_id', $this->activeCompanyId())
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail($transaction->id);

                if (! in_array(strtolower((string) $lockedTransaction->status), ['paid', 'settlement', 'capture'], true)) {
                    throw new \RuntimeException('Transaksi belum bisa diproses.');
                }

                $oldStatus = (string) $lockedTransaction->status;

                if ($lockedTransaction->normalizedSource() !== Transaction::SOURCE_MANUAL) {
                    foreach ($lockedTransaction->details as $detail) {
                        $variantId = (int) ($detail->product_variant_id ?? 0);
                        $qty = (int) ($detail->quantity ?? 0);
                        if ($variantId <= 0 || $qty <= 0) {
                            continue;
                        }

                        $variant = ProductVariant::query()->lockForUpdate()->find($variantId);
                        if (! $variant) {
                            continue;
                        }

                        $before = (int) $variant->stock;
                        if ($before < $qty) {
                            throw new \RuntimeException('Stok varian "'.($detail->variant_name ?: $detail->product_name).'" tidak mencukupi.');
                        }

                        $after = $before - $qty;
                        $variant->stock = $after;
                        $variant->save();

                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'transaction_detail_id' => $detail->id,
                            'admin_user_id' => $request->user()?->id,
                            'type' => 'out',
                            'quantity' => $qty,
                            'stock_before' => $before,
                            'stock_after' => $after,
                            'source' => 'sales',
                            'description' => 'Penjualan produk',
                        ]);
                    }
                }

                $lockedTransaction->status = 'process';
                $lockedTransaction->processed_at = now();
                $lockedTransaction->save();

                $this->recordHistory($lockedTransaction, $oldStatus, 'process', 'order_processed', 'Admin memproses pesanan.', $request->user()?->id);

                return $lockedTransaction;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($transaction->user_id) {
            UserNotification::create([
                'user_id' => $transaction->user_id,
                'type' => 'order_processed',
                'title' => 'Pesanan Sedang Disiapkan',
                'body' => 'Pesanan '.$transaction->invoice_no.' sedang disiapkan oleh tim kami dan akan segera dikirim.',
                'url' => route('frontend.profil').'?tab=pesanan',
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Transaksi diproses.']);
    }

    public function ship(Request $request, Transaction $transaction)
    {
        $this->guardCompanyOwnership($transaction->company_id);

        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
            'shipping_label' => ['nullable', 'string', 'max:100'],
            'shipping_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = DB::transaction(function () use ($transaction, $validated, $request): array {
                $lockedTransaction = Transaction::query()
                    ->where('company_id', $this->activeCompanyId())
                    ->lockForUpdate()
                    ->findOrFail($transaction->id);
                $currentStatus = strtolower((string) $lockedTransaction->status);

                if (! in_array($currentStatus, ['process', 'processing', 'kirim'], true)) {
                    throw new \RuntimeException('Transaksi belum bisa dikirim.');
                }

                $trackingNumber = (string) $validated['tracking_number'];
                $shippingLabel = ! empty($validated['shipping_label'])
                    ? (string) $validated['shipping_label']
                    : (string) $lockedTransaction->shipping_label;
                $shippingNote = $validated['shipping_note'] ?? $lockedTransaction->shipping_note;

                if (
                    $currentStatus === 'kirim'
                    && (string) $lockedTransaction->tracking_number === $trackingNumber
                    && (string) $lockedTransaction->shipping_label === $shippingLabel
                    && (string) ($lockedTransaction->shipping_note ?? '') === (string) ($shippingNote ?? '')
                ) {
                    return ['transaction' => $lockedTransaction, 'duplicate' => true];
                }

                $oldStatus = (string) $lockedTransaction->status;
                $lockedTransaction->status = 'kirim';
                $lockedTransaction->tracking_number = $trackingNumber;
                $lockedTransaction->shipping_label = $shippingLabel;
                $lockedTransaction->shipping_note = $shippingNote;
                $lockedTransaction->shipped_at = $lockedTransaction->shipped_at ?: now();
                $lockedTransaction->save();

                $this->recordHistory($lockedTransaction, $oldStatus, 'kirim', 'order_shipped', 'Resi: '.$trackingNumber, $request->user()?->id);

                return ['transaction' => $lockedTransaction, 'duplicate' => false];
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        /** @var Transaction $transaction */
        $transaction = $result['transaction'];

        if ($result['duplicate']) {
            return response()->json(['ok' => true, 'message' => 'Pesanan sudah dikirim dengan data resi yang sama.']);
        }

        if ($transaction->user_id) {
            UserNotification::create([
                'user_id' => $transaction->user_id,
                'type' => 'order_shipped',
                'title' => 'Pesanan Dalam Perjalanan',
                'body' => 'Pesanan '.$transaction->invoice_no.' sudah dikirim via '.($transaction->shipping_label ?: 'kurir').'. No. Resi: '.$validated['tracking_number'],
                'url' => route('frontend.profil').'?tab=pesanan',
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Pesanan dikirim.']);
    }

    public function verifyPayment(Request $request, Transaction $transaction, LoyaltyPointService $loyaltyPointService)
    {
        $this->guardCompanyOwnership($transaction->company_id);

        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'payment_admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = DB::transaction(function () use ($transaction, $validated, $request, $loyaltyPointService): array {
                $lockedTransaction = Transaction::query()
                    ->where('company_id', $this->activeCompanyId())
                    ->lockForUpdate()
                    ->findOrFail($transaction->id);

                if ($lockedTransaction->payment_type !== 'manual_transfer') {
                    throw new \RuntimeException('Transaksi ini bukan pembayaran manual.');
                }

                $oldStatus = (string) $lockedTransaction->status;
                if (
                    $validated['action'] === 'approve'
                    && strtolower($oldStatus) === 'paid'
                    && $lockedTransaction->payment_verified_at
                ) {
                    return [
                        'transaction' => $lockedTransaction,
                        'duplicate' => true,
                        'message' => 'Pembayaran manual sudah disetujui sebelumnya.',
                    ];
                }
                if (
                    $validated['action'] === 'reject'
                    && strtolower($oldStatus) === 'menunggu_verifikasi'
                    && $lockedTransaction->payment_rejected_at
                ) {
                    return [
                        'transaction' => $lockedTransaction,
                        'duplicate' => true,
                        'message' => 'Bukti pembayaran sudah ditolak sebelumnya.',
                    ];
                }

                if ($validated['action'] === 'approve') {
                    $lockedTransaction->status = 'paid';
                    $lockedTransaction->payment_status = 'paid';
                    $lockedTransaction->paid_at = $lockedTransaction->paid_at ?: now();
                    $lockedTransaction->payment_paid_at = $lockedTransaction->payment_paid_at ?: $lockedTransaction->paid_at;
                    $lockedTransaction->payment_amount = (int) $lockedTransaction->grand_total;
                    $lockedTransaction->payment_verified_at = now();
                    $lockedTransaction->payment_rejected_at = null;
                    $lockedTransaction->payment_admin_note = $validated['payment_admin_note'] ?? null;
                    $message = 'Pembayaran manual disetujui.';
                } else {
                    $lockedTransaction->status = 'menunggu_verifikasi';
                    $lockedTransaction->payment_rejected_at = now();
                    $lockedTransaction->payment_admin_note = $validated['payment_admin_note'] ?? 'Bukti transfer ditolak.';
                    $message = 'Bukti pembayaran ditolak.';
                }
                $lockedTransaction->save();

                if ($validated['action'] === 'approve') {
                    $loyaltyPointService->finalizeRedeemReservation($lockedTransaction);
                }

                $this->recordHistory($lockedTransaction, $oldStatus, (string) $lockedTransaction->status, 'payment_verification', $message, $request->user()?->id);

                return ['transaction' => $lockedTransaction, 'duplicate' => false, 'message' => $message];
            });
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        /** @var Transaction $transaction */
        $transaction = $result['transaction'];
        $message = $result['message'];

        if (! $result['duplicate'] && $transaction->user_id) {
            UserNotification::create([
                'user_id' => $transaction->user_id,
                'type' => $validated['action'] === 'approve' ? 'payment_received' : 'payment_rejected',
                'title' => $validated['action'] === 'approve' ? 'Pembayaran Dikonfirmasi' : 'Bukti Transfer Ditolak',
                'body' => $message.' Pesanan '.$transaction->invoice_no.'.',
                'url' => route('frontend.profil').'?tab=pesanan',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function show(Transaction $transaction)
    {
        $this->guardCompanyOwnership($transaction->company_id);

        $transaction->load(['user', 'createdByAdmin', 'details', 'statusHistories.user', 'returnRequests.items']);

        return view('backend.transactions.show', compact('transaction'));
    }

    public function shippingLabel(Transaction $transaction)
    {
        $this->guardCompanyOwnership($transaction->company_id);

        $transaction->load(['user', 'details.productVariant']);
        $storeLocation = StoreLocation::query()
            ->where('company_id', $transaction->company_id)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        return view('invoices.shipping-label', compact('transaction', 'storeLocation'));
    }

    public function bulkShippingLabels(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $transactions = Transaction::query()
            ->where('company_id', $this->activeCompanyId())
            ->with(['user', 'details.productVariant'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Transaction $transaction) => $ids->search((int) $transaction->id))
            ->values();

        $invalidTransactions = $transactions
            ->map(function (Transaction $transaction) {
                return [
                    'transaction' => $transaction,
                    'issues' => $this->shippingLabelIssues($transaction),
                ];
            })
            ->filter(fn (array $item) => count($item['issues']) > 0)
            ->values();

        $validTransactions = $transactions
            ->reject(fn (Transaction $transaction) => count($this->shippingLabelIssues($transaction)) > 0)
            ->values();

        $storeLocationsByCompany = StoreLocation::query()
            ->where('is_active', true)
            ->whereIn('company_id', $transactions->pluck('company_id')->unique())
            ->get()
            ->keyBy('company_id');

        return view('invoices.shipping-label-bulk', compact('transactions', 'validTransactions', 'invalidTransactions', 'storeLocationsByCompany'));
    }

    private function recordHistory(Transaction $transaction, ?string $from, string $to, string $type, ?string $note = null, ?int $userId = null): void
    {
        TransactionStatusHistory::create([
            'transaction_id' => $transaction->id,
            'user_id' => $userId,
            'from_status' => $from,
            'to_status' => $to,
            'type' => $type,
            'note' => $note,
        ]);
    }

    private function resolveImageUrl(string $image): string
    {
        $image = trim($image);
        if ($image === '') {
            return '';
        }

        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://') ||
            str_starts_with($image, '//') ||
            str_starts_with($image, 'data:')
        ) {
            return $image;
        }

        $normalized = str_starts_with($image, 'storage/')
            ? substr($image, strlen('storage/'))
            : ltrim($image, '/');

        return asset('storage/'.$normalized);
    }

    private function shippingLabelIssues(Transaction $transaction): array
    {
        $issues = [];
        if (
            trim((string) $transaction->shipping_recipient_name) === '' ||
            trim((string) $transaction->shipping_phone) === '' ||
            trim((string) $transaction->shipping_address_line) === ''
        ) {
            $issues[] = 'Alamat pengiriman belum lengkap.';
        }

        if (
            in_array(strtolower((string) $transaction->status), ['kirim', 'shipping', 'shipped'], true) &&
            trim((string) $transaction->tracking_number) === ''
        ) {
            $issues[] = 'Nomor resi belum ada.';
        }

        return $issues;
    }
}
