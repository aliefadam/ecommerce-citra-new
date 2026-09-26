<?php

namespace App\Providers;

use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\StorefrontNavigationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Rate limit untuk Open Catalog API: per-IP, longgar (anti scraping/DoS),
        // bukan untuk autentikasi. Lihat docs/prd-company-catalog-api.md §1.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('payment-proof', fn (Request $request) => Limit::perMinute(5)->by(($request->user()?->id ?? $request->ip()).'|'.$request->route('transaction')));
        RateLimiter::for('storefront-search', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        $storeSettings = StoreSetting::defaults();
        try {
            if (Schema::hasTable('store_settings')) {
                $storeSettings = StoreSetting::values();
            }
        } catch (\Throwable) {
            $storeSettings = StoreSetting::defaults();
        }

        $storeLogoPath = trim((string) ($storeSettings['store_logo_path'] ?? ''));
        View::share('appStoreSettings', $storeSettings);
        View::share('appStoreName', (string) config('app.name'));
        View::share('appStoreLogoUrl', $storeLogoPath !== '' ? asset('storage/'.ltrim($storeLogoPath, '/')) : null);

        View::composer(['layouts.user', 'partials.navbar-user'], function ($view): void {
            $user = auth()->user();
            $view->with('customerNavigation', [
                'megaCategories' => app(StorefrontNavigationService::class)->megaCategories(),
                'cartCount' => $user ? (int) $user->carts()->sum('quantity') : 0,
            ]);
        });

        $forgetStorefrontNavigation = fn () => app(StorefrontNavigationService::class)->forget();
        MainCategory::saved($forgetStorefrontNavigation);
        MainCategory::deleted($forgetStorefrontNavigation);
        CategoryDetail::saved($forgetStorefrontNavigation);
        CategoryDetail::deleted($forgetStorefrontNavigation);

        View::composer('partials.topbar', function ($view) {
            try {
                $adminNotifications = Transaction::query()
                    ->where('company_id', User::activeCompanyId() ?? 0)
                    ->with('user')
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function ($tx) {
                        $status = strtolower((string) $tx->status);
                        $isNew = in_array($status, ['menunggu', 'pending'], true);
                        $isPaid = in_array($status, ['paid', 'settlement', 'capture'], true);

                        if ($isPaid) {
                            $icon = 'paid';
                            $color = 'emerald';
                            $title = 'Pembayaran diterima';
                            $body = 'Order '.$tx->invoice_no.' dari '.($tx->user?->name ?? 'Guest').' sudah dibayar.';
                            $time = $tx->updated_at;
                        } elseif ($isNew) {
                            $icon = 'new';
                            $color = 'blue';
                            $title = 'Pesanan baru masuk';
                            $body = 'Order '.$tx->invoice_no.' dari '.($tx->user?->name ?? 'Guest').'.';
                            $time = $tx->created_at;
                        } else {
                            $icon = 'info';
                            $color = 'slate';
                            $title = 'Status: '.$tx->status;
                            $body = 'Order '.$tx->invoice_no.' — '.($tx->user?->name ?? 'Guest').'.';
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
