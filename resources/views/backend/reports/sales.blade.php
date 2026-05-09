@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Sales Report</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ringkasan penjualan berdasarkan periode.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            </form>
        </div>

        <div class="grid sm:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
                <p class="text-xs text-slate-400">Order</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($summary['orders'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
                <p class="text-xs text-slate-400">Omzet</p>
                <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
                <p class="text-xs text-slate-400">Diskon</p>
                <p class="text-2xl font-bold text-emerald-600">Rp {{ number_format($summary['discount'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
                <p class="text-xs text-slate-400">Ongkir</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-white">Rp {{ number_format($summary['shipping'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 font-bold text-slate-800 dark:text-white">Transaksi</div>
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
                                    <td class="px-4 py-3 text-slate-500">{{ $tx->status }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-12 text-center text-slate-400">Tidak ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 font-bold text-slate-800 dark:text-white">Produk Terlaris</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($topProducts as $product)
                        <div class="p-4">
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $product->product_name }}</p>
                            <p class="text-xs text-slate-500">{{ (int) $product->total_qty }} terjual / Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <div class="p-6 text-sm text-slate-400 text-center">Belum ada data.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
@endsection
