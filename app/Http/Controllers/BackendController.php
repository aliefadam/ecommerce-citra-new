<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use App\Models\StoreSetting;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BackendController extends Controller
{
    private function dashboardPeriod(Request $request): array
    {
        $period = $request->query('period', 'month');
        $allowedPeriods = ['today', '7days', 'month', 'all'];

        if (!in_array($period, $allowedPeriods, true)) {
            $period = 'month';
        }

        return match ($period) {
            'today' => [
                'key' => $period,
                'label' => 'Hari Ini',
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            '7days' => [
                'key' => $period,
                'label' => '7 Hari Terakhir',
                'start' => now()->subDays(6)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'all' => [
                'key' => $period,
                'label' => 'Semua Waktu',
                'start' => null,
                'end' => null,
            ],
            default => [
                'key' => 'month',
                'label' => 'Bulan Ini',
                'start' => now()->startOfMonth(),
                'end' => now()->endOfDay(),
            ],
        };
    }

    private function applyPeriod($query, array $period)
    {
        if ($period['start'] && $period['end']) {
            $query->whereBetween('created_at', [$period['start'], $period['end']]);
        }

        return $query;
    }

    private function orderStatusCards(array $period)
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

        return collect($cards)->map(function ($card) use ($period) {
            $summary = $this->applyPeriod(Transaction::query(), $period)
                ->whereIn(DB::raw('LOWER(status)'), $card['statuses'])
                ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(grand_total), 0) as total_amount')
                ->first();

            $card['count'] = (int) ($summary->total_count ?? 0);
            $card['amount'] = (float) ($summary->total_amount ?? 0);

            return $card;
        });
    }

    private function actionCards(array $period)
    {
        $waitingProcess = $this->applyPeriod(Transaction::query(), $period)
            ->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'capture']);
        $pendingPayment = $this->applyPeriod(Transaction::query(), $period)
            ->whereIn(DB::raw('LOWER(status)'), ['pending', 'menunggu']);
        $lowStockCount = ProductVariant::whereColumn('stock', '<=', 'low_stock_threshold')->count();

        return [
            [
                'label' => 'Perlu Diproses',
                'description' => 'Pesanan sudah dibayar',
                'count' => (clone $waitingProcess)->count(),
                'amount' => (clone $waitingProcess)->sum('grand_total'),
                'icon' => 'package-check',
                'color' => 'amber',
                'url' => route('transactions.index'),
            ],
            [
                'label' => 'Menunggu Pembayaran',
                'description' => 'Checkout belum lunas',
                'count' => (clone $pendingPayment)->count(),
                'amount' => (clone $pendingPayment)->sum('grand_total'),
                'icon' => 'credit-card',
                'color' => 'slate',
                'url' => route('transactions.index'),
            ],
            [
                'label' => 'Stok Rendah',
                'description' => 'Varian stok di bawah 10',
                'count' => $lowStockCount,
                'amount' => null,
                'icon' => 'triangle-alert',
                'color' => 'red',
                'url' => route('stocks.index'),
            ],
        ];
    }

    private function dashboardData(Request $request): array
    {
        $period = $this->dashboardPeriod($request);
        $paidStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];

        $totalRevenue = $this->applyPeriod(Transaction::query(), $period)
            ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
            ->sum('grand_total');
        $totalOrders = $this->applyPeriod(Transaction::query(), $period)->count();
        $totalUsers    = User::where('role', 'user')->count();
        $totalProducts = Product::count();

        $thisMonth        = now()->startOfMonth();
        $lastMonthStart   = now()->subMonth()->startOfMonth();
        $thisMonthRevenue = Transaction::whereIn(DB::raw('LOWER(status)'), $paidStatuses)
            ->whereBetween('created_at', [$thisMonth, now()])->sum('grand_total');
        $lastMonthRevenue = Transaction::whereIn(DB::raw('LOWER(status)'), $paidStatuses)
            ->whereBetween('created_at', [$lastMonthStart, $thisMonth])->sum('grand_total');

        $pendingOrders = $this->applyPeriod(Transaction::query(), $period)
            ->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'capture'])
            ->count();

        $revenueByMonth = Transaction::whereIn(DB::raw('LOWER(status)'), $paidStatuses)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(grand_total) as total")
            ->groupBy('month')->orderBy('month')
            ->pluck('total', 'month');

        $ordersByStatus = $this->applyPeriod(Transaction::query(), $period)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $topProducts = TransactionDetail::selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', function ($query) use ($period, $paidStatuses) {
                $this->applyPeriod($query, $period)
                    ->whereIn(DB::raw('LOWER(status)'), $paidStatuses);
            })
            ->groupBy('product_name')->orderByDesc('total_qty')->take(5)->get();

        $recentTransactions = $this->applyPeriod(Transaction::with('user'), $period)
            ->latest()->take(7)->get();

        $lowStockProducts = ProductVariant::with(['product', 'variant'])
            ->whereColumn('stock', '<=', 'low_stock_threshold')->orderBy('stock')->take(7)->get();

        $orderStatusCards = $this->orderStatusCards($period);
        $actionCards = $this->actionCards($period);
        $dashboardPeriod = $period;
        $dashboardPeriodOptions = [
            'today' => 'Hari Ini',
            '7days' => '7 Hari',
            'month' => 'Bulan Ini',
            'all' => 'Semua',
        ];

        return compact(
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
            'orderStatusCards',
            'actionCards',
            'dashboardPeriod',
            'dashboardPeriodOptions'
        );
    }

    public function index(Request $request)
    {
        return view('backend.dashboard2', $this->dashboardData($request));
    }

    public function dashboard2(Request $request)
    {
        return view('backend.dashboard2', $this->dashboardData($request));
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
        return view('backend.settings', [
            'storeSettings' => StoreSetting::values(),
            'location' => StoreLocation::query()
                ->where('is_active', true)
                ->latest('id')
                ->first(),
        ]);
    }

    public function updateSettings(Request $request, ImageOptimizer $imageOptimizer)
    {
        $section = (string) $request->input('section', 'store');

        if ($section === 'manual_payment') {
            $validated = $request->validate([
                'manual_payment_bank_name' => ['required', 'string', 'max:80'],
                'manual_payment_account_number' => ['required', 'string', 'max:80'],
                'manual_payment_account_name' => ['required', 'string', 'max:120'],
                'manual_payment_instruction' => ['nullable', 'string', 'max:1000'],
            ]);

            StoreSetting::setMany($validated);

            return redirect()->route('pages.settings', ['tab' => 'payment'])->with('success', 'Setting pembayaran manual berhasil disimpan.');
        }

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
            'store_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $values = [
            'store_name' => $validated['store_name'],
        ];

        if ($request->hasFile('store_logo')) {
            $currentLogo = (string) StoreSetting::values()['store_logo_path'];
            $values['store_logo_path'] = $imageOptimizer->storeWebp($request->file('store_logo'), 'store', 512, 512, 82);
            $imageOptimizer->deletePublicFile($currentLogo);
        }

        StoreSetting::setMany($values);

        return redirect()->route('pages.settings', ['tab' => 'store'])->with('success', 'Profil toko berhasil disimpan.');
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
