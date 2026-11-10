<?php

use App\Http\Controllers\Api\Mobile\V1\MobileAccountController;
use App\Http\Controllers\Api\Mobile\V1\MobileCatalogController;
use App\Http\Controllers\Api\Mobile\V1\MobileCheckoutController;
use App\Http\Controllers\Api\Mobile\V1\MobileCommerceController;
use App\Http\Controllers\Api\Mobile\V1\MobileSessionController;
use App\Http\Controllers\Api\Mobile\V1\MobileTelemetryController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\MidtransController;
use Illuminate\Support\Facades\Route;

Route::post('midtrans/notification', [MidtransController::class, 'notification'])
    ->middleware('throttle:120,1')
    ->name('midtrans.notification');

Route::prefix('mobile/v1')
    ->name('api.mobile.v1.')
    ->middleware('mobile.spa')
    ->group(function () {
        Route::get('bootstrap', [MobileSessionController::class, 'bootstrap'])->name('bootstrap');
        Route::middleware(['throttle:api', 'cache.headers:public;max_age=60;etag'])->group(function () {
            Route::get('home', [MobileCatalogController::class, 'home'])->name('home');
            Route::get('categories', [MobileCatalogController::class, 'categories'])->name('categories.index');
            Route::get('products', [MobileCatalogController::class, 'products'])->name('products.index');
            Route::get('products/{slug}', [MobileCatalogController::class, 'product'])->name('products.show');
            Route::get('search/suggestions', [MobileCatalogController::class, 'suggestions'])->name('search.suggestions');
        });
        Route::post('auth/login', [MobileSessionController::class, 'login'])
            ->middleware('throttle:login')
            ->name('auth.login');
        Route::post('auth/register', [MobileSessionController::class, 'register'])
            ->middleware('throttle:login')
            ->name('auth.register');
        Route::post('telemetry', [MobileTelemetryController::class, 'store'])
            ->middleware('throttle:120,1')
            ->name('telemetry.store');
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('session', [MobileSessionController::class, 'show'])->name('session.show');
            Route::post('auth/logout', [MobileSessionController::class, 'logout'])->name('auth.logout');
            Route::get('cart', [MobileCommerceController::class, 'cart'])->name('cart.show');
            Route::post('cart/items', [MobileCommerceController::class, 'addCartItem'])->name('cart.items.store');
            Route::patch('cart/items/{cart}', [MobileCommerceController::class, 'updateCartItem'])->whereNumber('cart')->name('cart.items.update');
            Route::delete('cart/items/{cart}', [MobileCommerceController::class, 'deleteCartItem'])->whereNumber('cart')->name('cart.items.destroy');
            Route::delete('cart', [MobileCommerceController::class, 'clearCart'])->name('cart.clear');
            Route::get('wishlist', [MobileCommerceController::class, 'wishlist'])->name('wishlist.index');
            Route::put('wishlist/{slug}', [MobileCommerceController::class, 'addWishlist'])->name('wishlist.store');
            Route::delete('wishlist/{slug}', [MobileCommerceController::class, 'deleteWishlist'])->name('wishlist.destroy');
            Route::get('account', [MobileAccountController::class, 'show'])->name('account.show');
            Route::patch('account/profile', [MobileAccountController::class, 'updateProfile'])->name('account.profile.update');
            Route::patch('account/password', [MobileAccountController::class, 'updatePassword'])->middleware('throttle:6,1')->name('account.password.update');
            Route::patch('addresses/{address}', [MobileAccountController::class, 'updateAddress'])->whereNumber('address')->name('addresses.update');
            Route::delete('addresses/{address}', [MobileAccountController::class, 'deleteAddress'])->whereNumber('address')->name('addresses.destroy');
            Route::get('orders', [MobileAccountController::class, 'orders'])->name('orders.index');
            Route::get('orders/{orderId}', [MobileAccountController::class, 'order'])->name('orders.show');
            Route::post('orders/{orderId}/complete', [MobileAccountController::class, 'complete'])->name('orders.complete');
            Route::post('orders/{orderId}/tax-invoice', [MobileAccountController::class, 'requestTaxInvoice'])->name('orders.tax-invoice.store');
            Route::get('notifications', [MobileAccountController::class, 'notifications'])->name('notifications.index');
            Route::post('notifications/read-all', [MobileAccountController::class, 'readAllNotifications'])->name('notifications.read-all');
            Route::post('notifications/{notification}/read', [MobileAccountController::class, 'readNotification'])->whereNumber('notification')->name('notifications.read');
            Route::middleware('throttle:'.config('mobile-spa.checkout_rate_limit').',1')->group(function () {
                Route::get('checkout', [MobileCheckoutController::class, 'show'])->name('checkout.show');
                Route::post('checkout/shipping-options', [MobileCheckoutController::class, 'shippingOptions'])->name('checkout.shipping-options');
                Route::post('checkout/coupon', [MobileCheckoutController::class, 'applyCoupon'])->name('checkout.coupon.store');
                Route::delete('checkout/coupon', [MobileCheckoutController::class, 'removeCoupon'])->name('checkout.coupon.destroy');
                Route::post('checkout/orders', [MobileCheckoutController::class, 'createOrder'])->name('checkout.orders.store');
                Route::get('orders/{orderId}/payment', [MobileCheckoutController::class, 'payment'])->name('orders.payment.show');
                Route::get('orders/{orderId}/payment/status', [MobileCheckoutController::class, 'paymentStatus'])->name('orders.payment.status');
                Route::post('orders/{orderId}/cancel', [MobileCheckoutController::class, 'cancel'])->name('orders.cancel');
                Route::post('orders/{orderId}/payment-proof', [MobileCheckoutController::class, 'uploadProof'])->name('orders.payment-proof.store');
                Route::post('addresses', [MobileCheckoutController::class, 'storeAddress'])->name('addresses.store');
                Route::get('checkout/locations/provinces', [MobileCheckoutController::class, 'provinces'])->name('checkout.locations.provinces');
                Route::get('checkout/locations/cities', [MobileCheckoutController::class, 'cities'])->name('checkout.locations.cities');
                Route::get('checkout/locations/districts', [MobileCheckoutController::class, 'districts'])->name('checkout.locations.districts');
                Route::get('checkout/locations/subdistricts', [MobileCheckoutController::class, 'subdistricts'])->name('checkout.locations.subdistricts');
            });
        });
    });

/*
|--------------------------------------------------------------------------
| Open Catalog API (v1)
|--------------------------------------------------------------------------
| Read-only, publik, ter-scope per perusahaan lewat slug di URL.
| Proteksi: rate limit per-IP (limiter "api", 120/menit) + HTTP cache headers.
| Lihat docs/prd-company-catalog-api.md.
*/

Route::prefix('v1/companies/{companySlug}')
    ->middleware(['throttle:api', 'cache.headers:public;max_age=300;etag'])
    ->group(function () {
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{identifier}', [ProductController::class, 'show']);

        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{identifier}', [CategoryController::class, 'show']);
    });
