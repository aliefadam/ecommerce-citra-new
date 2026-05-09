<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $paidStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];

        $base = Transaction::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn(DB::raw('LOWER(status)'), $paidStatuses);

        $summary = [
            'orders' => (clone $base)->count(),
            'revenue' => (clone $base)->sum('grand_total'),
            'discount' => (clone $base)->sum('discount_amount'),
            'shipping' => (clone $base)->sum('shipping_cost'),
        ];

        $transactions = (clone $base)->with('user')->latest()->get();

        $topProducts = TransactionDetail::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', fn($q) => $q->whereBetween('created_at', [$start, $end])->whereIn(DB::raw('LOWER(status)'), $paidStatuses))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return view('backend.reports.sales', compact('summary', 'transactions', 'topProducts', 'start', 'end'));
    }
}
