@extends('layouts.app')

@section('title', 'Customer Report')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Customer Report</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Akuisisi customer, repeat buyer, cart, wishlist, dan newsletter.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6 mb-6">
            @foreach ([
                ['Customer Baru', $summary['new_customers']],
                ['Pembeli Aktif', $summary['active_buyers']],
                ['Repeat Buyer', $summary['repeat_buyers']],
                ['User Cart', $summary['cart_users']],
                ['User Wishlist', $summary['wishlist_users']],
                ['Newsletter', $summary['newsletter']],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                    <p class="mt-2 text-xl font-extrabold text-blue-600">{{ number_format($card[1], 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Top Customer</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($topCustomers as $customer)
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $customer->name }}</p>
                                <p class="text-xs text-slate-500">{{ $customer->email }} · {{ number_format($customer->paid_orders_count, 0, ',', '.') }} order</p>
                            </div>
                            <p class="font-extrabold text-blue-600">Rp {{ number_format($customer->paid_revenue_sum ?? 0, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada pembeli pada periode ini.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Customer Terbaru</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($recentCustomers as $customer)
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $customer->name }}</p>
                                <p class="text-xs text-slate-500">{{ $customer->email }}</p>
                            </div>
                            <p class="text-xs text-slate-400">{{ optional($customer->created_at)->format('d M Y') }}</p>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada customer.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
@endsection
