<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionStatusHistory;
use App\Models\UserNotification;
use App\Services\LoyaltyPointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function complete(Request $request, Transaction $transaction, LoyaltyPointService $loyaltyPointService)
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);

        if (!in_array(strtolower((string) $transaction->status), ['kirim', 'shipping', 'shipped'], true)) {
            return back()->withErrors(['order' => 'Pesanan belum bisa ditandai selesai.']);
        }

        $earnedPoints = DB::transaction(function () use ($request, $transaction, $loyaltyPointService) {
            $freshTransaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $oldStatus = (string) $freshTransaction->status;

            if (!in_array(strtolower($oldStatus), ['kirim', 'shipping', 'shipped'], true)) {
                abort(422, 'Pesanan belum bisa ditandai selesai.');
            }

            $freshTransaction->status = 'selesai';
            $freshTransaction->save();

            TransactionStatusHistory::create([
                'transaction_id' => $freshTransaction->id,
                'user_id' => $request->user()->id,
                'from_status' => $oldStatus,
                'to_status' => 'selesai',
                'type' => 'order_completed',
                'note' => 'Customer menandai pesanan sudah diterima.',
            ]);

            $earnedPoints = $loyaltyPointService->awardCompletedTransactionPoints($freshTransaction);
            $transaction->status = $freshTransaction->status;

            return $earnedPoints;
        });

        UserNotification::create([
            'user_id' => $request->user()->id,
            'type' => 'order_completed',
            'title' => 'Pesanan Selesai',
            'body' => 'Pesanan ' . $transaction->invoice_no . ' telah ditandai diterima. Terima kasih sudah berbelanja.',
            'url' => route('frontend.profil') . '?tab=pesanan',
        ]);

        $message = 'Pesanan berhasil ditandai diterima.';
        if ($earnedPoints > 0) {
            $message .= ' Anda mendapat ' . number_format($earnedPoints, 0, ',', '.') . ' point.';
        }

        return redirect()->route('frontend.profil', ['tab' => 'pesanan'])->with('success', $message);
    }
}
