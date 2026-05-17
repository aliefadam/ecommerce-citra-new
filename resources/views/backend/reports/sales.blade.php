@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php
        $money = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $number = fn($value) => number_format((float) $value, 0, ',', '.');
        $statusLabel = function ($status) {
            return ucwords(str_replace(['_', '-'], ' ', (string) $status));
        };
        $statusClass = function ($status) {
            $status = strtolower((string) $status);
            return match (true) {
                in_array($status, ['paid', 'settlement', 'capture', 'selesai', 'completed', 'delivered'], true) => 'bg-emerald-50 text-emerald-700',
                in_array($status, ['process', 'processing', 'kirim', 'shipping', 'shipped'], true) => 'bg-blue-50 text-blue-700',
                in_array($status, ['pending', 'menunggu_verifikasi'], true) => 'bg-amber-50 text-amber-700',
                in_array($status, ['cancel', 'expire', 'deny', 'failure', 'dibatalkan'], true) => 'bg-red-50 text-red-700',
                default => 'bg-slate-100 text-slate-700',
            };
        };
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Report Center</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Reports</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Satu wadah untuk memantau omzet, stok, performa produk, pembayaran, dan operasional pesanan.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('reports.sales', ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString(), 'export' => 'csv']) }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">Export CSV</a>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Omzet</p>
                <p class="mt-2 text-2xl font-extrabold text-blue-600">{{ $money($summary['revenue']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $number($summary['orders']) }} transaksi paid</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Average Order</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-800 dark:text-white">{{ $money($summary['average_order_value']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $number($summary['items_sold']) }} item terjual</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nilai Stok</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-800 dark:text-white">{{ $money($stockSummary['inventory_value']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $number($stockSummary['total_variants']) }} varian produk</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Queue Operasional</p>
                <p class="mt-2 text-2xl font-extrabold text-amber-600">{{ $number($fulfillmentSummary['active_pipeline']) }}</p>
                <p class="mt-1 text-xs text-slate-500">Pesanan perlu dimonitor</p>
            </div>
        </div>

        <div class="grid gap-6">
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="border-b border-slate-100 dark:border-slate-700 px-5 py-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-extrabold text-slate-800 dark:text-white">Sales Report</h2>
                        <p class="text-xs text-slate-500">Periode, omzet, transaksi, status, top produk, dan export.</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-400">{{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</span>
                </div>

                <div class="grid gap-4 p-5 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="grid gap-3 sm:grid-cols-4">
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-700/40 p-4">
                                <p class="text-xs text-slate-400">Total Order</p>
                                <p class="mt-1 text-xl font-bold text-slate-800 dark:text-white">{{ $number($summary['orders']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-700/40 p-4">
                                <p class="text-xs text-slate-400">Diskon</p>
                                <p class="mt-1 text-xl font-bold text-emerald-600">{{ $money($summary['discount']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-700/40 p-4">
                                <p class="text-xs text-slate-400">Ongkir</p>
                                <p class="mt-1 text-xl font-bold text-slate-800 dark:text-white">{{ $money($summary['shipping']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-700/40 p-4">
                                <p class="text-xs text-slate-400">Semua Order</p>
                                <p class="mt-1 text-xl font-bold text-slate-800 dark:text-white">{{ $number($summary['all_orders']) }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-700">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th class="text-left px-4 py-3 text-slate-500">Invoice</th>
                                        <th class="text-left px-4 py-3 text-slate-500">Customer</th>
                                        <th class="text-left px-4 py-3 text-slate-500">Status</th>
                                        <th class="text-right px-4 py-3 text-slate-500">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @forelse ($transactions as $tx)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $tx->invoice_no }}</td>
                                            <td class="px-4 py-3 text-slate-500">{{ $tx->user?->name ?? '-' }}</td>
                                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass($tx->status) }}">{{ $statusLabel($tx->status) }}</span></td>
                                            <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">{{ $money($tx->grand_total) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="py-10 text-center text-slate-400">Tidak ada transaksi.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-4">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-3">Status Transaksi</h3>
                            <div class="space-y-3">
                                @forelse ($statusBreakdown as $row)
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass($row->status_key) }}">{{ $statusLabel($row->status_key) }}</span>
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $number($row->total_orders) }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Belum ada data.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-4">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-3">Omzet Harian</h3>
                            <div class="space-y-3">
                                @forelse ($dailyRevenue->take(7) as $day)
                                    <div>
                                        <div class="flex items-center justify-between text-xs text-slate-500">
                                            <span>{{ \Carbon\Carbon::parse($day->sales_date)->format('d M') }}</span>
                                            <span>{{ $money($day->total_revenue) }}</span>
                                        </div>
                                        <div class="mt-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full bg-blue-500" style="width: {{ min(100, (($day->total_revenue ?: 0) / max(1, $dailyRevenue->max('total_revenue'))) * 100) }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Belum ada data.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                    <div class="border-b border-slate-100 dark:border-slate-700 px-5 py-4">
                        <h2 class="font-extrabold text-slate-800 dark:text-white">Stock Report</h2>
                        <p class="text-xs text-slate-500">Low stock, out of stock, mutasi stok, dan nilai stok.</p>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-amber-50 p-4">
                                <p class="text-xs text-amber-700">Low Stock</p>
                                <p class="mt-1 text-xl font-bold text-amber-700">{{ $number($stockSummary['low_stock']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs text-red-700">Habis</p>
                                <p class="mt-1 text-xl font-bold text-red-700">{{ $number($stockSummary['out_of_stock']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs text-blue-700">Varian</p>
                                <p class="mt-1 text-xl font-bold text-blue-700">{{ $number($stockSummary['total_variants']) }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Perlu Restock</h3>
                                <div class="space-y-3">
                                    @forelse ($lowStockVariants as $variant)
                                        <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 line-clamp-1">{{ $variant->product?->name ?? 'Produk' }}</p>
                                            <p class="text-xs text-slate-400 line-clamp-1">{{ $variant->sku ?: $variant->attributeSummary() }}</p>
                                            <p class="mt-1 text-xs font-bold {{ $variant->stock <= 0 ? 'text-red-600' : 'text-amber-600' }}">Stok {{ $number($variant->stock) }}</p>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-400">Tidak ada low stock.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Mutasi Terbaru</h3>
                                <div class="space-y-3">
                                    @forelse ($stockMovements as $movement)
                                        <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 line-clamp-1">{{ $movement->productVariant?->product?->name ?? '-' }}</p>
                                                <span class="text-xs font-bold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-red-600' }}">{{ $movement->type === 'in' ? '+' : '-' }}{{ $number($movement->quantity) }}</span>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-400">{{ $movement->created_at?->format('d M Y H:i') }} / {{ $movement->source ?: '-' }}</p>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-400">Belum ada mutasi pada periode ini.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                    <div class="border-b border-slate-100 dark:border-slate-700 px-5 py-4">
                        <h2 class="font-extrabold text-slate-800 dark:text-white">Product Performance</h2>
                        <p class="text-xs text-slate-500">Terlaris, wishlist, rating, dan produk lambat.</p>
                    </div>
                    <div class="grid gap-4 p-5 lg:grid-cols-2">
                        <div>
                            <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Produk Terlaris</h3>
                            <div class="space-y-3">
                                @forelse ($topProducts->take(5) as $product)
                                    <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                        <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 line-clamp-1">{{ $product->product_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $number($product->total_qty) }} terjual / {{ $money($product->total_revenue) }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Belum ada data.</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Wishlist Tertinggi</h3>
                            <div class="space-y-3">
                                @forelse ($topWishlisted as $product)
                                    <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                        <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 line-clamp-1">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $number($product->wishlists_count) }} wishlist</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Belum ada data.</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Rating Terbaik</h3>
                            <div class="space-y-3">
                                @forelse ($topRatedProducts as $product)
                                    <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                        <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 line-clamp-1">{{ $product->product_name }}</p>
                                        <p class="text-xs text-slate-500">{{ number_format((float) $product->avg_rating, 1) }} / 5 dari {{ $number($product->total_reviews) }} ulasan</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Belum ada ulasan.</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Produk Lambat</h3>
                            <div class="space-y-3">
                                @forelse ($slowProducts as $product)
                                    <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                        <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 line-clamp-1">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-500">Stok {{ $number($product->stock_total ?? 0) }} / belum terjual di periode ini</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">Tidak ada produk lambat.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="border-b border-slate-100 dark:border-slate-700 px-5 py-4">
                    <h2 class="font-extrabold text-slate-800 dark:text-white">Payment & Fulfillment Report</h2>
                    <p class="text-xs text-slate-500">Metode pembayaran, pending payment, manual verification, dan queue fulfillment.</p>
                </div>
                <div class="grid gap-5 p-5 lg:grid-cols-3">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-amber-50 p-4">
                                <p class="text-xs text-amber-700">Pending</p>
                                <p class="mt-1 text-xl font-bold text-amber-700">{{ $number($paymentSummary['pending_payment']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs text-blue-700">Manual Verify</p>
                                <p class="mt-1 text-xl font-bold text-blue-700">{{ $number($paymentSummary['manual_waiting']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs text-emerald-700">Paid Period</p>
                                <p class="mt-1 text-xl font-bold text-emerald-700">{{ $number($paymentSummary['paid_period']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs text-red-700">Failed Period</p>
                                <p class="mt-1 text-xl font-bold text-red-700">{{ $number($paymentSummary['failed_period']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Metode Pembayaran</h3>
                        <div class="space-y-3">
                            @forelse ($paymentMethods as $method)
                                <div class="flex items-center justify-between rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $method->method_label }}</p>
                                        <p class="text-xs text-slate-500">{{ $money($method->total_revenue) }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $number($method->total_orders) }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">Belum ada data.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">Queue Operasional</h3>
                        <div class="space-y-3">
                            @forelse ($operationQueue as $tx)
                                <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $tx->invoice_no }}</p>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass($tx->status) }}">{{ $statusLabel($tx->status) }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $tx->user?->name ?? '-' }} / {{ $money($tx->grand_total) }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">Tidak ada queue aktif.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection
