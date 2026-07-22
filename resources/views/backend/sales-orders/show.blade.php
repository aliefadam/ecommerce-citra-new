@extends('layouts.app')

@section('title', 'Detail Sales Order')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">

    @php
        $statusChip = match ($salesOrder->status) {
            'confirmed' => ['bg-blue-100 text-blue-700', 'check-circle'],
            'partially_fulfilled' => ['bg-amber-100 text-amber-700', 'truck'],
            'fulfilled' => ['bg-emerald-100 text-emerald-700', 'package-check'],
            'cancelled' => ['bg-red-100 text-red-700', 'x-circle'],
            default => ['bg-slate-100 text-slate-600', 'circle'],
        };
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('sales-orders.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:underline">
                <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali ke sales orders
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $salesOrder->sales_order_no }}</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $statusChip[0] }}">
                    <i data-lucide="{{ $statusChip[1] }}" class="h-3.5 w-3.5"></i>
                    {{ ucfirst(str_replace('_', ' ', $salesOrder->status)) }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                Dibuat {{ $salesOrder->created_at->format('d M Y, H:i') }} oleh {{ $salesOrder->createdByAdmin?->name ?? '-' }}
                @if ($canSeePricing)
                    @if ($salesOrder->quotation_id)
                        &bull; Dari Quotation
                        <a href="{{ route('quotations.show', $salesOrder->quotation_id) }}" class="text-blue-600 hover:underline">{{ $salesOrder->quotation?->quotation_no }}</a>
                    @else
                        &bull; Dibuat langsung tanpa Quotation
                    @endif
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('sales-orders.print', $salesOrder) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-600 px-3.5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                <i data-lucide="printer" class="h-4 w-4"></i> Cetak
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
            <i data-lucide="check-circle-2" class="h-4 w-4 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/20 dark:text-red-400">
            <i data-lucide="alert-circle" class="h-4 w-4 shrink-0"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap gap-2">
        @if ($salesOrder->canBeCancelled())
            <form method="POST" action="{{ route('sales-orders.cancel', $salesOrder) }}" onsubmit="return confirm('Batalkan Sales Order ini?{{ $salesOrder->quotation_id ? ' Sisa qty akan dikembalikan ke Quotation asal.' : '' }}')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                    <i data-lucide="ban"></i> Batalkan Sales Order
                </button>
            </form>
        @endif

        @if ($canSeePricing && $salesOrder->status !== 'cancelled' && !$salesOrder->hasActiveProformaInvoice())
            <a href="{{ route('proforma-invoices.create-form', $salesOrder) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                <i data-lucide="file-clock"></i> Terbitkan Proforma Invoice
            </a>
        @endif

        @if ($salesOrder->status !== 'cancelled' && $salesOrder->details->sum(fn ($d) => $d->remainingToReserve()) > 0)
            <a href="{{ route('delivery-notes.create-form', $salesOrder) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                <i data-lucide="truck"></i> Buat Pengiriman
            </a>
        @endif

        @if ($canSeePricing && $salesOrder->uninvoicedDeliveryNotes()->isNotEmpty())
            <a href="{{ route('b2b-invoices.create-form', $salesOrder) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                <i data-lucide="receipt-text"></i> Buat Invoice dari Surat Jalan
            </a>
        @endif

        @if ($canSeePricing && $salesOrder->status !== 'cancelled')
            <a href="{{ route('b2b-invoices.create-direct-form', $salesOrder) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">
                <i data-lucide="zap"></i> Buat Invoice Langsung
            </a>
        @endif
    </div>

    {{-- Surat Jalan terkait --}}
    @if ($salesOrder->deliveryNotes->isNotEmpty())
        <div class="mb-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h2 class="font-bold text-slate-800 dark:text-white">Surat Jalan</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($salesOrder->deliveryNotes as $dn)
                    @php
                        $dnColor = match ($dn->status) {
                            'draft' => 'text-slate-500',
                            'shipped' => 'text-blue-600',
                            'delivered' => 'text-emerald-600',
                            'cancelled' => 'text-red-500',
                            default => 'text-slate-500',
                        };
                    @endphp
                    <a href="{{ route('delivery-notes.show', $dn) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $dn->delivery_note_no }}</p>
                            <p class="text-xs text-slate-400">{{ $dn->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <span class="text-xs font-semibold {{ $dnColor }}">{{ ucfirst($dn->status) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($canSeePricing && $salesOrder->proformaInvoices->isNotEmpty())
        <div class="mb-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h2 class="font-bold text-slate-800 dark:text-white">Proforma Invoice</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($salesOrder->proformaInvoices as $pi)
                    <a href="{{ route('proforma-invoices.show', $pi) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $pi->proforma_invoice_no }}</p>
                            <p class="text-xs text-slate-400">Outstanding: Rp {{ number_format($pi->outstanding_amount, 0, ',', '.') }} / Rp {{ number_format($pi->grand_total, 0, ',', '.') }}</p>
                        </div>
                        <span class="text-xs font-semibold {{ $pi->status === 'cancelled' ? 'text-red-500' : ($pi->status === 'paid' ? 'text-emerald-600' : 'text-amber-600') }}">{{ ucfirst(str_replace('_', ' ', $pi->status)) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($canSeePricing && $salesOrder->b2bInvoices->isNotEmpty())
        <div class="mb-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h2 class="font-bold text-slate-800 dark:text-white">Invoice</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($salesOrder->b2bInvoices as $invoice)
                    <a href="{{ route('b2b-invoices.show', $invoice) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $invoice->b2b_invoice_no }}
                                @if ($invoice->source === \App\Models\B2bInvoice::SOURCE_DIRECT)
                                    <span class="ml-1 rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">Langsung</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-400">Outstanding: Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }} / Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</p>
                        </div>
                        <span class="text-xs font-semibold {{ $invoice->status === 'cancelled' ? 'text-red-500' : ($invoice->status === 'paid' ? 'text-emerald-600' : 'text-amber-600') }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}{{ $invoice->isOverdue() ? ' — Overdue' : '' }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Customer --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-3">Customer</h2>
                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $salesOrder->customerName() }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $salesOrder->user?->email ?? $salesOrder->manual_customer_email ?? '-' }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $salesOrder->manual_customer_phone ?? $salesOrder->user?->phone_number ?? '-' }}</p>
            </div>

            {{-- Items with fulfillment progress --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Produk & Progres Pengiriman</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-slate-500">Produk</th>
                                <th class="text-right px-4 py-3 text-slate-500">Qty Dipesan</th>
                                <th class="text-right px-4 py-3 text-slate-500">Sudah Dikirim</th>
                                <th class="text-right px-4 py-3 text-slate-500">Sisa</th>
                                @if ($canSeePricing)
                                    <th class="text-right px-4 py-3 text-slate-500">Harga</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($salesOrder->details as $detail)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $detail->product_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $detail->variant_name }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $detail->quantityShipped() }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">{{ $detail->remainingToShip() }}</td>
                                    @if ($canSeePricing)
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Status History --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-4">Riwayat Status</h2>
                <div class="space-y-4">
                    @foreach ($salesOrder->statusHistories as $history)
                        <div class="flex gap-3">
                            <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    {{ $history->from_status ? ucfirst(str_replace('_', ' ', $history->from_status)) . ' → ' : '' }}{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ $history->created_at->format('d M Y, H:i') }}
                                    {{ $history->user ? '— ' . $history->user->name : '' }}
                                </p>
                                @if ($history->note)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $history->note }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="lg:sticky lg:top-24 lg:h-fit">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                @if ($canSeePricing)
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan</h2>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                        <div class="flex items-center justify-between px-5 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($salesOrder->subtotal_amount, 0, ',', '.') }}</span>
                        </div>
                        @include('backend.partials.financial-breakdown', ['document' => $salesOrder])
                        <div class="flex items-center justify-between px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                            <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @else
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-slate-800 dark:text-white">Info</h2>
                    </div>
                    <div class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">Data harga/komersial tidak ditampilkan untuk role ini.</div>
                @endif
                @if ($salesOrder->status === 'cancelled')
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Dibatalkan</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ optional($salesOrder->cancelled_at)->format('d M Y, H:i') }} oleh {{ $salesOrder->cancelledByAdmin?->name ?? '-' }}</p>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</main>
@endsection
