<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionProductReview;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    use ScopesToActiveCompany;

    public function home()
    {
        $groups = [
            [
                'title' => 'Laporan Penjualan',
                'description' => 'Pantau omzet, transaksi, status order, dan performa pendapatan.',
                'icon' => 'receipt',
                'tone' => 'blue',
                'items' => [
                    [
                        'title' => 'Owner Overview',
                        'description' => 'Ringkasan omzet, order, customer, stok, promo, return, dan pekerjaan aktif.',
                        'route' => route('reports.owner'),
                        'permission' => 'reports.owner',
                        'icon' => 'layout-dashboard',
                        'tone' => 'blue',
                    ],
                    [
                        'title' => 'Sales Report',
                        'description' => 'Periode, omzet, transaksi, status, top produk, dan export CSV.',
                        'route' => route('reports.sales'),
                        'permission' => 'reports.sales',
                        'icon' => 'bar-chart-3',
                        'tone' => 'blue',
                    ],
                    [
                        'title' => 'Payment dan Fulfillment',
                        'description' => 'Metode pembayaran, pending payment, verifikasi manual, dan queue pesanan.',
                        'route' => route('reports.payments'),
                        'permission' => 'reports.payments',
                        'icon' => 'credit-card',
                        'tone' => 'emerald',
                    ],
                    [
                        'title' => 'Customer Report',
                        'description' => 'Customer baru, pembeli aktif, repeat buyer, cart, wishlist, dan newsletter.',
                        'route' => route('reports.customers'),
                        'permission' => 'reports.customers',
                        'icon' => 'users',
                        'tone' => 'cyan',
                    ],
                ],
            ],
            [
                'title' => 'Laporan Persediaan',
                'description' => 'Pantau saldo stok, item minimum, nilai persediaan, dan mutasi.',
                'icon' => 'package',
                'tone' => 'orange',
                'items' => [
                    [
                        'title' => 'Stock Report',
                        'description' => 'Low stock, out of stock, mutasi stok, dan estimasi nilai stok.',
                        'route' => route('reports.stock'),
                        'permission' => 'reports.stock',
                        'icon' => 'boxes',
                        'tone' => 'orange',
                    ],
                ],
            ],
            [
                'title' => 'Laporan Produk',
                'description' => 'Evaluasi produk yang paling kuat dan yang perlu didorong ulang.',
                'icon' => 'shopping-bag',
                'tone' => 'violet',
                'items' => [
                    [
                        'title' => 'Product Performance',
                        'description' => 'Produk terlaris, wishlist tertinggi, rating terbaik, dan produk lambat.',
                        'route' => route('reports.products'),
                        'permission' => 'reports.products',
                        'icon' => 'trending-up',
                        'tone' => 'violet',
                    ],
                ],
            ],
            [
                'title' => 'Laporan Operasional',
                'description' => 'Pantau promosi, kupon, retur, refund, dan kualitas operasional harian.',
                'icon' => 'clipboard-list',
                'tone' => 'rose',
                'items' => [
                    [
                        'title' => 'Promo dan Coupon',
                        'description' => 'Kupon aktif, pemakaian promo, nilai diskon, dan transaksi berkode kupon.',
                        'route' => route('reports.promos'),
                        'permission' => 'reports.promos',
                        'icon' => 'badge-percent',
                        'tone' => 'amber',
                    ],
                    [
                        'title' => 'Return dan Refund',
                        'description' => 'Pengajuan retur, refund, status penyelesaian, dan return rate.',
                        'route' => route('reports.returns'),
                        'permission' => 'reports.returns',
                        'icon' => 'rotate-ccw',
                        'tone' => 'rose',
                    ],
                ],
            ],
        ];

        $user = request()->user();
        $groups = collect($groups)
            ->map(function ($group) use ($user) {
                $group['items'] = collect($group['items'])
                    ->filter(fn ($item) => empty($item['permission']) || $user?->hasAdminPermission($item['permission']))
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn ($group) => ! empty($group['items']))
            ->values()
            ->all();

        return view('backend.reports.index', compact('groups'));
    }

    public function owner(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = $this->paidStatuses();
        $periodBase = $this->companyTransactions()->whereBetween('created_at', [$start, $end]);
        $paidBase = (clone $periodBase)->whereIn(DB::raw('LOWER(status)'), $paidStatuses);

        $orders = (clone $paidBase)->count();
        $revenue = (clone $paidBase)->sum('grand_total');
        $periodDays = max(1, $start->diffInDays($end) + 1);
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($periodDays - 1)->startOfDay();
        $previousPaid = $this->companyTransactions()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->whereIn(DB::raw('LOWER(status)'), $paidStatuses);
        $previousRevenue = (clone $previousPaid)->sum('grand_total');
        $previousOrders = (clone $previousPaid)->count();

        $overview = [
            'revenue' => $revenue,
            'orders' => $orders,
            'aov' => $orders > 0 ? round($revenue / $orders) : 0,
            'items_sold' => $this->companyTransactionDetails()
                ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
                ->sum('quantity'),
            'revenue_growth' => $this->percentageChange($revenue, $previousRevenue),
            'order_growth' => $this->percentageChange($orders, $previousOrders),
            'new_customers' => $this->companyCustomers()->whereBetween('created_at', [$start, $end])->count(),
            'active_customers' => (clone $paidBase)->distinct('user_id')->whereNotNull('user_id')->count('user_id'),
            'inventory_value' => $this->companyProductVariants()->selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0,
            'low_stock' => $this->companyProductVariants()->whereRaw('stock > 0 AND stock <= COALESCE(low_stock_threshold, 5)')->count(),
            'pending_payment' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['pending', 'menunggu_verifikasi'])->count(),
            'fulfillment_queue' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped'])->count(),
            'return_open' => $this->companyReturnRequests()->whereNotIn('status', ['selesai', 'completed', 'ditolak', 'rejected'])->count(),
            'discount' => (clone $paidBase)->sum('discount_amount'),
        ];

        $dailyRevenue = (clone $paidBase)
            ->selectRaw('DATE(created_at) as sales_date, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->groupBy('sales_date')
            ->orderBy('sales_date')
            ->get();

        $topProducts = $this->companyTransactionDetails()
            ->selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->take(8)
            ->get();

        $statusBreakdown = (clone $periodBase)
            ->selectRaw('LOWER(status) as status_key, COUNT(*) as total_orders')
            ->groupBy('status_key')
            ->orderByDesc('total_orders')
            ->take(8)
            ->get();

        $workQueue = $this->companyTransactions()
            ->with('user')
            ->whereIn(DB::raw('LOWER(status)'), ['pending', 'menunggu_verifikasi', 'paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped'])
            ->latest()
            ->take(8)
            ->get();

        return view('backend.reports.owner', compact('start', 'end', 'overview', 'dailyRevenue', 'topProducts', 'statusBreakdown', 'workQueue'));
    }

    public function index(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];
        $pendingStatuses = ['pending', 'menunggu_verifikasi'];
        $fulfillmentStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped'];

        $periodBase = $this->companyTransactions()
            ->whereBetween('created_at', [$start, $end]);

        $base = (clone $periodBase)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn(DB::raw('LOWER(status)'), $paidStatuses);

        $orders = (clone $base)->count();
        $revenue = (clone $base)->sum('grand_total');

        $summary = [
            'orders' => $orders,
            'revenue' => $revenue,
            'discount' => (clone $base)->sum('discount_amount'),
            'shipping' => (clone $base)->sum('shipping_cost'),
            'items_sold' => $this->companyTransactionDetails()
                ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
                ->sum('quantity'),
            'average_order_value' => $orders > 0 ? round($revenue / $orders) : 0,
            'all_orders' => (clone $periodBase)->count(),
        ];

        $transactions = (clone $base)->with('user')->latest()->take(12)->get();

        if ($request->query('export') === 'csv') {
            abort_unless($request->user()?->hasAdminPermission('reports.sales.export'), 403);

            return $this->exportTransactions((clone $base)->with('user')->latest()->get(), $start, $end);
        }

        $statusBreakdown = (clone $periodBase)
            ->selectRaw('LOWER(status) as status_key, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->groupBy('status_key')
            ->orderByDesc('total_orders')
            ->get();

        $dailyRevenue = (clone $base)
            ->selectRaw('DATE(created_at) as sales_date, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->groupBy('sales_date')
            ->orderBy('sales_date')
            ->get();

        $topProducts = $this->companyTransactionDetails()
            ->selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $stockSummary = [
            'total_variants' => $this->companyProductVariants()->count(),
            'low_stock' => $this->companyProductVariants()->whereRaw('stock > 0 AND stock <= COALESCE(low_stock_threshold, 5)')->count(),
            'out_of_stock' => $this->companyProductVariants()->where('stock', '<=', 0)->count(),
            'inventory_value' => $this->companyProductVariants()->selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0,
        ];

        $lowStockVariants = $this->companyProductVariants()
            ->with(['product', 'variant', 'attributeValues.definition'])
            ->whereRaw('stock <= COALESCE(low_stock_threshold, 5)')
            ->orderBy('stock')
            ->take(10)
            ->get();

        $stockMovements = $this->companyStockMovements()
            ->with(['productVariant.product', 'productVariant.variant'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->take(10)
            ->get();

        $topWishlisted = $this->companyProducts()
            ->withCount('wishlists')
            ->where('status', 'active')
            ->has('wishlists')
            ->orderByDesc('wishlists_count')
            ->take(8)
            ->get();

        $slowProducts = $this->companyProducts()
            ->withSum('productVariants as stock_total', 'stock')
            ->where('status', 'active')
            ->whereDoesntHave('transactionDetails', fn ($q) => $q->whereHas('transaction', fn ($tx) => $tx->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses)))
            ->orderByDesc('stock_total')
            ->take(8)
            ->get();

        $topRatedProducts = $this->companyProductReviews()
            ->join('transaction_details', 'transaction_product_reviews.transaction_detail_id', '=', 'transaction_details.id')
            ->where('transaction_product_reviews.is_hidden', false)
            ->selectRaw('transaction_details.product_name, AVG(transaction_product_reviews.rating) as avg_rating, COUNT(*) as total_reviews')
            ->groupBy('transaction_details.product_name')
            ->havingRaw('COUNT(*) > 0')
            ->orderByDesc('avg_rating')
            ->orderByDesc('total_reviews')
            ->take(8)
            ->get();

        $paymentMethods = (clone $periodBase)
            ->selectRaw('COALESCE(NULLIF(payment_method, ""), NULLIF(payment_type, ""), "Tidak diketahui") as method_label, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->groupBy('method_label')
            ->orderByDesc('total_orders')
            ->get();

        $paymentSummary = [
            'pending_payment' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), $pendingStatuses)->count(),
            'manual_waiting' => $this->companyTransactions()->where('payment_type', 'manual_transfer')->where('status', 'menunggu_verifikasi')->count(),
            'paid_period' => $orders,
            'failed_period' => (clone $periodBase)->whereIn(DB::raw('LOWER(status)'), ['deny', 'failure', 'expire', 'cancel', 'dibatalkan'])->count(),
        ];

        $fulfillmentSummary = [
            'need_process' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'capture'])->count(),
            'in_process' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['process', 'processing'])->count(),
            'shipping' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['kirim', 'shipping', 'shipped'])->count(),
            'active_pipeline' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), $fulfillmentStatuses)->count(),
        ];

        $operationQueue = $this->companyTransactions()
            ->with('user')
            ->whereIn(DB::raw('LOWER(status)'), array_merge($pendingStatuses, $fulfillmentStatuses))
            ->latest()
            ->take(10)
            ->get();

        return view('backend.reports.sales', compact(
            'summary',
            'transactions',
            'topProducts',
            'start',
            'end',
            'statusBreakdown',
            'dailyRevenue',
            'stockSummary',
            'lowStockVariants',
            'stockMovements',
            'topWishlisted',
            'slowProducts',
            'topRatedProducts',
            'paymentMethods',
            'paymentSummary',
            'fulfillmentSummary',
            'operationQueue'
        ));
    }

    public function stock(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();

        $stockSummary = [
            'total_variants' => $this->companyProductVariants()->count(),
            'low_stock' => $this->companyProductVariants()->whereRaw('stock > 0 AND stock <= COALESCE(low_stock_threshold, 5)')->count(),
            'out_of_stock' => $this->companyProductVariants()->where('stock', '<=', 0)->count(),
            'inventory_value' => $this->companyProductVariants()->selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0,
        ];

        $variants = $this->companyProductVariants()
            ->with(['product', 'variant', 'attributeValues.definition'])
            ->orderBy('stock')
            ->paginate(20)
            ->withQueryString();

        $stockMovements = $this->companyStockMovements()
            ->with(['productVariant.product', 'productVariant.variant', 'adminUser'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->paginate(20, ['*'], 'movements_page')
            ->withQueryString();

        return view('backend.reports.stock', compact('start', 'end', 'stockSummary', 'variants', 'stockMovements'));
    }

    public function products(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];

        $topProducts = $this->companyTransactionDetails()
            ->selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(20)
            ->get();

        $topWishlisted = $this->companyProducts()
            ->withCount('wishlists')
            ->where('status', 'active')
            ->has('wishlists')
            ->orderByDesc('wishlists_count')
            ->take(20)
            ->get();

        $topRatedProducts = $this->companyProductReviews()
            ->join('transaction_details', 'transaction_product_reviews.transaction_detail_id', '=', 'transaction_details.id')
            ->where('transaction_product_reviews.is_hidden', false)
            ->selectRaw('transaction_details.product_name, AVG(transaction_product_reviews.rating) as avg_rating, COUNT(*) as total_reviews')
            ->groupBy('transaction_details.product_name')
            ->havingRaw('COUNT(*) > 0')
            ->orderByDesc('avg_rating')
            ->orderByDesc('total_reviews')
            ->take(20)
            ->get();

        $slowProducts = $this->companyProducts()
            ->withSum('productVariants as stock_total', 'stock')
            ->where('status', 'active')
            ->whereDoesntHave('transactionDetails', fn ($q) => $q->whereHas('transaction', fn ($tx) => $tx->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses)))
            ->orderByDesc('stock_total')
            ->take(20)
            ->get();

        return view('backend.reports.products', compact('start', 'end', 'topProducts', 'topWishlisted', 'topRatedProducts', 'slowProducts'));
    }

    public function payments(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];
        $pendingStatuses = ['pending', 'menunggu_verifikasi'];
        $fulfillmentStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped'];

        $periodBase = $this->companyTransactions()->whereBetween('created_at', [$start, $end]);

        $paymentSummary = [
            'pending_payment' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), $pendingStatuses)->count(),
            'manual_waiting' => $this->companyTransactions()->where('payment_type', 'manual_transfer')->where('status', 'menunggu_verifikasi')->count(),
            'paid_period' => (clone $periodBase)->whereIn(DB::raw('LOWER(status)'), $paidStatuses)->count(),
            'failed_period' => (clone $periodBase)->whereIn(DB::raw('LOWER(status)'), ['deny', 'failure', 'expire', 'cancel', 'dibatalkan'])->count(),
        ];

        $fulfillmentSummary = [
            'need_process' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'capture'])->count(),
            'in_process' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['process', 'processing'])->count(),
            'shipping' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), ['kirim', 'shipping', 'shipped'])->count(),
            'active_pipeline' => $this->companyTransactions()->whereIn(DB::raw('LOWER(status)'), $fulfillmentStatuses)->count(),
        ];

        $paymentMethods = (clone $periodBase)
            ->selectRaw('COALESCE(NULLIF(payment_method, ""), NULLIF(payment_type, ""), "Tidak diketahui") as method_label, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->groupBy('method_label')
            ->orderByDesc('total_orders')
            ->get();

        $operationQueue = $this->companyTransactions()
            ->with('user')
            ->whereIn(DB::raw('LOWER(status)'), array_merge($pendingStatuses, $fulfillmentStatuses))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.reports.payments', compact('start', 'end', 'paymentSummary', 'fulfillmentSummary', 'paymentMethods', 'operationQueue'));
    }

    public function customers(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = $this->paidStatuses();

        $paidInPeriod = $this->companyTransactions()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn(DB::raw('LOWER(status)'), $paidStatuses);

        $summary = [
            'new_customers' => $this->companyCustomers()->whereBetween('created_at', [$start, $end])->count(),
            'active_buyers' => (clone $paidInPeriod)->distinct('user_id')->whereNotNull('user_id')->count('user_id'),
            'repeat_buyers' => $this->companyCustomers()
                ->whereHas('transactions', fn ($q) => $q->where('company_id', $this->activeCompanyId())->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses), '>=', 2)
                ->count(),
            'cart_users' => $this->companyCarts()->distinct('user_id')->count('user_id'),
            'wishlist_users' => $this->companyWishlists()->distinct('user_id')->count('user_id'),
            'newsletter' => $this->companyNewsletterSubscribers()->where('is_subscribed', true)->count(),
        ];

        $topCustomers = $this->companyCustomers()
            ->whereHas('transactions', fn ($q) => $q
                ->where('company_id', $this->activeCompanyId())
                ->whereBetween('created_at', [$start, $end])
                ->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
            ->withCount(['transactions as paid_orders_count' => fn ($q) => $q->where('company_id', $this->activeCompanyId())->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses)])
            ->withSum(['transactions as paid_revenue_sum' => fn ($q) => $q->where('company_id', $this->activeCompanyId())->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses)], 'grand_total')
            ->orderByDesc('paid_revenue_sum')
            ->take(20)
            ->get();

        $recentCustomers = $this->companyCustomers()
            ->latest()
            ->take(20)
            ->get();

        return view('backend.reports.customers', compact('start', 'end', 'summary', 'topCustomers', 'recentCustomers'));
    }

    public function promos(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = $this->paidStatuses();

        $couponTransactions = $this->companyTransactions()
            ->with('user')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
            ->whereNotNull('coupon_code')
            ->where('coupon_code', '!=', '');

        $summary = [
            'active_coupons' => $this->companyCoupons()->where('is_active', true)->count(),
            'coupon_orders' => (clone $couponTransactions)->count(),
            'discount_total' => (clone $couponTransactions)->sum('discount_amount'),
            'redeemed_points' => $this->companyTransactions()->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses)->sum('redeem_points_reserved'),
        ];

        $topCoupons = (clone $couponTransactions)
            ->selectRaw('coupon_code, COUNT(*) as total_orders, SUM(discount_amount) as total_discount, SUM(grand_total) as total_revenue')
            ->groupBy('coupon_code')
            ->orderByDesc('total_orders')
            ->take(20)
            ->get();

        $coupons = $this->companyCoupons()
            ->latest()
            ->take(20)
            ->get();

        $transactions = (clone $couponTransactions)->latest()->take(20)->get();

        return view('backend.reports.promos', compact('start', 'end', 'summary', 'topCoupons', 'coupons', 'transactions'));
    }

    public function returns(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidOrders = $this->companyTransactions()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn(DB::raw('LOWER(status)'), $this->paidStatuses())
            ->count();

        $returnBase = $this->companyReturnRequests()->whereBetween('created_at', [$start, $end]);
        $returnsCount = (clone $returnBase)->count();

        $summary = [
            'total_requests' => $returnsCount,
            'open_requests' => (clone $returnBase)->whereNotIn('status', ['selesai', 'completed', 'ditolak', 'rejected'])->count(),
            'refund_total' => (clone $returnBase)->sum('refund_amount'),
            'return_rate' => $paidOrders > 0 ? round(($returnsCount / $paidOrders) * 100, 1) : 0,
        ];

        $statusBreakdown = (clone $returnBase)
            ->selectRaw('LOWER(status) as status_key, COUNT(*) as total_requests, SUM(refund_amount) as refund_total')
            ->groupBy('status_key')
            ->orderByDesc('total_requests')
            ->get();

        $recentReturns = (clone $returnBase)
            ->with(['user', 'transaction'])
            ->latest()
            ->take(20)
            ->get();

        return view('backend.reports.returns', compact('start', 'end', 'summary', 'statusBreakdown', 'recentReturns'));
    }

    private function companyTransactions(): Builder
    {
        return Transaction::query()->where('company_id', $this->activeCompanyId());
    }

    private function companyTransactionDetails(): Builder
    {
        return TransactionDetail::query()
            ->whereHas('transaction', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyProducts(): Builder
    {
        return Product::query()->where('company_id', $this->activeCompanyId());
    }

    private function companyProductVariants(): Builder
    {
        return ProductVariant::query()
            ->whereHas('product', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyStockMovements(): Builder
    {
        return StockMovement::query()
            ->whereHas('productVariant.product', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyProductReviews(): Builder
    {
        return TransactionProductReview::query()
            ->whereHas('transaction', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyReturnRequests(): Builder
    {
        return ReturnRequest::query()
            ->whereHas('transaction', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyCoupons(): Builder
    {
        return Coupon::query()->where('company_id', $this->activeCompanyId());
    }

    private function companyCarts(): Builder
    {
        return Cart::query()
            ->whereHas('productVariant.product', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyWishlists(): Builder
    {
        return Wishlist::query()
            ->whereHas('product', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyCustomers(): Builder
    {
        return User::query()
            ->where('role', 'user')
            ->whereHas('transactions', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function companyNewsletterSubscribers(): Builder
    {
        return NewsletterSubscriber::query()
            ->where(function ($query) {
                $query
                    ->whereIn('email', User::query()
                        ->select('email')
                        ->whereHas('transactions', fn ($transaction) => $transaction->where('company_id', $this->activeCompanyId())))
                    ->orWhereIn('email', Transaction::query()
                        ->select('manual_customer_email')
                        ->where('company_id', $this->activeCompanyId())
                        ->whereNotNull('manual_customer_email'));
            });
    }

    private function paidStatuses(): array
    {
        return ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];
    }

    private function percentageChange(float|int $current, float|int $previous): ?float
    {
        if ((float) $previous === 0.0) {
            return (float) $current === 0.0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function exportTransactions($transactions, $start, $end)
    {
        $filename = 'report-transaksi-'.$start->format('Ymd').'-'.$end->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Invoice', 'Customer', 'Status', 'Metode Pembayaran', 'Subtotal', 'Diskon', 'Ongkir', 'Grand Total']);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    optional($tx->created_at)->format('Y-m-d H:i:s'),
                    $tx->invoice_no,
                    $tx->user?->name ?? '-',
                    $tx->status,
                    $tx->payment_method ?: $tx->payment_type,
                    (int) $tx->subtotal_amount,
                    (int) $tx->discount_amount,
                    (int) $tx->shipping_cost,
                    (int) $tx->grand_total,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
