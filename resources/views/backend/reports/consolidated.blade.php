@extends('layouts.app')

@section('title', 'Laporan Konsolidasi Perusahaan')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">&larr; Report Center</a>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-800 dark:text-white sm:text-3xl">Laporan Konsolidasi</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Gabungan dan breakdown seluruh perusahaan aktif. Halaman ini memerlukan permission khusus.</p>
        </div>
        <form class="flex flex-wrap gap-2">
            <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <div data-kpi-grid class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-5">
        @foreach ([
            ['Total omzet', 'Rp '.number_format($totals['revenue'], 0, ',', '.')],
            ['Semua order', number_format($totals['orders'], 0, ',', '.')],
            ['Order paid', number_format($totals['paid_orders'], 0, ',', '.')],
            ['Pending', number_format($totals['pending'], 0, ',', '.')],
            ['Total diskon', 'Rp '.number_format($totals['discount'], 0, ',', '.')],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $card[0] }}</p>
                <p class="mt-2 break-words text-xl font-extrabold text-slate-800 dark:text-white">{{ $card[1] }}</p>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
        <div class="border-b border-slate-100 px-5 py-4 font-extrabold text-slate-800 dark:border-slate-700 dark:text-white">Breakdown per perusahaan</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                    <tr><th class="px-5 py-3">Perusahaan</th><th class="px-5 py-3">Order</th><th class="px-5 py-3">Paid</th><th class="px-5 py-3">Pending</th><th class="px-5 py-3">Diskon</th><th class="px-5 py-3">Omzet</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($companies as $company)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-800 dark:text-white">{{ $company['name'] }}</td>
                            <td class="px-5 py-4">{{ number_format($company['orders'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4">{{ number_format($company['paid_orders'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4">{{ number_format($company['pending'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4">Rp {{ number_format($company['discount'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-bold text-blue-700">Rp {{ number_format($company['revenue'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada perusahaan aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>
@endsection
