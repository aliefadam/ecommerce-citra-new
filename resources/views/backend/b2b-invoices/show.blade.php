@extends('layouts.app')

@section('title', 'Detail Invoice B2B')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">

    @php
        $statusChip = match ($b2bInvoice->status) {
            'issued' => ['bg-blue-100 text-blue-700', 'send'],
            'partially_paid' => ['bg-amber-100 text-amber-700', 'circle-dollar-sign'],
            'paid' => ['bg-emerald-100 text-emerald-700', 'check-circle'],
            'cancelled' => ['bg-red-100 text-red-700', 'x-circle'],
            default => ['bg-slate-100 text-slate-600', 'circle'],
        };
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('b2b-invoices.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:underline">
                <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali ke Invoice B2B
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $b2bInvoice->b2b_invoice_no }}</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $statusChip[0] }}">
                    <i data-lucide="{{ $statusChip[1] }}" class="h-3.5 w-3.5"></i>
                    {{ ucfirst(str_replace('_', ' ', $b2bInvoice->status)) }}
                </span>
                @if ($b2bInvoice->isOverdue())
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        <i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i> Overdue
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                Diterbitkan {{ optional($b2bInvoice->issued_at)->format('d M Y, H:i') }} oleh {{ $b2bInvoice->createdByAdmin?->name ?? '-' }}
                &bull; Jatuh tempo {{ optional($b2bInvoice->due_date)->format('d M Y') }}
                &bull; Dari Sales Order
                <a href="{{ route('sales-orders.show', $b2bInvoice->sales_order_id) }}" class="text-blue-600 hover:underline">{{ $b2bInvoice->salesOrder?->sales_order_no }}</a>
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('b2b-invoices.print', $b2bInvoice) }}" target="_blank"
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

    @if ($b2bInvoice->canBeCancelled())
        <div class="mb-6">
            <form method="POST" action="{{ route('b2b-invoices.cancel', $b2bInvoice) }}" onsubmit="return confirm('Batalkan Invoice B2B ini?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                    <i data-lucide="ban"></i> Batalkan
                </button>
            </form>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Customer --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-3">Customer</h2>
                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $b2bInvoice->customerName() }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $b2bInvoice->user?->email ?? $b2bInvoice->manual_customer_email ?? '-' }}</p>
            </div>

            {{-- Surat Jalan terkait --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Surat Jalan yang Ditagihkan</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($b2bInvoice->deliveryNotes as $dn)
                        <a href="{{ route('delivery-notes.show', $dn) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $dn->delivery_note_no }}</span>
                            <span class="text-xs text-slate-400">{{ ucfirst($dn->status) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Items --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Item Ditagihkan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-slate-500">Produk</th>
                                <th class="text-right px-4 py-3 text-slate-500">Qty</th>
                                <th class="text-right px-4 py-3 text-slate-500">Harga</th>
                                <th class="text-right px-4 py-3 text-slate-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($b2bInvoice->details as $detail)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $detail->product_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $detail->variant_name }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Riwayat Pembayaran --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Riwayat Pembayaran</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($b2bInvoice->documentPayments as $payment)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <p class="font-semibold text-slate-700 dark:text-slate-200">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                    @if ($payment->source === 'dp_credit')
                                        <span class="ml-1 text-xs font-semibold text-violet-600">Kredit DP Otomatis</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ $payment->payment_date->format('d M Y') }}
                                    {{ $payment->recordedByAdmin ? '— dicatat oleh ' . $payment->recordedByAdmin->name : '' }}
                                </p>
                                @if ($payment->note)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $payment->note }}</p>
                                @endif
                            </div>
                            @if ($payment->source === 'manual')
                                <form method="POST" action="{{ route('document-payments.destroy', $payment) }}" onsubmit="return confirm('Hapus baris pembayaran ini? Status akan dihitung ulang.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Hapus</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-slate-400">Belum ada pembayaran tercatat.</p>
                    @endforelse
                </div>

                @if ($b2bInvoice->status !== 'cancelled' && $b2bInvoice->outstanding_amount > 0)
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
                        <h3 class="font-semibold text-slate-700 dark:text-slate-200 mb-3 text-sm">Catat Pembayaran</h3>
                        <form method="POST" action="{{ route('b2b-invoices.record-payment', $b2bInvoice) }}" class="grid gap-3 sm:grid-cols-4">
                            @csrf
                            <div class="sm:col-span-1">
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Nominal</label>
                                <input type="number" name="amount" min="1" max="{{ $b2bInvoice->outstanding_amount }}" required
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Tanggal Bayar</label>
                                <input type="date" name="payment_date" required value="{{ now()->format('Y-m-d') }}"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="mb-1 block text-xs font-semibold text-slate-500">Catatan</label>
                                <input type="text" name="note" placeholder="Opsional"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <div class="sm:col-span-1 flex items-end">
                                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Simpan Pembayaran</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <aside class="lg:sticky lg:top-24 lg:h-fit">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan Piutang</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-slate-500 dark:text-slate-400">Grand Total</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($b2bInvoice->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-slate-500 dark:text-slate-400">Sudah Dibayar</span>
                        <span class="font-semibold text-emerald-600">Rp {{ number_format($b2bInvoice->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                        <span class="font-bold text-blue-700 dark:text-blue-400">Outstanding</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($b2bInvoice->outstanding_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</main>
@endsection
