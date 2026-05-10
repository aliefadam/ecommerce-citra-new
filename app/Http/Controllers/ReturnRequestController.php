<?php

namespace App\Http\Controllers;

use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReturnRequestController extends Controller
{
    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'type' => ['required', Rule::in(['refund', 'exchange'])],
            'reason' => ['required', 'string', 'max:1000'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $transaction = Transaction::query()
            ->with('details')
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $validated['transaction_id']);

        if (!$this->isEligible($transaction)) {
            return back()->withErrors(['return_request' => 'Pesanan ini belum bisa diajukan return/refund atau sudah melewati batas 7 hari.'])->withInput();
        }

        $requestedItems = collect($validated['items'])
            ->mapWithKeys(fn($qty, $detailId) => [(int) $detailId => (int) $qty])
            ->filter(fn($qty) => $qty > 0);

        if ($requestedItems->isEmpty()) {
            return back()->withErrors(['items' => 'Pilih minimal satu produk untuk diajukan.'])->withInput();
        }

        $details = $transaction->details->keyBy('id');
        $photos = [];

        foreach ($request->file('photos', []) as $photo) {
            $photos[] = $imageOptimizer->storeWebp($photo, 'return-requests', 1200, 1200, 82, true);
        }

        try {
            DB::transaction(function () use ($transaction, $request, $validated, $requestedItems, $details, $photos) {
                $return = ReturnRequest::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $request->user()->id,
                    'request_no' => $this->makeRequestNo(),
                    'type' => $validated['type'],
                    'status' => 'menunggu',
                    'refund_amount' => 0,
                    'reason' => $validated['reason'],
                    'customer_note' => $validated['customer_note'] ?? null,
                    'photos' => $photos,
                ]);

                $refundAmount = 0;

                foreach ($requestedItems as $detailId => $qty) {
                    $detail = $details->get($detailId);
                    if (!$detail) {
                        throw new \RuntimeException('Produk pengajuan tidak valid.');
                    }

                    $availableQty = $this->availableQuantity($detail);
                    if ($qty > $availableQty) {
                        throw new \RuntimeException('Jumlah pengajuan untuk ' . $detail->product_name . ' melebihi jumlah yang bisa diajukan.');
                    }

                    $subtotal = (int) $detail->price * $qty;
                    $refundAmount += $subtotal;

                    ReturnRequestItem::create([
                        'return_request_id' => $return->id,
                        'transaction_detail_id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'product_variant_id' => $detail->product_variant_id,
                        'product_name' => $detail->product_name,
                        'variant_name' => $detail->variant_name,
                        'quantity' => $qty,
                        'price' => (int) $detail->price,
                        'subtotal' => $subtotal,
                    ]);
                }

                $return->refund_amount = $refundAmount;
                $return->save();
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['return_request' => $e->getMessage()])->withInput();
        }

        return redirect()->route('frontend.profil', ['tab' => 'pesanan'])
            ->with('success', 'Pengajuan return/refund berhasil dikirim.');
    }

    private function isEligible(Transaction $transaction): bool
    {
        $status = strtolower((string) $transaction->status);
        if (!in_array($status, ['kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'], true)) {
            return false;
        }

        $start = $transaction->shipped_at ?? $transaction->updated_at ?? $transaction->created_at;
        return $start ? $start->copy()->addDays(7)->endOfDay()->isFuture() : false;
    }

    private function availableQuantity($detail): int
    {
        $used = ReturnRequestItem::query()
            ->where('transaction_detail_id', $detail->id)
            ->whereHas('returnRequest', fn($q) => $q->whereNotIn('status', ['ditolak']))
            ->sum('quantity');

        return max(0, (int) $detail->quantity - (int) $used);
    }

    private function makeRequestNo(): string
    {
        do {
            $number = 'RR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (ReturnRequest::query()->where('request_no', $number)->exists());

        return $number;
    }
}
