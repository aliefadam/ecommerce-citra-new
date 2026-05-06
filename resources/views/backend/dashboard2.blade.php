@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Welcome back, Admin! Here's what's happening today. - {{ now()->format('d M Y') }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Periode transaksi: {{ $dashboardPeriod['label'] }}</p>
            </div>

            <div
                class="inline-flex self-start rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1 shadow-sm overflow-x-auto">
                @foreach ($dashboardPeriodOptions as $periodKey => $periodLabel)
                    <a href="{{ url()->current() }}?period={{ $periodKey }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $dashboardPeriod['key'] === $periodKey ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        {{ $periodLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ============ ACTION CARDS ============ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @foreach ($actionCards as $actionCard)
                @php
                    $actionColor = $actionCard['color'] ?? 'slate';
                    $actionStyle = match ($actionColor) {
                        'amber'
                            => [
                                'iconBg' => 'bg-amber-100 dark:bg-amber-900/40',
                                'iconText' => 'text-amber-600 dark:text-amber-400',
                                'link' => 'text-amber-700 dark:text-amber-400',
                            ],
                        'red'
                            => [
                                'iconBg' => 'bg-red-100 dark:bg-red-900/40',
                                'iconText' => 'text-red-600 dark:text-red-400',
                                'link' => 'text-red-700 dark:text-red-400',
                            ],
                        default
                            => [
                                'iconBg' => 'bg-slate-100 dark:bg-slate-700',
                                'iconText' => 'text-slate-600 dark:text-slate-300',
                                'link' => 'text-slate-700 dark:text-slate-300',
                            ],
                    };
                @endphp

                <a href="{{ $actionCard['url'] }}"
                    class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 hover:-translate-y-0.5 hover:shadow-md transition-all">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                {{ $actionCard['label'] }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $actionCard['description'] }}</p>
                        </div>
                        <span class="w-9 h-9 rounded-xl {{ $actionStyle['iconBg'] }} flex items-center justify-center">
                            <i data-lucide="{{ $actionCard['icon'] }}" class="w-4 h-4 {{ $actionStyle['iconText'] }}"></i>
                        </span>
                    </div>
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <div>
                            <div class="text-2xl font-bold text-slate-800 dark:text-white">
                                {{ number_format($actionCard['count'], 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-400 mt-1">
                                {{ $actionCard['amount'] === null ? 'item' : 'transaksi' }}
                            </div>
                        </div>
                        <div class="text-right">
                            @if ($actionCard['amount'] !== null)
                                <div class="text-xs text-slate-400 mb-1">Nominal</div>
                                <div class="text-sm font-bold text-slate-800 dark:text-white">
                                    Rp {{ number_format($actionCard['amount'], 0, ',', '.') }}
                                </div>
                            @else
                                <div class="text-xs font-semibold {{ $actionStyle['link'] }}">Cek stok</div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- ============ STAT CARDS ============ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

            {{-- Total Revenue --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total
                        Revenue</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                    </span>
                </div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white mb-1">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                @php
                    $revDiff =
                        $lastMonthRevenue > 0
                            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
                            : ($thisMonthRevenue > 0
                                ? 100
                                : 0);
                @endphp
                <div class="flex items-center gap-1 text-xs">
                    @if ($revDiff >= 0)
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">▲
                            {{ number_format($revDiff, 1) }}%</span>
                    @else
                        <span class="text-red-500 font-semibold">▼ {{ number_format(abs($revDiff), 1) }}%</span>
                    @endif
                    <span class="text-slate-400">vs bulan lalu</span>
                </div>
                <div class="text-xs text-slate-400 mt-1">Bulan ini: Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}
                </div>
            </div>

            {{-- Total Orders --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total
                        Orders</span>
                    <span class="w-9 h-9 rounded-xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-violet-600 dark:text-violet-400"></i>
                    </span>
                </div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white mb-1">{{ number_format($totalOrders) }}</div>
                @if ($pendingOrders > 0)
                    <div
                        class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-semibold px-2 py-0.5 rounded-full">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $pendingOrders }} perlu diproses
                    </div>
                @else
                    <div class="text-xs text-slate-400">Tidak ada pesanan menunggu</div>
                @endif
            </div>

            {{-- Total Users --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total
                        Pelanggan</span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    </span>
                </div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white mb-1">{{ number_format($totalUsers) }}</div>
                <div class="text-xs text-slate-400">Pengguna terdaftar</div>
            </div>

            {{-- Total Products --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total
                        Produk</span>
                    <span class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center">
                        <i data-lucide="package" class="w-4 h-4 text-orange-600 dark:text-orange-400"></i>
                    </span>
                </div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white mb-1">{{ number_format($totalProducts) }}
                </div>
                <div class="text-xs text-slate-400">Produk aktif di katalog</div>
            </div>
        </div>

        {{-- ============ ORDER STATUS CARDS ============ --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Status Transaksi</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Jumlah pesanan dan nominal berdasarkan progres pesanan</p>
                </div>
                <a href="{{ route('transactions.index') }}"
                    class="hidden sm:inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    Kelola transaksi
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach ($orderStatusCards as $statusCard)
                    @php
                        $statusColor = $statusCard['color'] ?? 'slate';
                        $cardStyle = match ($statusColor) {
                            'amber'
                                => [
                                    'iconBg' => 'bg-amber-100 dark:bg-amber-900/40',
                                    'iconText' => 'text-amber-600 dark:text-amber-400',
                                    'pill' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    'bar' => 'bg-amber-500',
                                ],
                            'blue'
                                => [
                                    'iconBg' => 'bg-blue-100 dark:bg-blue-900/40',
                                    'iconText' => 'text-blue-600 dark:text-blue-400',
                                    'pill' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'bar' => 'bg-blue-500',
                                ],
                            'violet'
                                => [
                                    'iconBg' => 'bg-violet-100 dark:bg-violet-900/40',
                                    'iconText' => 'text-violet-600 dark:text-violet-400',
                                    'pill' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                                    'bar' => 'bg-violet-500',
                                ],
                            'emerald'
                                => [
                                    'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/40',
                                    'iconText' => 'text-emerald-600 dark:text-emerald-400',
                                    'pill' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'bar' => 'bg-emerald-500',
                                ],
                            default
                                => [
                                    'iconBg' => 'bg-slate-100 dark:bg-slate-700',
                                    'iconText' => 'text-slate-600 dark:text-slate-300',
                                    'pill' => 'bg-slate-50 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                    'bar' => 'bg-slate-500',
                                ],
                        };
                    @endphp

                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden relative">
                        <div class="absolute inset-x-0 top-0 h-1 {{ $cardStyle['bar'] }}"></div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                    {{ $statusCard['label'] }}
                                </p>
                                <p class="text-xs text-slate-400 mt-1">{{ $statusCard['description'] }}</p>
                            </div>
                            <span class="w-9 h-9 rounded-xl {{ $cardStyle['iconBg'] }} flex items-center justify-center">
                                <i data-lucide="{{ $statusCard['icon'] }}" class="w-4 h-4 {{ $cardStyle['iconText'] }}"></i>
                            </span>
                        </div>

                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <div class="text-2xl font-bold text-slate-800 dark:text-white">
                                    {{ number_format($statusCard['count'], 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-slate-400 mt-1">transaksi</div>
                            </div>
                            <div class="text-right min-w-0">
                                <div class="text-xs text-slate-400 mb-1">Nominal</div>
                                <div class="text-sm font-bold text-slate-800 dark:text-white break-words">
                                    Rp {{ number_format($statusCard['amount'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold {{ $cardStyle['pill'] }}">
                            <i data-lucide="receipt-text" class="w-3.5 h-3.5"></i>
                            Total nilai pesanan
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="mt-4 bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white text-sm">Nominal Transaksi per Status</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Perbandingan total nilai pesanan dari tiap progres admin</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-amber-500"></i>
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 items-center">
                    <div class="lg:col-span-2 h-64 relative">
                        <canvas id="statusAmountChart"></canvas>
                        <div id="statusAmountEmpty"
                            class="hidden absolute inset-0 items-center justify-center text-center text-xs text-slate-400 px-6">
                            Belum ada nominal transaksi pada status ini.
                        </div>
                    </div>
                    <div class="lg:col-span-3 space-y-2" id="statusAmountLegend"></div>
                </div>
            </div>
        </div>

        {{-- ============ CHARTS ROW 1 ============ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

            {{-- Line Chart: Revenue 12 bulan --}}
            <div
                class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white text-sm">Revenue 12 Bulan Terakhir</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Dari transaksi dengan status paid/process/kirim/selesai</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <i data-lucide="bar-chart-2" class="w-4 h-4 text-blue-500"></i>
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Doughnut Chart: Order by status --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white text-sm">Status Order</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Distribusi semua transaksi</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-violet-500"></i>
                    </span>
                </div>
                <div class="h-48">
                    <canvas id="statusChart"></canvas>
                </div>
                {{-- Legend --}}
                <div class="mt-3 space-y-1.5" id="statusLegend"></div>
            </div>
        </div>

        {{-- ============ CHART TOP PRODUK ============ --}}
        @if ($topProducts->count())
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white text-sm">Top 5 Produk Terlaris</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Berdasarkan total kuantitas terjual</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i data-lucide="award" class="w-4 h-4 text-emerald-500"></i>
                    </span>
                </div>
                <div class="h-52">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        @endif

        {{-- ============ TABLES ROW ============ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Tabel Transaksi Terbaru --}}
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800 dark:text-white text-sm">Transaksi Terbaru</h3>
                    <a href="{{ route('transactions.index') }}"
                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Lihat semua →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Invoice</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Customer</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Total</th>
                                <th
                                    class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($recentTransactions as $trx)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 text-xs font-mono text-slate-600 dark:text-slate-300">
                                        {{ $trx->invoice_no ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300">
                                        {{ $trx->user?->name ?? 'Guest' }}</td>
                                    <td class="px-4 py-3 text-xs text-right font-semibold text-slate-800 dark:text-white">Rp
                                        {{ number_format($trx->grand_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $statusClass = match ($trx->status) {
                                                'selesai'
                                                    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                                                'kirim'
                                                    => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
                                                'process'
                                                    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                                'paid'
                                                    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                                                'batal'
                                                    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                                default
                                                    => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                                            };
                                        @endphp
                                        <span
                                            class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $trx->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-xs text-slate-400">Belum ada
                                        transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel Stok Rendah --}}
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800 dark:text-white text-sm">Produk Stok Rendah <span
                            class="text-red-500">(&lt; 10)</span></h3>
                    <a href="{{ route('products.index') }}"
                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Kelola produk →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Produk</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    SKU</th>
                                <th
                                    class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($lowStockProducts as $pv)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="text-xs font-medium text-slate-800 dark:text-white">
                                            {{ Str::limit($pv->product?->name ?? '-', 28) }}</div>
                                        <div class="text-xs text-slate-400">{{ $pv->variant?->name }}
                                            {{ $pv->variant?->value }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs font-mono text-slate-500 dark:text-slate-400">
                                        {{ $pv->sku ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($pv->stock == 0)
                                            <span
                                                class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">Habis</span>
                                        @else
                                            <span
                                                class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">{{ $pv->stock }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-400"></i>
                                            <span class="text-xs text-slate-400">Semua stok aman</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    {{-- ============ CHARTS JS ============ --}}
    <script>
        (function() {
            const isDark = () => document.documentElement.classList.contains('dark');
            const gridColor = () => isDark() ? 'rgba(148,163,184,0.12)' : 'rgba(0,0,0,0.06)';
            const textColor = () => isDark() ? '#94a3b8' : '#64748b';
            const rupiah = (value) => 'Rp ' + Number(value || 0).toLocaleString('id-ID');

            // --- Build 12-month labels & revenue data ---
            const revenueRaw = @json($revenueByMonth);
            const months12Labels = [];
            const months12Data = [];
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for (let i = 11; i >= 0; i--) {
                const d = new Date();
                d.setDate(1);
                d.setMonth(d.getMonth() - i);
                const key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
                months12Labels.push(monthNames[d.getMonth()] + ' ' + d.getFullYear());
                months12Data.push(parseFloat(revenueRaw[key] ?? 0));
            }

            // --- Chart 1: Revenue Line ---
            const ctxRevenue = document.getElementById('revenueChart');
            if (ctxRevenue) {
                new Chart(ctxRevenue, {
                    type: 'line',
                    data: {
                        labels: months12Labels,
                        datasets: [{
                            label: 'Revenue',
                            data: months12Data,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            borderWidth: 2,
                            pointBackgroundColor: '#3b82f6',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor()
                                },
                                ticks: {
                                    color: textColor(),
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    color: gridColor()
                                },
                                ticks: {
                                    color: textColor(),
                                    font: {
                                        size: 10
                                    },
                                    callback: v => 'Rp ' + (v >= 1000000 ? (v / 1000000).toFixed(1) + 'jt' : v
                                        .toLocaleString('id-ID')),
                                }
                            }
                        }
                    }
                });
            }

            // --- Chart 2: Transaction amount by operational status ---
            const statusCardRaw = @json($orderStatusCards);
            const statusAmountLabels = statusCardRaw.map(item => item.label);
            const statusAmountData = statusCardRaw.map(item => Number(item.amount || 0));
            const statusAmountCounts = statusCardRaw.map(item => Number(item.count || 0));
            const statusAmountColors = ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981'];
            const statusAmountTotal = statusAmountData.reduce((total, amount) => total + amount, 0);
            const ctxStatusAmount = document.getElementById('statusAmountChart');
            const statusAmountEmpty = document.getElementById('statusAmountEmpty');

            if (ctxStatusAmount && statusAmountTotal > 0) {
                new Chart(ctxStatusAmount, {
                    type: 'pie',
                    data: {
                        labels: statusAmountLabels,
                        datasets: [{
                            data: statusAmountData,
                            backgroundColor: statusAmountColors,
                            borderColor: isDark() ? '#1e293b' : '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        const amount = Number(ctx.parsed || 0);
                                        const pct = statusAmountTotal > 0 ? ((amount / statusAmountTotal) * 100)
                                            .toFixed(1) : 0;
                                        return `${ctx.label}: ${rupiah(amount)} (${pct}%)`;
                                    },
                                    afterLabel: ctx => `${statusAmountCounts[ctx.dataIndex]} transaksi`,
                                }
                            }
                        }
                    }
                });
            } else if (statusAmountEmpty) {
                statusAmountEmpty.classList.remove('hidden');
                statusAmountEmpty.classList.add('flex');
            }

            const statusAmountLegend = document.getElementById('statusAmountLegend');
            if (statusAmountLegend) {
                statusCardRaw.forEach((item, i) => {
                    const amount = Number(item.amount || 0);
                    const pct = statusAmountTotal > 0 ? ((amount / statusAmountTotal) * 100).toFixed(1) : '0.0';
                    statusAmountLegend.innerHTML += `<div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 dark:border-slate-700 px-3 py-2.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${statusAmountColors[i]}"></span>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">${item.label}</div>
                                <div class="text-xs text-slate-400">${Number(item.count || 0).toLocaleString('id-ID')} transaksi</div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-xs font-bold text-slate-800 dark:text-white">${rupiah(amount)}</div>
                            <div class="text-xs text-slate-400">${pct}%</div>
                        </div>
                    </div>`;
                });
            }

            // --- Chart 3: Status Doughnut ---
            const statusRaw = @json($ordersByStatus);
            const statusLabels = Object.keys(statusRaw);
            const statusData = Object.values(statusRaw).map(Number);
            const statusColors = {
                pending: '#94a3b8',
                paid: '#f59e0b',
                settlement: '#22c55e',
                capture: '#16a34a',
                process: '#3b82f6',
                kirim: '#8b5cf6',
                shipped: '#7c3aed',
                selesai: '#10b981',
                completed: '#059669',
                batal: '#ef4444',
                cancel: '#ef4444',
                cancelled: '#ef4444',
                dibatalkan: '#ef4444',
                deny: '#dc2626',
                failed: '#dc2626',
                expire: '#f97316',
            };
            const statusLabelMap = {
                pending: 'Pending',
                paid: 'Paid',
                settlement: 'Settlement',
                capture: 'Capture',
                process: 'Diproses',
                kirim: 'Dikirim',
                shipped: 'Dikirim',
                selesai: 'Selesai',
                completed: 'Selesai',
                batal: 'Dibatalkan',
                cancel: 'Dibatalkan',
                cancelled: 'Dibatalkan',
                dibatalkan: 'Dibatalkan',
                deny: 'Ditolak',
                failed: 'Gagal',
                expire: 'Kedaluwarsa',
            };
            const normalizedStatusLabels = statusLabels.map((label) => String(label || '').toLowerCase().trim());
            const doughnutColors = normalizedStatusLabels.map((label) => statusColors[label] ?? '#cbd5e1');

            const ctxStatus = document.getElementById('statusChart');
            if (ctxStatus && statusLabels.length) {
                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: doughnutColors,
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                // Manual legend
                const legend = document.getElementById('statusLegend');
                if (legend) {
                    statusLabels.forEach((label, i) => {
                        const total = statusData.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? ((statusData[i] / total) * 100).toFixed(1) : 0;
                        const normalized = String(label || '').toLowerCase().trim();
                        const displayLabel = statusLabelMap[normalized] ?? label;
                        legend.innerHTML += `<div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${doughnutColors[i]}"></span>
                        <span class="text-xs text-slate-600 dark:text-slate-400 capitalize">${displayLabel}</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">${statusData[i]} <span class="text-slate-400 font-normal">(${pct}%)</span></span>
                </div>`;
                    });
                }
            }

            // --- Chart 4: Top Products Horizontal Bar ---
            const topRaw = @json($topProducts);
            const ctxTop = document.getElementById('topProductsChart');
            if (ctxTop && topRaw.length) {
                new Chart(ctxTop, {
                    type: 'bar',
                    data: {
                        labels: topRaw.map(p => p.product_name.length > 30 ? p.product_name.substring(0, 30) +
                            '…' : p.product_name),
                        datasets: [{
                            label: 'Terjual (qty)',
                            data: topRaw.map(p => p.total_qty),
                            backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'],
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    afterLabel: ctx => 'Revenue: Rp ' + Number(topRaw[ctx.dataIndex]
                                        .total_revenue).toLocaleString('id-ID'),
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor()
                                },
                                ticks: {
                                    color: textColor(),
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor(),
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                        }
                    }
                });
            }
        })();
    </script>
@endsection
