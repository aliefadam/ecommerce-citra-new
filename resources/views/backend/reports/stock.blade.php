@extends('layouts.app')

@section('title', 'Stock Report')

@section('content')
    @php
        $money = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $number = fn($value) => number_format((float) $value, 0, ',', '.');
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Stock Report</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Low stock, out of stock, mutasi stok, dan estimasi nilai persediaan.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter Mutasi</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            @foreach ([['Total Varian', $number($stockSummary['total_variants']), 'text-slate-800 dark:text-white'], ['Low Stock', $number($stockSummary['low_stock']), 'text-amber-600'], ['Stok Habis', $number($stockSummary['out_of_stock']), 'text-red-600'], ['Nilai Stok', $money($stockSummary['inventory_value']), 'text-blue-600']] as $card)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                    <p class="mt-2 text-xl font-extrabold {{ $card[2] }}">{{ $card[1] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Saldo Stok per Varian</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-slate-500">Produk</th>
                                <th class="text-left px-4 py-3 text-slate-500">Varian</th>
                                <th class="text-right px-4 py-3 text-slate-500">Stok</th>
                                <th class="text-right px-4 py-3 text-slate-500">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($variants as $variant)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $variant->product?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $variant->sku ?: $variant->attributeSummary() }}</td>
                                    <td class="px-4 py-3 text-right font-bold {{ $variant->stock <= 0 ? 'text-red-600' : ($variant->stock <= ($variant->low_stock_threshold ?? 5) ? 'text-amber-600' : 'text-slate-800 dark:text-slate-200') }}">{{ $number($variant->stock) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $money($variant->stock * $variant->price) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-slate-400">Belum ada varian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $variants->links() }}</div>
            </section>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Kartu Mutasi Stok</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-slate-500">Tanggal</th>
                                <th class="text-left px-4 py-3 text-slate-500">Produk</th>
                                <th class="text-right px-4 py-3 text-slate-500">Qty</th>
                                <th class="text-left px-4 py-3 text-slate-500">Sumber</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($stockMovements as $movement)
                                <tr>
                                    <td class="px-4 py-3 text-slate-500">{{ $movement->created_at?->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $movement->productVariant?->product?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-bold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-red-600' }}">{{ $movement->type === 'in' ? '+' : '-' }}{{ $number($movement->quantity) }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $movement->source ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-slate-400">Tidak ada mutasi pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $stockMovements->links() }}</div>
            </section>
        </div>
    </main>
@endsection
