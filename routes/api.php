<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

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
