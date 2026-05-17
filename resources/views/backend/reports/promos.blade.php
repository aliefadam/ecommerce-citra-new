@extends('layouts.app')

@section('title', 'Promo dan Coupon')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Promo dan Coupon</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau efektivitas kupon, nilai diskon, dan transaksi promo.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            @foreach ([
                ['Kupon Aktif', number_format($summary['active_coupons'], 0, ',', '.')],
                ['Order Pakai Kupon', number_format($summary['coupon_orders'], 0, ',', '.')],
                ['Total Diskon', 'Rp ' . number_format($summary['discount_total'], 0, ',', '.')],
                ['Poin Diredeem', number_format($summary['redeemed_points'], 0, ',', '.')],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                    <p class="mt-2 text-xl font-extrabold text-amber-600">{{ $card[1] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Top Coupon</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($topCoupons as $coupon)
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $coupon->coupon_code }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($coupon->total_orders, 0, ',', '.') }} order · diskon Rp {{ number_format($coupon->total_discount, 0, ',', '.') }}</p>
                            </div>
                            <p class="font-extrabold text-blue-600">Rp {{ number_format($coupon->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada transaksi kupon.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Daftar Coupon</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($coupons as $coupon)
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $coupon->code }}</p>
                                <p class="text-xs text-slate-500">{{ $coupon->name }} · dipakai {{ number_format($coupon->used_count, 0, ',', '.') }}{{ $coupon->usage_limit ? '/' . number_format($coupon->usage_limit, 0, ',', '.') : '' }}</p>
                            </div>
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $coupon->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada kupon.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
@endsection
