@extends('layouts.app')

@section('title', 'Buat Invoice B2B')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke Sales Order</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Buat Invoice B2B</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $salesOrder->sales_order_no }} — {{ $salesOrder->customerName() }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('b2b-invoices.store', $salesOrder) }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Jatuh Tempo <span class="text-red-500">*</span></label>
                <input type="date" name="due_date" required min="{{ now()->format('Y-m-d') }}" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}"
                    class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                <p class="mt-1 text-xs text-slate-400">Contoh: NET 30 → 30 hari dari sekarang.</p>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Pilih Surat Jalan yang Ditagihkan</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Surat Jalan yang sudah shipped/delivered dan belum ter-invoice.</p>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($candidates as $dn)
                        <label class="flex items-start gap-3 px-5 py-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <input type="checkbox" name="delivery_note_ids[]" value="{{ $dn->id }}" checked
                                class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $dn->delivery_note_no }}</p>
                                    <span class="text-xs font-semibold {{ $dn->status === 'delivered' ? 'text-emerald-600' : 'text-blue-600' }}">{{ ucfirst($dn->status) }}</span>
                                </div>
                                <ul class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    @foreach ($dn->details as $detail)
                                        <li>{{ $detail->product_name }}{{ $detail->variant_name ? ' - ' . $detail->variant_name : '' }} x{{ $detail->quantity }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div class="px-5 py-4 flex justify-end gap-2">
                    <a href="{{ route('sales-orders.show', $salesOrder) }}" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</a>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Buat Invoice</button>
                </div>
            </div>
        </form>
    </main>
@endsection
