@extends('layouts.app')

@section('title', 'Return dan Refund')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Return dan Refund</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pengajuan retur, refund, status penyelesaian, dan return rate.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            @foreach ([
                ['Total Pengajuan', number_format($summary['total_requests'], 0, ',', '.')],
                ['Masih Aktif', number_format($summary['open_requests'], 0, ',', '.')],
                ['Nilai Refund', 'Rp ' . number_format($summary['refund_total'], 0, ',', '.')],
                ['Return Rate', $summary['return_rate'] . '%'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                    <p class="mt-2 text-xl font-extrabold text-rose-600">{{ $card[1] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Status Return</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($statusBreakdown as $status)
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $status->status_key ?: 'unknown')) }}</p>
                                <p class="text-xs text-slate-500">Refund Rp {{ number_format($status->refund_total ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <p class="font-extrabold text-slate-800 dark:text-white">{{ number_format($status->total_requests, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada return pada periode ini.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Return Terbaru</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($recentReturns as $return)
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $return->request_no }}</p>
                                <span class="rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">{{ $return->status }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $return->user?->name ?? 'Guest' }} · {{ $return->transaction?->invoice_no ?? '-' }} · Rp {{ number_format($return->refund_amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada return.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
@endsection
