<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $transactions = Transaction::query()
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
        if (!in_array(strtolower((string) $transaction->status), ['paid', 'settlement', 'capture'], true)) {
            return response()->json(['message' => 'Transaksi belum bisa diproses.'], 422);
        }

        try {
            DB::transaction(function () use ($transaction, $request) {
                $transaction->loadMissing('details');
                $oldStatus = (string) $transaction->status;

                if ($transaction->normalizedSource() !== Transaction::SOURCE_MANUAL) {
                    foreach ($transaction->details as $detail) {
                        $variantId = (int) ($detail->product_variant_id ?? 0);
                        $qty = (int) ($detail->quantity ?? 0);
                        if ($variantId <= 0 || $qty <= 0) {
                            continue;
                        }

                        $variant = ProductVariant::query()->lockForUpdate()->find($variantId);
                        if (!$variant) {
                            continue;
                        }

                        $before = (int) $variant->stock;
                        if ($before < $qty) {
                            throw new \RuntimeException('Stok varian "' . ($detail->variant_name ?: $detail->product_name) . '" tidak mencukupi.');
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

                $transaction->status = 'process';
                $transaction->processed_at = now();
                $transaction->save();

                $this->recordHistory($transaction, $oldStatus, 'process', 'order_processed', 'Admin memproses pesanan.', $request->user()?->id);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($transaction->user_id) {
            UserNotification::create([
                'user_id' => $transaction->user_id,
                'type'    => 'order_processed',
                'title'   => 'Pesanan Sedang Disiapkan',
                'body'    => 'Pesanan ' . $transaction->invoice_no . ' sedang disiapkan oleh tim kami dan akan segera dikirim.',
                'url'     => route('frontend.profil') . '?tab=pesanan',
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Transaksi diproses.']);
    }

    public function ship(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
            'shipping_label' => ['nullable', 'string', 'max:100'],
            'shipping_note' => ['nullable', 'string', 'max:500'],
        ]);

        if (!in_array(strtolower((string) $transaction->status), ['process', 'processing', 'kirim'], true)) {
            return response()->json(['message' => 'Transaksi belum bisa dikirim.'], 422);
        }

        $oldStatus = (string) $transaction->status;
        $transaction->status = 'kirim';
        $transaction->tracking_number = (string) $validated['tracking_number'];
        if (!empty($validated['shipping_label'])) {
            $transaction->shipping_label = (string) $validated['shipping_label'];
        }
        $transaction->shipping_note = $validated['shipping_note'] ?? $transaction->shipping_note;
        $transaction->shipped_at = $transaction->shipped_at ?: now();
        $transaction->save();

        $this->recordHistory($transaction, $oldStatus, 'kirim', 'order_shipped', 'Resi: ' . $validated['tracking_number'], $request->user()?->id);

        if ($transaction->user_id) {
            UserNotification::create([
                'user_id' => $transaction->user_id,
                'type'    => 'order_shipped',
                'title'   => 'Pesanan Dalam Perjalanan',
                'body'    => 'Pesanan ' . $transaction->invoice_no . ' sudah dikirim via ' . ($transaction->shipping_label ?: 'kurir') . '. No. Resi: ' . $validated['tracking_number'],
                'url'     => route('frontend.profil') . '?tab=pesanan',
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Pesanan dikirim.']);
    }

    public function verifyPayment(Request $request, Transaction $transaction, LoyaltyPointService $loyaltyPointService)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'payment_admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($transaction->payment_type !== 'manual_transfer') {
            return back()->withErrors(['payment' => 'Transaksi ini bukan pembayaran manual.']);
        }

        $oldStatus = (string) $transaction->status;
        if ($validated['action'] === 'approve') {
            $transaction->status = 'paid';
            $transaction->paid_at = $transaction->paid_at ?: now();
            $transaction->payment_verified_at = now();
            $transaction->payment_rejected_at = null;
            $transaction->payment_admin_note = $validated['payment_admin_note'] ?? null;
            $message = 'Pembayaran manual disetujui.';
        } else {
            $transaction->status = 'menunggu_verifikasi';
            $transaction->payment_rejected_at = now();
            $transaction->payment_admin_note = $validated['payment_admin_note'] ?? 'Bukti transfer ditolak.';
            $message = 'Bukti pembayaran ditolak.';
        }
        $transaction->save();

        if ($validated['action'] === 'approve') {
            $loyaltyPointService->finalizeRedeemReservation($transaction);
        }

        $this->recordHistory($transaction, $oldStatus, (string) $transaction->status, 'payment_verification', $message, $request->user()?->id);

        if ($transaction->user_id) {
            UserNotification::create([
                'user_id' => $transaction->user_id,
                'type' => $validated['action'] === 'approve' ? 'payment_received' : 'payment_rejected',
                'title' => $validated['action'] === 'approve' ? 'Pembayaran Dikonfirmasi' : 'Bukti Transfer Ditolak',
                'body' => $message . ' Pesanan ' . $transaction->invoice_no . '.',
                'url' => route('frontend.profil') . '?tab=pesanan',
            ]);
        }

        return back()->with('success', $message);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'createdByAdmin', 'details', 'statusHistories.user', 'returnRequests.items']);

        return view('backend.transactions.show', compact('transaction'));
    }

    public function shippingLabel(Transaction $transaction)
    {
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

        $storeLocation = StoreLocation::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        return view('invoices.shipping-label-bulk', compact('transactions', 'validTransactions', 'invalidTransactions', 'storeLocation'));
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

        return asset('storage/' . $normalized);
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
