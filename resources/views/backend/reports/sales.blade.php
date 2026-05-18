@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
    @php
        $money = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $number = fn($value) => number_format((float) $value, 0, ',', '.');
        $statusLabel = fn($status) => ucwords(str_replace(['_', '-'], ' ', (string) $status));
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
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Sales Report</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Periode, omzet, transaksi, status, top produk, dan export transaksi.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                @if (auth()->user()?->hasAdminPermission('reports.sales.export'))
                    <a href="{{ route('reports.sales', ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString(), 'export' => 'csv']) }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">Export CSV</a>
                @endif
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5 mb-6">
            @foreach ([['Omzet', $money($summary['revenue']), 'text-blue-600'], ['Order Paid', $number($summary['orders']), 'text-slate-800 dark:text-white'], ['Item Terjual', $number($summary['items_sold']), 'text-slate-800 dark:text-white'], ['AOV', $money($summary['average_order_value']), 'text-slate-800 dark:text-white'], ['Diskon', $money($summary['discount']), 'text-emerald-600']] as $card)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                    <p class="mt-2 text-xl font-extrabold {{ $card[2] }}">{{ $card[1] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="xl:col-span-2 rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Transaksi Paid Terbaru</div>
                <div class="overflow-x-auto">
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
            </section>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h2 class="font-extrabold text-slate-800 dark:text-white mb-4">Status Transaksi</h2>
                    <div class="space-y-3">
                        @forelse ($statusBreakdown as $row)
                            <div class="flex items-center justify-between">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass($row->status_key) }}">{{ $statusLabel($row->status_key) }}</span>
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $number($row->total_orders) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada data.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <h2 class="font-extrabold text-slate-800 dark:text-white mb-4">Produk Terlaris</h2>
                    <div class="space-y-3">
                        @forelse ($topProducts as $product)
                            <div class="rounded-2xl border border-slate-100 dark:border-slate-700 p-3">
                                <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 line-clamp-1">{{ $product->product_name }}</p>
                                <p class="text-xs text-slate-500">{{ $number($product->total_qty) }} terjual / {{ $money($product->total_revenue) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada data.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </main>
@endsection
