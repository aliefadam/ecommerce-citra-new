@extends('layouts.app')

@section('title', 'Detail Quotation')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">

    @php
        $statusChip = match ($quotation->status) {
            'draft' => ['bg-slate-100 text-slate-600', 'file'],
            'sent' => ['bg-blue-100 text-blue-700', 'send'],
            'accepted', 'partially_converted' => ['bg-emerald-100 text-emerald-700', 'check-circle'],
            'rejected' => ['bg-red-100 text-red-700', 'x-circle'],
            'expired' => ['bg-red-100 text-red-700', 'clock'],
            'closed' => ['bg-violet-100 text-violet-700', 'lock'],
            default => ['bg-slate-100 text-slate-600', 'circle'],
        };
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('quotations.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:underline">
                <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali ke quotations
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $quotation->quotation_no }}</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $statusChip[0] }}">
                    <i data-lucide="{{ $statusChip[1] }}" class="h-3.5 w-3.5"></i>
                    {{ ucfirst(str_replace('_', ' ', $quotation->status)) }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                Dibuat {{ $quotation->created_at->format('d M Y, H:i') }} oleh {{ $quotation->createdByAdmin?->name ?? '-' }}
                &bull; Berlaku hingga {{ $quotation->valid_until->format('d M Y') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('quotations.print', $quotation) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-600 px-3.5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                <i data-lucide="printer" class="h-4 w-4"></i> Cetak
            </a>
            @if (!$quotation->isReadOnly())
                <a href="{{ route('quotations.edit', $quotation) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 dark:border-blue-700 px-3.5 py-2 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                    <i data-lucide="pencil" class="h-4 w-4"></i> Edit
                </a>
            @endif
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

    {{-- Aksi status --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @if ($quotation->status === 'draft')
            <form method="POST" action="{{ route('quotations.send', $quotation) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i data-lucide="send" class="h-4 w-4"></i> Kirim ke Customer
                </button>
            </form>
        @endif

        @if (in_array($quotation->status, ['draft', 'sent'], true))
            <form method="POST" action="{{ route('quotations.update-status', $quotation) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="to_status" value="accepted">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i data-lucide="check"></i> Tandai Diterima
                </button>
            </form>
            <form method="POST" action="{{ route('quotations.update-status', $quotation) }}" onsubmit="return confirm('Tandai quotation ini ditolak customer?')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="to_status" value="rejected">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                    <i data-lucide="x"></i> Tandai Ditolak
                </button>
            </form>
        @endif

        @if (in_array($quotation->status, ['accepted', 'partially_converted'], true) && !$quotation->isExpiredByDate())
            <a href="{{ route('quotations.convert-form', $quotation) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                <i data-lucide="arrow-right-left"></i> Convert to Sales Order
            </a>
        @endif

        @if (!$quotation->isReadOnly())
            <form method="POST" action="{{ route('quotations.close', $quotation) }}" onsubmit="return confirm('Tutup manual quotation ini? Sisa qty yang belum terpakai tidak akan bisa dikonversi lagi.')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    <i data-lucide="lock"></i> Tutup Manual
                </button>
            </form>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Customer --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-3">Customer</h2>
                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $quotation->customerName() }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $quotation->user?->email ?? $quotation->manual_customer_email ?? '-' }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $quotation->manual_customer_phone ?? $quotation->user?->phone_number ?? '-' }}</p>
            </div>

            {{-- Items --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Produk</h2>
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
                            @foreach ($quotation->details as $detail)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $detail->product_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $detail->variant_name }}{{ $detail->item_note ? ' — ' . $detail->item_note : '' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sales Orders terkait --}}
            @if ($quotation->salesOrders->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-slate-800 dark:text-white">Sales Orders</h2>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($quotation->salesOrders as $salesOrder)
                            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $salesOrder->sales_order_no }}</p>
                                    <p class="text-xs text-slate-400">{{ $salesOrder->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</p>
                                    <span class="text-xs font-semibold {{ $salesOrder->status === 'cancelled' ? 'text-red-500' : 'text-emerald-600' }}">{{ ucfirst(str_replace('_', ' ', $salesOrder->status)) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Status History --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-4">Riwayat Status</h2>
                <div class="space-y-4">
                    @foreach ($quotation->statusHistories as $history)
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

        {{-- Ringkasan --}}
        <aside class="lg:sticky lg:top-24 lg:h-fit">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($quotation->subtotal_amount, 0, ',', '.') }}</span>
                    </div>
                    @if ($quotation->discount_amount > 0)
                        <div class="flex items-center justify-between px-5 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Diskon</span>
                            <span class="font-semibold text-emerald-600">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                        <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
                @if ($quotation->note)
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Catatan</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $quotation->note }}</p>
                    </div>
                @endif
                @if ($quotation->status === 'closed')
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Ditutup</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ optional($quotation->closed_at)->format('d M Y, H:i') }} oleh {{ $quotation->closedByAdmin?->name ?? '-' }}</p>
                        @if ($quotation->close_reason)
                            <p class="mt-1 text-xs text-slate-400">{{ $quotation->close_reason }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </aside>
    </div>
</main>
@endsection
