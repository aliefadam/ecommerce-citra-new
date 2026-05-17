@extends('layouts.app')

@section('title', 'Payment dan Fulfillment')

@section('content')
    @php
        $money = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $number = fn($value) => number_format((float) $value, 0, ',', '.');
        $statusLabel = fn($status) => ucwords(str_replace(['_', '-'], ' ', (string) $status));
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Payment dan Fulfillment</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Metode pembayaran, pending payment, verifikasi manual, dan queue pesanan.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            @foreach ([['Pending Payment', $paymentSummary['pending_payment'], 'text-amber-600'], ['Manual Verify', $paymentSummary['manual_waiting'], 'text-blue-600'], ['Paid Period', $paymentSummary['paid_period'], 'text-emerald-600'], ['Failed Period', $paymentSummary['failed_period'], 'text-red-600']] as $card)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                    <p class="mt-2 text-xl font-extrabold {{ $card[2] }}">{{ $number($card[1]) }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Metode Pembayaran</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($paymentMethods as $method)
                        <div class="p-4 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $method->method_label }}</p>
                                <p class="text-xs text-slate-500">{{ $money($method->total_revenue) }}</p>
                            </div>
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $number($method->total_orders) }} order</p>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-slate-400">Belum ada data.</div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Queue Operasional</div>
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
                            @forelse ($operationQueue as $tx)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $tx->invoice_no }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $tx->user?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $statusLabel($tx->status) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">{{ $money($tx->grand_total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-slate-400">Tidak ada queue aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $operationQueue->links() }}</div>
            </section>
        </div>
    </main>
@endsection
