<?php

namespace App\Providers;

use App\Models\Transaction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('partials.topbar', function ($view) {
            try {
                $adminNotifications = Transaction::query()
                    ->with('user')
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function ($tx) {
                        $status = strtolower((string) $tx->status);
                        $isNew = in_array($status, ['menunggu', 'pending'], true);
                        $isPaid = in_array($status, ['paid', 'settlement', 'capture'], true);

                        if ($isPaid) {
                            $icon = 'paid'; $color = 'emerald';
                            $title = 'Pembayaran diterima';
                            $body = 'Order ' . $tx->invoice_no . ' dari ' . ($tx->user?->name ?? 'Guest') . ' sudah dibayar.';
                            $time = $tx->updated_at;
                        } elseif ($isNew) {
                            $icon = 'new'; $color = 'blue';
                            $title = 'Pesanan baru masuk';
                            $body = 'Order ' . $tx->invoice_no . ' dari ' . ($tx->user?->name ?? 'Guest') . '.';
                            $time = $tx->created_at;
                        } else {
                            $icon = 'info'; $color = 'slate';
                            $title = 'Status: ' . $tx->status;
                            $body = 'Order ' . $tx->invoice_no . ' — ' . ($tx->user?->name ?? 'Guest') . '.';
                            $time = $tx->updated_at;
                        }

                        return compact('icon', 'color', 'title', 'body', 'time') + ['url' => route('transactions.index')];
                    });
            } catch (\Throwable) {
                $adminNotifications = collect();
            }

            $view->with('adminNotifications', $adminNotifications);
        });
    }
}
