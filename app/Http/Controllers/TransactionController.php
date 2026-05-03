<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

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

    public function process(Transaction $transaction)
    {
        if (!in_array(strtolower((string) $transaction->status), ['paid', 'settlement', 'capture'], true)) {
            return response()->json(['message' => 'Transaksi belum bisa diproses.'], 422);
        }

        $transaction->status = 'process';
        $transaction->processed_at = now();
        $transaction->save();

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

        return response()->json(['ok' => true, 'message' => 'Pesanan dikirim.']);
    }
}
