<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;

class BackendController extends Controller
{
    public function index()
    {
        $paidStatuses = ['paid', 'process', 'kirim', 'selesai'];

        $totalRevenue  = Transaction::whereIn('status', $paidStatuses)->sum('grand_total');
        $totalOrders   = Transaction::count();
        $totalUsers    = User::where('role', 'user')->count();
        $totalProducts = Product::count();

        $thisMonth        = now()->startOfMonth();
        $lastMonthStart   = now()->subMonth()->startOfMonth();
        $thisMonthRevenue = Transaction::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$thisMonth, now()])->sum('grand_total');
        $lastMonthRevenue = Transaction::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$lastMonthStart, $thisMonth])->sum('grand_total');

        $pendingOrders = Transaction::where('status', 'paid')->count();

        $revenueByMonth = Transaction::whereIn('status', $paidStatuses)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(grand_total) as total")
            ->groupBy('month')->orderBy('month')
            ->pluck('total', 'month');

        $ordersByStatus = Transaction::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $topProducts = TransactionDetail::selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_name')->orderByDesc('total_qty')->take(5)->get();

        $recentTransactions = Transaction::with('user')->latest()->take(7)->get();

        $lowStockProducts = ProductVariant::with(['product', 'variant'])
            ->where('stock', '<', 10)->orderBy('stock')->take(7)->get();

        return view('backend.dashboard2', compact(
            'totalRevenue',
            'totalOrders',
            'totalUsers',
            'totalProducts',
            'thisMonthRevenue',
            'lastMonthRevenue',
            'pendingOrders',
            'revenueByMonth',
            'ordersByStatus',
            'topProducts',
            'recentTransactions',
            'lowStockProducts'
        ));
    }

    public function dashboard2()
    {
        $paidStatuses = ['paid', 'process', 'kirim', 'selesai'];

        $totalRevenue  = Transaction::whereIn('status', $paidStatuses)->sum('grand_total');
        $totalOrders   = Transaction::count();
        $totalUsers    = User::where('role', 'user')->count();
        $totalProducts = Product::count();

        $thisMonth        = now()->startOfMonth();
        $lastMonthStart   = now()->subMonth()->startOfMonth();
        $thisMonthRevenue = Transaction::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$thisMonth, now()])->sum('grand_total');
        $lastMonthRevenue = Transaction::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$lastMonthStart, $thisMonth])->sum('grand_total');

        $pendingOrders = Transaction::where('status', 'paid')->count();

        $revenueByMonth = Transaction::whereIn('status', $paidStatuses)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(grand_total) as total")
            ->groupBy('month')->orderBy('month')
            ->pluck('total', 'month');

        $ordersByStatus = Transaction::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $topProducts = TransactionDetail::selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_name')->orderByDesc('total_qty')->take(5)->get();

        $recentTransactions = Transaction::with('user')->latest()->take(7)->get();

        $lowStockProducts = ProductVariant::with(['product', 'variant'])
            ->where('stock', '<', 10)->orderBy('stock')->take(7)->get();

        return view('backend.dashboard2', compact(
            'totalRevenue',
            'totalOrders',
            'totalUsers',
            'totalProducts',
            'thisMonthRevenue',
            'lastMonthRevenue',
            'pendingOrders',
            'revenueByMonth',
            'ordersByStatus',
            'topProducts',
            'recentTransactions',
            'lowStockProducts'
        ));
    }

    public function charts()
    {
        return view('backend.charts');
    }

    public function components()
    {
        return view('backend.components');
    }

    public function datatables()
    {
        return view('backend.datatables');
    }

    public function settings()
    {
        return view('backend.settings');
    }
}
