<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionStatusHistory;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function complete(Request $request, Transaction $transaction)
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);

        if (!in_array(strtolower((string) $transaction->status), ['kirim', 'shipping', 'shipped'], true)) {
            return back()->withErrors(['order' => 'Pesanan belum bisa ditandai selesai.']);
        }

        $oldStatus = (string) $transaction->status;
        $transaction->status = 'selesai';
        $transaction->save();

        TransactionStatusHistory::create([
            'transaction_id' => $transaction->id,
            'user_id' => $request->user()->id,
            'from_status' => $oldStatus,
            'to_status' => 'selesai',
            'type' => 'order_completed',
            'note' => 'Customer menandai pesanan sudah diterima.',
        ]);

        UserNotification::create([
            'user_id' => $request->user()->id,
            'type' => 'order_completed',
            'title' => 'Pesanan Selesai',
            'body' => 'Pesanan ' . $transaction->invoice_no . ' telah ditandai diterima. Terima kasih sudah berbelanja.',
            'url' => route('frontend.profil') . '?tab=pesanan',
        ]);

        return redirect()->route('frontend.profil', ['tab' => 'pesanan'])->with('success', 'Pesanan berhasil ditandai diterima.');
    }
}
