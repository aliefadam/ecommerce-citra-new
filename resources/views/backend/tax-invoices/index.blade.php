@extends('layouts.app')

@section('title', 'Faktur Pajak')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Faktur Pajak</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Queue permintaan faktur pajak customer untuk diproses finance.</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <i data-lucide="receipt" class="h-4 w-4"></i>
                Transactions
            </a>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Requested', 'value' => $summary['requested'] ?? 0, 'class' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800'],
                ['label' => 'Processing', 'value' => $summary['processing'] ?? 0, 'class' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800'],
                ['label' => 'Issued / Sent', 'value' => $summary['issued'] ?? 0, 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800'],
                ['label' => 'Rejected', 'value' => $summary['rejected'] ?? 0, 'class' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800'],
            ] as $card)
                <div class="rounded-2xl border {{ $card['class'] }} p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider opacity-75">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-extrabold">{{ number_format((int) $card['value'], 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
            <form method="GET" action="{{ route('tax-invoices.index') }}" class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                        <input name="q" value="{{ $filters['q'] ?? '' }}" type="text" placeholder="Cari invoice, customer, NPWP..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                    <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\TransactionTaxInvoice::STATUSES as $status)
                            @continue($status === \App\Models\TransactionTaxInvoice::STATUS_NOT_REQUESTED)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <input name="transaction_date" value="{{ $filters['transaction_date'] ?? '' }}" type="date"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    <input name="request_date" value="{{ $filters['request_date'] ?? '' }}" type="date"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                        <a href="{{ route('tax-invoices.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Reset</a>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead class="bg-slate-50 text-left dark:bg-slate-700/50">
                        <tr class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-4 py-3">Transaksi</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Nilai</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Wajib Pajak</th>
                            <th class="px-4 py-3">Request</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($taxInvoices as $taxInvoice)
                            @php
                                $tx = $taxInvoice->transaction;
                                $statusClass = match ($taxInvoice->status) {
                                    'requested' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                                    'processing' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
                                    'issued', 'sent' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800',
                                    'rejected' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
                                    default => 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
                                };
                            @endphp
                            <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                                <td class="px-4 py-4">
                                    <p class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ $tx?->invoice_no ?? '-' }}</p>
                                    <p class="text-xs text-slate-400">{{ $tx?->created_at?->format('d M Y H:i') ?? '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Order: {{ $tx?->order_id ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $tx?->customerDisplayName() ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ $tx?->customerDisplayEmail() ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">Rp {{ number_format((int) ($tx?->grand_total ?? 0), 0, ',', '.') }}</p>
                                    <p class="text-xs {{ (int) ($tx?->tax_amount ?? 0) > 0 ? 'text-slate-500' : 'text-amber-600' }}">
                                        PPN: Rp {{ number_format((int) ($tx?->tax_amount ?? 0), 0, ',', '.') }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($taxInvoice->status)) }}</span>
                                    <p class="mt-2 text-xs text-slate-500">Transaksi: {{ $tx?->status ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->taxpayer_name }}</p>
                                    <p class="font-mono text-xs text-slate-500">{{ $taxInvoice->masked_taxpayer_number }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-slate-700 dark:text-slate-200">{{ $taxInvoice->requested_at?->format('d M Y H:i') ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">Oleh {{ $taxInvoice->requestedByUser?->name ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('tax-invoices.show', $taxInvoice) }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-blue-700">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400">Belum ada permintaan faktur pajak.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-700">
                {{ $taxInvoices->links() }}
            </div>
        </div>
    </main>
@endsection
