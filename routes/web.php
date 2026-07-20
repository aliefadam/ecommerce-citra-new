<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminManualTransactionController;
use App\Http\Controllers\AdminProductReviewController;
use App\Http\Controllers\AdminReturnRequestController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminTaxInvoiceController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminWhatsappGatewayController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackendController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryDetailController;
use App\Http\Controllers\CheckoutCouponController;
use App\Http\Controllers\ApiDocController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContentPageController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerTaxInvoiceController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\FrontendContentController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MainCategoryController;
use App\Http\Controllers\ManualPaymentController;
use App\Http\Controllers\MemberTierController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterSubscriberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoPageController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\ReturnRequestController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StoreLocationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\WishlistController;
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

Route::middleware(['auth', 'admin', 'company.scope'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/', [BackendController::class, 'index'])->name('pages.index')->middleware('admin.permission:dashboard.index');
        Route::get('/dashboard2', [BackendController::class, 'dashboard2'])->name('pages.dashboard2')->middleware('admin.permission:dashboard.index');
        Route::get('/charts', [BackendController::class, 'charts'])->name('pages.charts')->middleware('admin.permission:dashboard.index');
        Route::get('/components', [BackendController::class, 'components'])->name('pages.components')->middleware('admin.permission:dashboard.index');
        Route::get('/datatables', [BackendController::class, 'datatables'])->name('pages.datatables')->middleware('admin.permission:dashboard.index');
        Route::get('/settings', [BackendController::class, 'settings'])->name('pages.settings')->middleware('admin.permission:store_settings.index,store_settings.edit');
        Route::post('/settings', [BackendController::class, 'updateSettings'])->name('pages.settings.update')->middleware('admin.permission:store_settings.edit');
        Route::prefix('settings/whatsapp-gateway')->name('whatsapp-gateway.')->middleware('admin.permission:store_settings.edit')->group(function () {
            Route::post('/', [AdminWhatsappGatewayController::class, 'update'])->name('update');
            Route::post('/prepare', [AdminWhatsappGatewayController::class, 'prepare'])->name('prepare');
            Route::post('/connect', [AdminWhatsappGatewayController::class, 'connect'])->name('connect');
            Route::post('/disconnect', [AdminWhatsappGatewayController::class, 'disconnect'])->name('disconnect');
            Route::get('/status', [AdminWhatsappGatewayController::class, 'status'])->name('status');
            Route::get('/qr', [AdminWhatsappGatewayController::class, 'qr'])->name('qr');
            Route::get('/qr/raw', [AdminWhatsappGatewayController::class, 'qrRaw'])->name('qr-raw');
            Route::get('/usage', [AdminWhatsappGatewayController::class, 'usage'])->name('usage');
        });
        Route::get('/change-password', [BackendController::class, 'changePassword'])->name('pages.change-password');
        Route::post('/change-password', [BackendController::class, 'updatePassword'])->name('pages.change-password.update');

        Route::resource('products', ProductController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:products.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:products.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:products.edit')
            ->middlewareFor(['destroy'], 'admin.permission:products.delete');
        Route::get('products-import-template', [ProductController::class, 'downloadImportTemplate'])->name('products.import-template')->middleware('admin.permission:products.import');
        Route::post('products-import', [ProductController::class, 'import'])->name('products.import')->middleware('admin.permission:products.import');
        Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('admin.permission:customers.index');
        Route::resource('member-tiers', MemberTierController::class)->parameters(['member-tiers' => 'memberTier'])->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:member_tiers.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:member_tiers.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:member_tiers.edit')
            ->middlewareFor(['destroy'], 'admin.permission:member_tiers.delete');
        Route::resource('admin-users', AdminUserController::class)->parameters(['admin-users' => 'adminUser'])->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:admin_users.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:admin_users.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:admin_users.edit')
            ->middlewareFor(['destroy'], 'admin.permission:admin_users.delete');
        Route::resource('admin-roles', AdminRoleController::class)->parameters(['admin-roles' => 'adminRole'])->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:admin_roles.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:admin_roles.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:admin_roles.edit')
            ->middlewareFor(['destroy'], 'admin.permission:admin_roles.delete');
        Route::resource('companies', CompanyController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:companies.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:companies.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:companies.edit')
            ->middlewareFor(['destroy'], 'admin.permission:companies.delete');
        Route::post('switch-company', [CompanyController::class, 'switch'])->name('companies.switch');

        Route::get('/api-docs', [ApiDocController::class, 'index'])->name('api-docs.index')->middleware('admin.permission:api_docs.index');

        Route::resource('main-categories', MainCategoryController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:categories.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:categories.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:categories.edit')
            ->middlewareFor(['destroy'], 'admin.permission:categories.delete');
        Route::post('categories/quick-add', [CategoryDetailController::class, 'quickStore'])->name('categories.quick-add')->middleware('admin.permission:categories.create');
        Route::resource('category-details', CategoryDetailController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:categories.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:categories.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:categories.edit')
            ->middlewareFor(['destroy'], 'admin.permission:categories.delete');

        Route::post('variants/quick-add', [VariantController::class, 'quickStore'])->name('variants.quick-add')->middleware('admin.permission:variants.create');
        Route::resource('variants', VariantController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:variants.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:variants.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:variants.edit')
            ->middlewareFor(['destroy'], 'admin.permission:variants.delete');
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index')->middleware('admin.permission:stock.index');
        Route::post('stocks', [StockController::class, 'store'])->name('stocks.store')->middleware('admin.permission:stock.edit');
        Route::patch('stocks/{productVariant}/threshold', [StockController::class, 'updateThreshold'])->name('stocks.threshold')->middleware('admin.permission:stock.edit');
        Route::resource('flash-sales', FlashSaleController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:flash_sales.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:flash_sales.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:flash_sales.edit')
            ->middlewareFor(['destroy'], 'admin.permission:flash_sales.delete');
        Route::resource('coupons', CouponController::class)->except(['show', 'create', 'edit'])
            ->middlewareFor(['index'], 'admin.permission:coupons.index')
            ->middlewareFor(['store'], 'admin.permission:coupons.create')
            ->middlewareFor(['update'], 'admin.permission:coupons.edit')
            ->middlewareFor(['destroy'], 'admin.permission:coupons.delete');
        Route::resource('banners', BannerController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:banners.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:banners.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:banners.edit')
            ->middlewareFor(['destroy'], 'admin.permission:banners.delete');
        Route::get('newsletter-subscribers', [NewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index')->middleware('admin.permission:newsletter.index');
        Route::get('newsletter-subscribers/export', [NewsletterSubscriberController::class, 'export'])->name('newsletter-subscribers.export')->middleware('admin.permission:newsletter.send');
        Route::post('newsletter-subscribers/send', [NewsletterSubscriberController::class, 'send'])->name('newsletter-subscribers.send')->middleware('admin.permission:newsletter.send');
        Route::post('newsletter-subscribers/send-test', [NewsletterSubscriberController::class, 'sendTest'])->name('newsletter-subscribers.send-test')->middleware('admin.permission:newsletter.send');
        Route::post('newsletter-subscribers/preview', [NewsletterSubscriberController::class, 'preview'])->name('newsletter-subscribers.preview')->middleware('admin.permission:newsletter.send');
        Route::delete('newsletter-subscribers/{newsletterSubscriber}', [NewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy')->middleware('admin.permission:newsletter.delete');
        Route::resource('promo-pages', PromoPageController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:promo_pages.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:promo_pages.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:promo_pages.edit')
            ->middlewareFor(['destroy'], 'admin.permission:promo_pages.delete');
        Route::resource('content-pages', ContentPageController::class)->except(['show'])
            ->middlewareFor(['index'], 'admin.permission:content_pages.index')
            ->middlewareFor(['create', 'store'], 'admin.permission:content_pages.create')
            ->middlewareFor(['edit', 'update'], 'admin.permission:content_pages.edit')
            ->middlewareFor(['destroy'], 'admin.permission:content_pages.delete');
        Route::get('reports', [SalesReportController::class, 'home'])->name('reports.index')->middleware('admin.permission:reports.index');
        Route::get('reports/owner', [SalesReportController::class, 'owner'])->name('reports.owner')->middleware('admin.permission:reports.owner');
        Route::get('reports/sales', [SalesReportController::class, 'index'])->name('reports.sales')->middleware('admin.permission:reports.sales');
        Route::get('reports/stock', [SalesReportController::class, 'stock'])->name('reports.stock')->middleware('admin.permission:reports.stock');
        Route::get('reports/products', [SalesReportController::class, 'products'])->name('reports.products')->middleware('admin.permission:reports.products');
        Route::get('reports/payments', [SalesReportController::class, 'payments'])->name('reports.payments')->middleware('admin.permission:reports.payments');
        Route::get('reports/customers', [SalesReportController::class, 'customers'])->name('reports.customers')->middleware('admin.permission:reports.customers');
        Route::get('reports/promos', [SalesReportController::class, 'promos'])->name('reports.promos')->middleware('admin.permission:reports.promos');
        Route::get('reports/returns', [SalesReportController::class, 'returns'])->name('reports.returns')->middleware('admin.permission:reports.returns');
        Route::get('store-location', [StoreLocationController::class, 'edit'])->name('store-locations.edit')->middleware('admin.permission:store_settings.index,store_settings.edit');
        Route::put('store-location', [StoreLocationController::class, 'update'])->name('store-locations.update')->middleware('admin.permission:store_settings.edit');
        Route::get('store-location/provinces', [StoreLocationController::class, 'provinces'])->name('store-locations.provinces')->middleware('admin.permission:store_settings.edit');
        Route::get('store-location/cities', [StoreLocationController::class, 'cities'])->name('store-locations.cities')->middleware('admin.permission:store_settings.edit');
        Route::get('transactions/shipping-labels/bulk', [TransactionController::class, 'bulkShippingLabels'])->name('transactions.bulk-shipping-label')->middleware('admin.permission:transactions.show');
        Route::get('tax-invoices', [AdminTaxInvoiceController::class, 'index'])->name('tax-invoices.index')->middleware('admin.permission:tax_invoices.index');
        Route::get('tax-invoices/{taxInvoice}', [AdminTaxInvoiceController::class, 'show'])->name('tax-invoices.show')->middleware('admin.permission:tax_invoices.show');
        Route::get('tax-invoices/{taxInvoice}/download', [AdminTaxInvoiceController::class, 'download'])->name('tax-invoices.download')->middleware('admin.permission:tax_invoices.show');
        Route::patch('tax-invoices/{taxInvoice}/process', [AdminTaxInvoiceController::class, 'process'])->name('tax-invoices.process')->middleware('admin.permission:tax_invoices.process');
        Route::patch('tax-invoices/{taxInvoice}/reject', [AdminTaxInvoiceController::class, 'reject'])->name('tax-invoices.reject')->middleware('admin.permission:tax_invoices.reject');
        Route::post('tax-invoices/{taxInvoice}/upload', [AdminTaxInvoiceController::class, 'upload'])->name('tax-invoices.upload')->middleware('admin.permission:tax_invoices.upload');
        Route::post('tax-invoices/{taxInvoice}/send', [AdminTaxInvoiceController::class, 'send'])->name('tax-invoices.send')->middleware('admin.permission:tax_invoices.send');
        Route::get('transactions/create-manual', [AdminManualTransactionController::class, 'create'])->name('transactions.create-manual')->middleware('admin.permission:transactions.create');
        Route::post('transactions/create-manual', [AdminManualTransactionController::class, 'store'])->name('transactions.store-manual')->middleware('admin.permission:transactions.create');
        Route::get('transactions/create-manual/search-customers', [AdminManualTransactionController::class, 'searchCustomers'])->name('transactions.create-manual.search-customers')->middleware('admin.permission:transactions.create');
        Route::get('transactions/create-manual/search-products', [AdminManualTransactionController::class, 'searchProducts'])->name('transactions.create-manual.search-products')->middleware('admin.permission:transactions.create');
        Route::patch('transactions/{transaction}/manual-payment', [AdminManualTransactionController::class, 'updatePayment'])->name('transactions.manual-payment.update')->middleware('admin.permission:transactions.verify_payment');
        Route::patch('transactions/{transaction}/manual-shipping', [AdminManualTransactionController::class, 'updateShipping'])->name('transactions.manual-shipping.update')->middleware('admin.permission:transactions.edit');
        Route::resource('transactions', TransactionController::class)->only(['index', 'show'])
            ->middlewareFor(['index'], 'admin.permission:transactions.index')
            ->middlewareFor(['show'], 'admin.permission:transactions.show');
        Route::patch('transactions/{transaction}/process', [TransactionController::class, 'process'])->name('transactions.process')->middleware('admin.permission:transactions.edit');
        Route::patch('transactions/{transaction}/ship', [TransactionController::class, 'ship'])->name('transactions.ship')->middleware('admin.permission:transactions.edit');
        Route::get('transactions/{transaction}/shipping-label', [TransactionController::class, 'shippingLabel'])->name('transactions.shipping-label')->middleware('admin.permission:transactions.show');
        Route::patch('transactions/{transaction}/verify-payment', [TransactionController::class, 'verifyPayment'])->name('transactions.verify-payment')->middleware('admin.permission:transactions.verify_payment');
        Route::get('return-requests', [AdminReturnRequestController::class, 'index'])->name('return-requests.index')->middleware('admin.permission:return_requests.index');
        Route::patch('return-requests/{returnRequest}', [AdminReturnRequestController::class, 'update'])->name('return-requests.update')->middleware('admin.permission:return_requests.edit');
        Route::get('product-reviews', [AdminProductReviewController::class, 'index'])->name('product-reviews.index')->middleware('admin.permission:product_reviews.index');
        Route::patch('product-reviews/{review}/toggle', [AdminProductReviewController::class, 'toggle'])->name('product-reviews.toggle')->middleware('admin.permission:product_reviews.edit');
        Route::delete('product-reviews/{review}', [AdminProductReviewController::class, 'destroy'])->name('product-reviews.destroy')->middleware('admin.permission:product_reviews.delete');
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
    Route::post('/orders/{transaction}/tax-invoice', [CustomerTaxInvoiceController::class, 'store'])->name('orders.tax-invoice.store');
    Route::get('/orders/{transaction}/tax-invoice/download', [CustomerTaxInvoiceController::class, 'download'])->name('orders.tax-invoice.download');
});

Route::name('frontend.')->group(function () {
    Route::get('/', [FrontendController::class, 'index'])->name('index');
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->name('newsletter.subscribe');
    Route::get('/newsletter/unsubscribe/{token}', [NewsletterSubscriberController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
    Route::get('/flash-sale', [FrontendController::class, 'flashSale'])->name('flash-sale');
    Route::get('/promo/{slug?}', [FrontendController::class, 'promo'])->name('promo');
    Route::get('/pages/{slug}', [FrontendContentController::class, 'page'])->name('pages.show');
    Route::get('/blog', [FrontendContentController::class, 'blog'])->name('blog.index');
    Route::get('/blog/{slug}', [FrontendContentController::class, 'post'])->name('blog.show');
    Route::get('/redeem-point', [FrontendController::class, 'redeemPoint'])->name('redeem-point');
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
        Route::post('/redeem-point/checkout', [CartController::class, 'prepareRedeemCheckout'])->name('redeem.prepare-checkout');
        Route::post('/checkout/buy-now', [CartController::class, 'buyNow'])->name('checkout.buy-now');
        Route::post('/checkout/complete', [CartController::class, 'completeCheckout'])->name('checkout.complete');
        Route::get('/checkout/orders', [FrontendController::class, 'checkoutOrders'])->name('checkout.orders');
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
