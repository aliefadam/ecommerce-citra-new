<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackendController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
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

        Route::post('categories/quick-add', [CategoryController::class, 'quickStore'])->name('categories.quick-add');
        Route::resource('categories', CategoryController::class)->except(['show']);

        Route::post('variants/quick-add', [VariantController::class, 'quickStore'])->name('variants.quick-add');
        Route::resource('variants', VariantController::class)->except(['show']);
        Route::resource('flash-sales', FlashSaleController::class)->except(['show']);
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });

    Route::prefix('profil')->name('frontend.profil.')->group(function () {
        Route::post('/biodata', [ProfileController::class, 'updateBiodata'])->name('biodata.update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    });
});

Route::name('frontend.')->group(function () {
    Route::get('/', [FrontendController::class, 'index'])->name('index');
    Route::get('/flash-sale', [FrontendController::class, 'flashSale'])->name('flash-sale');
    Route::get('/pencarian', [FrontendController::class, 'search'])->name('search');
    Route::get('/kategori', [FrontendController::class, 'kategori'])->name('kategori');
    Route::get('/detail-produk/{slug?}', [FrontendController::class, 'detailProduk'])->name('detail-produk');
    Route::get('/checkout', [FrontendController::class, 'checkout'])->name('checkout');
    Route::middleware('auth')->group(function () {
        Route::get('/profil', [FrontendController::class, 'profil'])->name('profil');
    });
});
