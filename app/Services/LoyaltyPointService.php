<?php

namespace App\Services;

use App\Models\PointHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyPointService
{
    public const EARN_RATE_AMOUNT = 100000;

    public function calculateEarnedPoints(int $amount): int
    {
        return $amount > 0 ? intdiv($amount, self::EARN_RATE_AMOUNT) : 0;
    }

    public function awardCompletedTransactionPoints(Transaction $transaction): int
    {
        if (!$transaction->user_id || $transaction->points_awarded_at) {
            return 0;
        }

        $points = $this->calculateEarnedPoints((int) $transaction->grand_total);
        if ($points <= 0) {
            $transaction->forceFill([
                'points_awarded_at' => now(),
            ])->save();

            return 0;
        }

        return DB::transaction(function () use ($transaction, $points) {
            $freshTransaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if ($freshTransaction->points_awarded_at) {
                return 0;
            }

            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($freshTransaction->user_id);

            $before = (int) $user->point_balance;
            $after = $before + $points;

            $user->forceFill([
                'point_balance' => $after,
                'lifetime_points' => (int) $user->lifetime_points + $points,
            ])->save();

            PointHistory::query()->create([
                'user_id' => $user->id,
                'type' => 'earn',
                'points' => $points,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => 'Point dari transaksi selesai ' . $freshTransaction->invoice_no,
                'reference_type' => Transaction::class,
                'reference_id' => $freshTransaction->id,
            ]);

            $freshTransaction->forceFill([
                'points_awarded_at' => now(),
            ])->save();

            $transaction->points_awarded_at = $freshTransaction->points_awarded_at;

            return $points;
        });
    }
}
