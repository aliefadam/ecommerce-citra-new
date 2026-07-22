@extends('layouts.app')

@section('title', 'Invoice')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Invoice</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tagihan final dari Surat Jalan yang sudah terkirim, atau langsung dari Sales Order.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('b2b-invoices.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
            <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-200">
                <option value="">Semua Status</option>
                @foreach (['issued', 'partially_paid', 'paid', 'cancelled'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ $filterStatus === $statusOption ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                @endforeach
            </select>
        </form>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 text-slate-500">Nomor</th>
                            <th class="text-left px-4 py-3 text-slate-500">Customer</th>
                            <th class="text-left px-4 py-3 text-slate-500">Jatuh Tempo</th>
                            <th class="text-right px-4 py-3 text-slate-500">Grand Total</th>
                            <th class="text-right px-4 py-3 text-slate-500">Outstanding</th>
                            <th class="text-left px-4 py-3 text-slate-500">Status</th>
                            <th class="text-left px-4 py-3 text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($b2bInvoices as $invoice)
                            @php
                                $statusColor = match ($invoice->status) {
                                    'issued' => 'bg-blue-100 text-blue-700',
                                    'partially_paid' => 'bg-amber-100 text-amber-700',
                                    'paid' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    <a href="{{ route('b2b-invoices.show', $invoice) }}" class="hover:text-blue-600">{{ $invoice->b2b_invoice_no }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $invoice->customerName() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    {{ optional($invoice->due_date)->format('d M Y') }}
                                    @if ($invoice->isOverdue())
                                        <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Overdue</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('b2b-invoices.show', $invoice) }}" class="text-blue-600 hover:underline text-xs font-semibold">Lihat Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada Invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($b2bInvoices->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
                    {{ $b2bInvoices->links() }}
                </div>
            @endif
        </div>
    </main>
@endsection
