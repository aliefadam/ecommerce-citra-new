<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\CommerceReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredCommerceReservations extends Command
{
    private const TERMINAL_STATUSES = ['settlement', 'capture', 'paid', 'process', 'kirim', 'selesai', 'completed', 'dibatalkan'];

    protected $signature = 'commerce:release-expired-reservations';

    protected $description = 'Release stock, flash-sale, and coupon reservations for unpaid expired orders';

    public function handle(CommerceReservationService $reservations): int
    {
        $released = 0;

        Transaction::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereNotIn('status', self::TERMINAL_STATUSES)
            ->orderBy('id')
            ->chunkById(100, function ($transactions) use ($reservations, &$released): void {
                foreach ($transactions as $transaction) {
                    DB::transaction(function () use ($transaction, $reservations, &$released): void {
                        $locked = Transaction::query()->lockForUpdate()->find($transaction->id);
                        if (! $locked
                            || ! $locked->expires_at?->isPast()
                            || in_array(strtolower((string) $locked->status), self::TERMINAL_STATUSES, true)
                            || strtolower((string) $locked->payment_status) === 'paid') {
                            return;
                        }

                        $previous = (string) $locked->status;
                        $locked->update([
                            'status' => 'dibatalkan',
                            'payment_status' => 'cancelled',
                            'cancelled_at' => now(),
                            'cancel_reason' => 'Transaksi kedaluwarsa sebelum pembayaran.',
                        ]);
                        $reservations->release($locked);
                        $locked->statusHistories()->create([
                            'user_id' => null,
                            'from_status' => $previous,
                            'to_status' => 'dibatalkan',
                            'type' => 'reservation_expired',
                            'note' => 'Reservasi dilepas otomatis karena transaksi kedaluwarsa.',
                        ]);
                        $released++;
                    });
                }
            });

        $this->info("{$released} transaksi kedaluwarsa dilepas.");

        return self::SUCCESS;
    }
}
