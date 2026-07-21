@extends('layouts.app')

@section('title', 'Convert to Sales Order')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <a href="{{ route('quotations.show', $quotation) }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke detail quotation</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Convert to Sales Order</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $quotation->quotation_no }} — {{ $quotation->customerName() }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('quotations.convert', $quotation) }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
            @csrf
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h2 class="font-bold text-slate-800 dark:text-white">Pilih Item & Qty</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Qty default terisi sisa qty maksimum. Kurangi jika hanya sebagian yang dipesan saat ini.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 text-slate-500">Produk</th>
                            <th class="text-right px-4 py-3 text-slate-500">Harga</th>
                            <th class="text-right px-4 py-3 text-slate-500">Sisa Qty</th>
                            <th class="text-right px-4 py-3 text-slate-500 w-32">Qty Ditarik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($quotation->details as $detail)
                            @php $remaining = $detail->remainingQuantity(); @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $detail->product_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $detail->variant_name }}</p>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $remaining }} / {{ $detail->quantity }}</td>
                                <td class="px-4 py-3 text-right">
                                    <input type="hidden" name="items[{{ $loop->index }}][quotation_detail_id]" value="{{ $detail->id }}">
                                    <input type="number" name="items[{{ $loop->index }}][qty]" min="0" max="{{ $remaining }}" value="{{ $remaining }}"
                                        {{ $remaining < 1 ? 'readonly' : '' }}
                                        class="w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-right text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:text-slate-200 {{ $remaining < 1 ? 'bg-slate-100 dark:bg-slate-800 cursor-not-allowed' : 'bg-white dark:bg-slate-700' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 flex justify-end gap-2">
                <a href="{{ route('quotations.show', $quotation) }}" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</a>
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Buat Sales Order</button>
            </div>
        </form>
    </main>
@endsection
