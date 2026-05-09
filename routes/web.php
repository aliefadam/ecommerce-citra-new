<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackendController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MainCategoryController;
use App\Http\Controllers\CategoryDetailController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionReviewController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\StoreLocationController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReturnRequestController;
use App\Http\Controllers\AdminReturnRequestController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CheckoutCouponController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\ManualPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/', [BackendController::class, 'index'])->name('pages.index');
        Route::get('/dashboard2', [BackendController::class, 'dashboard2'])->name('pages.dashboard2');
        Route::get('/charts', [BackendController::class, 'charts'])->name('pages.charts');
        Route::get('/components', [BackendController::class, 'components'])->name('pages.components');
        Route::get('/datatables', [BackendController::class, 'datatables'])->name('pages.datatables');
        Route::get('/settings', [BackendController::class, 'settings'])->name('pages.settings');
        Route::post('/settings', [BackendController::class, 'updateSettings'])->name('pages.settings.update');
        Route::get('/change-password', [BackendController::class, 'changePassword'])->name('pages.change-password');
        Route::post('/change-password', [BackendController::class, 'updatePassword'])->name('pages.change-password.update');

        Route::resource('products', ProductController::class)->except(['show']);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        Route::resource('main-categories', MainCategoryController::class)->except(['show']);
        Route::post('categories/quick-add', [CategoryDetailController::class, 'quickStore'])->name('categories.quick-add');
        Route::resource('category-details', CategoryDetailController::class)->except(['show']);

        Route::post('variants/quick-add', [VariantController::class, 'quickStore'])->name('variants.quick-add');
        Route::resource('variants', VariantController::class)->except(['show']);
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::post('stocks', [StockController::class, 'store'])->name('stocks.store');
        Route::patch('stocks/{productVariant}/threshold', [StockController::class, 'updateThreshold'])->name('stocks.threshold');
        Route::resource('flash-sales', FlashSaleController::class)->except(['show']);
        Route::resource('coupons', CouponController::class)->except(['show', 'create', 'edit']);
        Route::resource('banners', BannerController::class)->except(['show']);
        Route::get('reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
        Route::get('store-location', [StoreLocationController::class, 'edit'])->name('store-locations.edit');
        Route::put('store-location', [StoreLocationController::class, 'update'])->name('store-locations.update');
        Route::get('store-location/provinces', [StoreLocationController::class, 'provinces'])->name('store-locations.provinces');
        Route::get('store-location/cities', [StoreLocationController::class, 'cities'])->name('store-locations.cities');
        Route::resource('transactions', TransactionController::class)->only(['index', 'show']);
        Route::patch('transactions/{transaction}/process', [TransactionController::class, 'process'])->name('transactions.process');
        Route::patch('transactions/{transaction}/ship', [TransactionController::class, 'ship'])->name('transactions.ship');
        Route::patch('transactions/{transaction}/verify-payment', [TransactionController::class, 'verifyPayment'])->name('transactions.verify-payment');
        Route::get('return-requests', [AdminReturnRequestController::class, 'index'])->name('return-requests.index');
        Route::patch('return-requests/{returnRequest}', [AdminReturnRequestController::class, 'update'])->name('return-requests.update');
    });

});

Route::middleware('auth')->prefix('profil')->name('frontend.profil.')->group(function () {
    Route::post('/biodata', [ProfileController::class, 'updateBiodata'])->name('biodata.update');
    Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/reviews', [TransactionReviewController::class, 'store'])->name('reviews.store');
    Route::post('/return-requests', [ReturnRequestController::class, 'store'])->name('return-requests.store');
    Route::patch('/orders/{transaction}/complete', [CustomerOrderController::class, 'complete'])->name('orders.complete');
});

Route::name('frontend.')->group(function () {
    Route::get('/', [FrontendController::class, 'index'])->name('index');
    Route::get('/flash-sale', [FrontendController::class, 'flashSale'])->name('flash-sale');
    Route::get('/pencarian', [FrontendController::class, 'search'])->name('search');
    Route::get('/kategori', [FrontendController::class, 'kategori'])->name('kategori');
    Route::get('/detail-produk/{slug?}', [FrontendController::class, 'detailProduk'])->name('detail-produk');

    Route::middleware('auth')->group(function () {
        Route::get('/profil', [FrontendController::class, 'profil'])->name('profil');
        Route::get('/checkout', [FrontendController::class, 'checkout'])->name('checkout');
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
        Route::get('/cart/items', [CartController::class, 'items'])->name('cart.items');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('/cart/checkout', [CartController::class, 'prepareCheckout'])->name('cart.prepare-checkout');
        Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
        Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/checkout/buy-now', [CartController::class, 'buyNow'])->name('checkout.buy-now');
        Route::post('/checkout/complete', [CartController::class, 'completeCheckout'])->name('checkout.complete');
        Route::post('/checkout/manual-payment', [ManualPaymentController::class, 'checkout'])->name('checkout.manual-payment');
        Route::post('/checkout/coupon/apply', [CheckoutCouponController::class, 'apply'])->name('checkout.coupon.apply');
        Route::delete('/checkout/coupon', [CheckoutCouponController::class, 'remove'])->name('checkout.coupon.remove');
        Route::get('/rajaongkir/provinces', [RajaOngkirController::class, 'provinces'])->name('rajaongkir.provinces');
        Route::get('/rajaongkir/cities', [RajaOngkirController::class, 'cities'])->name('rajaongkir.cities');
        Route::get('/rajaongkir/districts', [RajaOngkirController::class, 'districts'])->name('rajaongkir.districts');
        Route::get('/rajaongkir/subdistricts', [RajaOngkirController::class, 'subdistricts'])->name('rajaongkir.subdistricts');
        Route::get('/rajaongkir/shipping-options', [RajaOngkirController::class, 'shippingOptions'])->name('rajaongkir.shipping-options');
        Route::post('/checkout/midtrans/charge', [MidtransController::class, 'createCharge'])->name('checkout.midtrans.charge');
        Route::get('/checkout/waiting/{orderId}', [MidtransController::class, 'waiting'])->name('checkout.waiting');
        Route::get('/checkout/midtrans/status/{orderId}', [MidtransController::class, 'status'])->name('checkout.midtrans.status');
        Route::post('/checkout/midtrans/cancel/{orderId}', [MidtransController::class, 'cancel'])->name('checkout.midtrans.cancel');
        Route::post('/checkout/midtrans/simulate', [MidtransController::class, 'simulate'])->name('checkout.midtrans.simulate');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
        Route::post('/wishlist/status', [WishlistController::class, 'status'])->name('wishlist.status');
        Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');

        // Notifikasi user
        Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])->name('notifications.read');
    });
});

Route::get('/invoice/{transaction}', [InvoiceController::class, 'show'])->name('invoice.show')->middleware('auth');
Route::post('/manual-payment/{transaction}/proof', [ManualPaymentController::class, 'uploadProof'])->name('manual-payment.proof')->middleware('auth');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
