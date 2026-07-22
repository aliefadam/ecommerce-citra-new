@extends('layouts.app')

@section('title', 'Proforma Invoices')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Proforma Invoices</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tagihan DP/pembayaran di muka dari Sales Order.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('proforma-invoices.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
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
                            <th class="text-left px-4 py-3 text-slate-500">Sales Order</th>
                            <th class="text-right px-4 py-3 text-slate-500">Grand Total</th>
                            <th class="text-right px-4 py-3 text-slate-500">Outstanding</th>
                            <th class="text-left px-4 py-3 text-slate-500">Status</th>
                            <th class="text-left px-4 py-3 text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($proformaInvoices as $pi)
                            @php
                                $statusColor = match ($pi->status) {
                                    'issued' => 'bg-blue-100 text-blue-700',
                                    'partially_paid' => 'bg-amber-100 text-amber-700',
                                    'paid' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    <a href="{{ route('proforma-invoices.show', $pi) }}" class="hover:text-blue-600">{{ $pi->proforma_invoice_no }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $pi->customerName() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <a href="{{ route('sales-orders.show', $pi->sales_order_id) }}" class="hover:text-blue-600 hover:underline">{{ $pi->salesOrder?->sales_order_no }}</a>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($pi->grand_total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($pi->outstanding_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $pi->status)) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @include('backend.partials.row-actions-dropdown', ['actions' => [
                                        ['label' => 'Buka Detail', 'url' => route('proforma-invoices.show', $pi), 'icon' => 'eye'],
                                        ['label' => 'Cetak', 'url' => route('proforma-invoices.print', $pi), 'icon' => 'printer', 'target' => '_blank'],
                                    ]])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada Proforma Invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($proformaInvoices->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
                    {{ $proformaInvoices->links() }}
                </div>
            @endif
        </div>
    </main>
@endsection
