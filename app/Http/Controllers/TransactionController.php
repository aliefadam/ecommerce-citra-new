<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::query()
            ->with(['user', 'details'])
            ->latest()
            ->get();

        $transactions->transform(function ($tx) {
            $tx->details->transform(function ($d) {
                $image = (string) ($d->image ?? '');
                $isAbsolute = str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '//') || str_starts_with($image, 'data:');
                if ($image !== '' && !$isAbsolute) {
                    $image = asset(ltrim($image, '/'));
                }
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

                $transaction->status = 'process';
                $transaction->processed_at = now();
                $transaction->save();
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
        ]);

        if (!in_array(strtolower((string) $transaction->status), ['process'], true)) {
            return response()->json(['message' => 'Transaksi belum bisa dikirim.'], 422);
        }

        $transaction->status = 'kirim';
        $transaction->tracking_number = (string) $validated['tracking_number'];
        $transaction->shipped_at = now();
        $transaction->save();

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
}
