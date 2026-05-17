<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\TransactionProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];
        $pendingStatuses = ['pending', 'menunggu_verifikasi'];
        $fulfillmentStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped'];

        $periodBase = Transaction::query()
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
            'items_sold' => TransactionDetail::query()
                ->whereHas('transaction', fn($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
                ->sum('quantity'),
            'average_order_value' => $orders > 0 ? round($revenue / $orders) : 0,
            'all_orders' => (clone $periodBase)->count(),
        ];

        $transactions = (clone $base)->with('user')->latest()->take(12)->get();

        if ($request->query('export') === 'csv') {
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

        $topProducts = TransactionDetail::query()
            ->selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', fn($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $stockSummary = [
            'total_variants' => ProductVariant::query()->count(),
            'low_stock' => ProductVariant::query()->whereRaw('stock > 0 AND stock <= COALESCE(low_stock_threshold, 5)')->count(),
            'out_of_stock' => ProductVariant::query()->where('stock', '<=', 0)->count(),
            'inventory_value' => ProductVariant::query()->selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0,
        ];

        $lowStockVariants = ProductVariant::query()
            ->with(['product', 'variant', 'attributeValues.definition'])
            ->whereRaw('stock <= COALESCE(low_stock_threshold, 5)')
            ->orderBy('stock')
            ->take(10)
            ->get();

        $stockMovements = StockMovement::query()
            ->with(['productVariant.product', 'productVariant.variant'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->take(10)
            ->get();

        $topWishlisted = Product::query()
            ->withCount('wishlists')
            ->where('status', 'active')
            ->has('wishlists')
            ->orderByDesc('wishlists_count')
            ->take(8)
            ->get();

        $slowProducts = Product::query()
            ->withSum('productVariants as stock_total', 'stock')
            ->where('status', 'active')
            ->whereDoesntHave('transactionDetails', fn($q) => $q->whereHas('transaction', fn($tx) => $tx->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses)))
            ->orderByDesc('stock_total')
            ->take(8)
            ->get();

        $topRatedProducts = TransactionProductReview::query()
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
            'pending_payment' => Transaction::query()->whereIn(DB::raw('LOWER(status)'), $pendingStatuses)->count(),
            'manual_waiting' => Transaction::query()->where('payment_type', 'manual_transfer')->where('status', 'menunggu_verifikasi')->count(),
            'paid_period' => $orders,
            'failed_period' => (clone $periodBase)->whereIn(DB::raw('LOWER(status)'), ['deny', 'failure', 'expire', 'cancel', 'dibatalkan'])->count(),
        ];

        $fulfillmentSummary = [
            'need_process' => Transaction::query()->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'capture'])->count(),
            'in_process' => Transaction::query()->whereIn(DB::raw('LOWER(status)'), ['process', 'processing'])->count(),
            'shipping' => Transaction::query()->whereIn(DB::raw('LOWER(status)'), ['kirim', 'shipping', 'shipped'])->count(),
            'active_pipeline' => Transaction::query()->whereIn(DB::raw('LOWER(status)'), $fulfillmentStatuses)->count(),
        ];

        $operationQueue = Transaction::query()
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

    private function exportTransactions($transactions, $start, $end)
    {
        $filename = 'report-transaksi-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.csv';

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
