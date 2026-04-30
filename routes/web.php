<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackendController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/', [BackendController::class, 'index'])->name('pages.index');
        Route::get('/charts', [BackendController::class, 'charts'])->name('pages.charts');
        Route::get('/components', [BackendController::class, 'components'])->name('pages.components');
        Route::get('/datatables', [BackendController::class, 'datatables'])->name('pages.datatables');
        Route::get('/settings', [BackendController::class, 'settings'])->name('pages.settings');

        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::name('frontend.')->group(function () {
    Route::get('/', [FrontendController::class, 'index'])->name('index');
    Route::get('/flash-sale', [FrontendController::class, 'flashSale'])->name('flash-sale');
    Route::get('/pencarian', [FrontendController::class, 'search'])->name('search');
    Route::get('/kategori', [FrontendController::class, 'kategori'])->name('kategori');
    Route::get('/detail-produk', [FrontendController::class, 'detailProduk'])->name('detail-produk');
    Route::get('/checkout', [FrontendController::class, 'checkout'])->name('checkout');
    Route::get('/profil', [FrontendController::class, 'profil'])->name('profil');
});
