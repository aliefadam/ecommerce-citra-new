<?php

use App\Http\Controllers\Api\Mobile\V1\MobileCatalogController;
use App\Http\Controllers\Api\Mobile\V1\MobileSessionController;
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
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('session', [MobileSessionController::class, 'show'])->name('session.show');
            Route::post('auth/logout', [MobileSessionController::class, 'logout'])->name('auth.logout');
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
