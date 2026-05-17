@extends('layouts.app')

@section('title', 'Product Performance')

@section('content')
    @php
        $money = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $number = fn($value) => number_format((float) $value, 0, ',', '.');
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Product Performance</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Produk terlaris, wishlist tertinggi, rating terbaik, dan produk lambat.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            </form>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            @foreach ([['Produk Terlaris', $topProducts, 'sold'], ['Wishlist Tertinggi', $topWishlisted, 'wishlist'], ['Rating Terbaik', $topRatedProducts, 'rating'], ['Produk Lambat', $slowProducts, 'slow']] as [$title, $items, $type])
                <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">{{ $title }}</div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($items as $item)
                            <div class="p-4">
                                <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $type === 'wishlist' || $type === 'slow' ? $item->name : $item->product_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($type === 'sold')
                                        {{ $number($item->total_qty) }} terjual / {{ $money($item->total_revenue) }}
                                    @elseif ($type === 'wishlist')
                                        {{ $number($item->wishlists_count) }} wishlist
                                    @elseif ($type === 'rating')
                                        {{ number_format((float) $item->avg_rating, 1) }} / 5 dari {{ $number($item->total_reviews) }} ulasan
                                    @else
                                        Stok {{ $number($item->stock_total ?? 0) }} / belum terjual di periode ini
                                    @endif
                                </p>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-400">Belum ada data.</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </main>
@endsection
