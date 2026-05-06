<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BackendController extends Controller
{
    private function orderStatusCards()
    {
        $cards = [
            [
                'key' => 'waiting_process',
                'label' => 'Belum Diproses',
                'description' => 'Sudah checkout dan dibayar',
                'statuses' => ['paid', 'settlement', 'capture'],
                'icon' => 'clock',
                'color' => 'amber',
            ],
            [
                'key' => 'processing',
                'label' => 'Diproses',
                'description' => 'Sedang disiapkan admin',
                'statuses' => ['process', 'processing'],
                'icon' => 'package-check',
                'color' => 'blue',
            ],
            [
                'key' => 'shipping',
                'label' => 'Sedang Dikirim',
                'description' => 'Sudah memiliki status kirim',
                'statuses' => ['kirim', 'shipping', 'shipped'],
                'icon' => 'truck',
                'color' => 'violet',
            ],
            [
                'key' => 'completed',
                'label' => 'Selesai',
                'description' => 'Transaksi sudah selesai',
                'statuses' => ['selesai', 'completed', 'delivered'],
                'icon' => 'check-circle-2',
                'color' => 'emerald',
            ],
        ];

        return collect($cards)->map(function ($card) {
            $summary = Transaction::query()
                ->whereIn(DB::raw('LOWER(status)'), $card['statuses'])
                ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(grand_total), 0) as total_amount')
                ->first();

            $card['count'] = (int) ($summary->total_count ?? 0);
            $card['amount'] = (float) ($summary->total_amount ?? 0);

            return $card;
        });
    }

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

        $orderStatusCards = $this->orderStatusCards();

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
            'lowStockProducts',
            'orderStatusCards'
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

        $orderStatusCards = $this->orderStatusCards();

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
            'lowStockProducts',
            'orderStatusCards'
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

    public function changePassword()
    {
        return view('backend.change-password');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak valid.'])->withInput();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
