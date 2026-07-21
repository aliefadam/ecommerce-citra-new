@extends('layouts.app')

@section('title', 'Detail Surat Jalan')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">

    @php
        $statusChip = match ($deliveryNote->status) {
            'draft' => ['bg-slate-100 text-slate-600', 'file'],
            'shipped' => ['bg-blue-100 text-blue-700', 'truck'],
            'delivered' => ['bg-emerald-100 text-emerald-700', 'package-check'],
            'cancelled' => ['bg-red-100 text-red-700', 'x-circle'],
            default => ['bg-slate-100 text-slate-600', 'circle'],
        };
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('delivery-notes.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:underline">
                <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali ke Surat Jalan
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $deliveryNote->delivery_note_no }}</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $statusChip[0] }}">
                    <i data-lucide="{{ $statusChip[1] }}" class="h-3.5 w-3.5"></i>
                    {{ ucfirst($deliveryNote->status) }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                Dibuat {{ $deliveryNote->created_at->format('d M Y, H:i') }} oleh {{ $deliveryNote->createdByUser?->name ?? '-' }}
                &bull; Dari Sales Order
                <a href="{{ route('sales-orders.show', $deliveryNote->sales_order_id) }}" class="text-blue-600 hover:underline">{{ $deliveryNote->salesOrder?->sales_order_no }}</a>
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('delivery-notes.print', $deliveryNote) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-600 px-3.5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                <i data-lucide="printer" class="h-4 w-4"></i> Cetak SJ & Packing List
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
        @if ($deliveryNote->status === 'draft')
            <form method="POST" action="{{ route('delivery-notes.ship', $deliveryNote) }}" onsubmit="return confirm('Konfirmasi Surat Jalan ini terkirim? Stok akan dipotong dan tidak bisa dibatalkan lagi.')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i data-lucide="truck"></i> Konfirmasi Terkirim (Potong Stok)
                </button>
            </form>
            <form method="POST" action="{{ route('delivery-notes.cancel', $deliveryNote) }}" onsubmit="return confirm('Batalkan Surat Jalan draft ini?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                    <i data-lucide="ban"></i> Batalkan
                </button>
            </form>
        @endif

        @if ($deliveryNote->status === 'shipped')
            <form method="POST" action="{{ route('delivery-notes.deliver', $deliveryNote) }}" onsubmit="return confirm('Tandai Surat Jalan ini sudah diterima customer?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i data-lucide="package-check"></i> Tandai Diterima Customer
                </button>
            </form>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Data Pengiriman --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-3">Data Pengiriman</h2>
                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $deliveryNote->recipient_name }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $deliveryNote->shipping_address }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">Kurir: {{ $deliveryNote->courier_name ?: '-' }}</p>
                @if ($deliveryNote->note)
                    <p class="mt-2 text-xs text-slate-400">Catatan: {{ $deliveryNote->note }}</p>
                @endif
            </div>

            {{-- Items --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Item Dikirim</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-slate-500">Produk</th>
                                <th class="text-right px-4 py-3 text-slate-500">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($deliveryNote->details as $detail)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $detail->product_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $detail->variant_name }}{{ $detail->sku ? ' — SKU ' . $detail->sku : '' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">{{ $detail->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Riwayat Status --}}
            @php
                $timeline = collect([
                    ['label' => 'Draft dibuat', 'at' => $deliveryNote->created_at, 'by' => $deliveryNote->createdByUser?->name],
                ]);
                if ($deliveryNote->shipped_at) {
                    $timeline->push(['label' => 'Dikonfirmasi terkirim (stok dipotong)', 'at' => $deliveryNote->shipped_at, 'by' => null]);
                }
                if ($deliveryNote->delivered_at) {
                    $timeline->push(['label' => 'Diterima customer', 'at' => $deliveryNote->delivered_at, 'by' => null]);
                }
                if ($deliveryNote->cancelled_at) {
                    $timeline->push(['label' => 'Dibatalkan', 'at' => $deliveryNote->cancelled_at, 'by' => $deliveryNote->cancelledByUser?->name]);
                }
            @endphp
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="font-bold text-slate-800 dark:text-white mb-4">Riwayat Status</h2>
                <div class="space-y-4">
                    @foreach ($timeline as $event)
                        <div class="flex gap-3">
                            <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $event['label'] }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ optional($event['at'])->format('d M Y, H:i') }}
                                    {{ $event['by'] ? '— ' . $event['by'] : '' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="lg:sticky lg:top-24 lg:h-fit">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Packing List</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-slate-500 dark:text-slate-400">Nomor</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $deliveryNote->packingList?->packing_list_no }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-slate-500 dark:text-slate-400">Total Berat</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format(($deliveryNote->packingList?->total_weight_grams ?? 0) / 1000, 2) }} kg</span>
                    </div>
                    @if ($deliveryNote->packingList?->total_packages)
                        <div class="flex items-center justify-between px-5 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Jumlah Koli</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $deliveryNote->packingList->total_packages }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</main>
@endsection
