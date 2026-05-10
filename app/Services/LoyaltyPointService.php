<?php

namespace App\Services;

use App\Models\PointHistory;
use App\Models\TransactionDetail;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyPointService
{
    public const EARN_RATE_AMOUNT = 100000;
    private const REDEEM_NOTE_PREFIX = '[redeem_points_per_item:';

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

    public function calculateRedeemPoints(Transaction $transaction): int
    {
        $transaction->loadMissing('details');

        return (int) $transaction->details->sum(function (TransactionDetail $detail) {
            $perItem = $this->extractRedeemPointsPerItem((string) ($detail->item_note ?? ''));
            if ($perItem < 1) {
                return 0;
            }

            return $perItem * max(1, (int) ($detail->quantity ?? 1));
        });
    }

    public function reserveRedeemPoints(Transaction $transaction): int
    {
        if (!$transaction->user_id) {
            return 0;
        }

        $points = $this->calculateRedeemPoints($transaction);
        if ($points <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($transaction, $points) {
            $freshTransaction = Transaction::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if ($freshTransaction->redeem_points_reserved_at || $freshTransaction->redeem_points_finalized_at || $freshTransaction->redeem_points_released_at) {
                return 0;
            }

            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($freshTransaction->user_id);

            $before = (int) $user->point_balance;
            $after = $before - $points;
            if ($after < 0) {
                throw new \RuntimeException('Point customer tidak cukup untuk reserve redeem ini.');
            }

            $user->forceFill([
                'point_balance' => $after,
            ])->save();

            PointHistory::query()->create([
                'user_id' => $user->id,
                'type' => 'redeem_reserved',
                'points' => -$points,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => 'Reserve point untuk transaksi redeem ' . $freshTransaction->invoice_no,
                'reference_type' => Transaction::class,
                'reference_id' => $freshTransaction->id,
            ]);

            $freshTransaction->forceFill([
                'redeem_points_reserved' => $points,
                'redeem_points_reserved_at' => now(),
            ])->save();

            return $points;
        });
    }

    public function finalizeRedeemReservation(Transaction $transaction): int
    {
        if (!$transaction->user_id) {
            return 0;
        }

        return DB::transaction(function () use ($transaction) {
            $freshTransaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            $points = (int) ($freshTransaction->redeem_points_reserved ?? 0);
            if ($points <= 0 || $freshTransaction->redeem_points_finalized_at || $freshTransaction->redeem_points_released_at || !$freshTransaction->redeem_points_reserved_at) {
                return 0;
            }

            PointHistory::query()
                ->where('reference_type', Transaction::class)
                ->where('reference_id', $freshTransaction->id)
                ->where('type', 'redeem_reserved')
                ->update([
                    'type' => 'redeem',
                    'description' => 'Redeem point untuk transaksi ' . $freshTransaction->invoice_no,
                    'updated_at' => now(),
                ]);

            $freshTransaction->forceFill([
                'redeem_points_finalized_at' => now(),
            ])->save();

            return $points;
        });
    }

    public function releaseRedeemReservation(Transaction $transaction): int
    {
        if (!$transaction->user_id) {
            return 0;
        }

        return DB::transaction(function () use ($transaction) {
            $freshTransaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            $points = (int) ($freshTransaction->redeem_points_reserved ?? 0);
            if ($points <= 0 || !$freshTransaction->redeem_points_reserved_at || $freshTransaction->redeem_points_finalized_at || $freshTransaction->redeem_points_released_at) {
                return 0;
            }

            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($freshTransaction->user_id);

            $before = (int) $user->point_balance;
            $after = $before + $points;

            $user->forceFill([
                'point_balance' => $after,
            ])->save();

            PointHistory::query()->create([
                'user_id' => $user->id,
                'type' => 'redeem_release',
                'points' => $points,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => 'Pengembalian reserve point untuk transaksi redeem ' . $freshTransaction->invoice_no,
                'reference_type' => Transaction::class,
                'reference_id' => $freshTransaction->id,
            ]);

            $freshTransaction->forceFill([
                'redeem_points_released_at' => now(),
            ])->save();

            return $points;
        });
    }

    public static function buildRedeemItemNote(int $redeemPointsPerItem, string $note = ''): string
    {
        $prefix = self::REDEEM_NOTE_PREFIX . max(0, $redeemPointsPerItem) . ']';
        $note = trim($note);

        return $note !== '' ? $prefix . ' ' . $note : $prefix;
    }

    private function extractRedeemPointsPerItem(string $note): int
    {
        if (preg_match('/\[redeem_points_per_item:(\d+)\]/', $note, $matches) === 1) {
            return (int) ($matches[1] ?? 0);
        }

        return 0;
    }
}
