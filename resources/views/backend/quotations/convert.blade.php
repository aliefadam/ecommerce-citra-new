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
                                        data-price="{{ $detail->price }}" oninput="recalculateConvert()"
                                        {{ $remaining < 1 ? 'readonly' : '' }}
                                        class="convert-qty w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-right text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:text-slate-200 {{ $remaining < 1 ? 'bg-slate-100 dark:bg-slate-800 cursor-not-allowed' : 'bg-white dark:bg-slate-700' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="convert_ppn_rate" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">PPN (%)</label>
                    <input id="convert_ppn_rate" name="ppn_rate" type="number" min="0" max="100" step="0.01" value="{{ old('ppn_rate', $defaultPpnRate) }}" oninput="recalculateConvert()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </div>
                <div>
                    <label for="convert_shipping_cost" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Ongkir</label>
                    <input id="convert_shipping_cost" name="shipping_cost" type="number" min="0" step="1" value="{{ old('shipping_cost', 0) }}" oninput="recalculateConvert()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </div>
                <div>
                    <label for="convert_admin_fee" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Biaya Admin</label>
                    <input id="convert_admin_fee" name="admin_fee" type="number" min="0" step="1" value="{{ old('admin_fee', 0) }}" oninput="recalculateConvert()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </div>
                <div>
                    <label for="convert_other_cost" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Lain-lain</label>
                    <input id="convert_other_cost" name="other_cost" type="number" min="0" step="1" value="{{ old('other_cost', 0) }}" oninput="recalculateConvert()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </div>
                <div class="md:col-span-2">
                    <input name="other_cost_note" type="text" placeholder="Keterangan biaya lain-lain (opsional)" value="{{ old('other_cost_note') }}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </div>
            </div>

            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between bg-blue-50 dark:bg-blue-900/20">
                <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                <span id="convertGrandTotal" class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp 0</span>
            </div>

            <div class="px-5 py-4 flex justify-end gap-2">
                <a href="{{ route('quotations.show', $quotation) }}" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</a>
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Buat Sales Order</button>
            </div>
        </form>
    </main>
@endsection

@section('script')
    <script>
        function recalculateConvert() {
            let subtotal = 0;
            document.querySelectorAll('.convert-qty').forEach((input) => {
                subtotal += Math.max(0, Number(input.value || 0)) * Number(input.dataset.price || 0);
            });

            const ppnRate      = Math.max(0, Math.min(100, parseFloat(document.getElementById('convert_ppn_rate')?.value || 0)));
            const ppnAmount    = Math.round(subtotal * ppnRate / 100);
            const shippingCost = Math.max(0, Number(document.getElementById('convert_shipping_cost')?.value || 0));
            const adminFee     = Math.max(0, Number(document.getElementById('convert_admin_fee')?.value || 0));
            const otherCost    = Math.max(0, Number(document.getElementById('convert_other_cost')?.value || 0));
            const grandTotal   = subtotal + ppnAmount + shippingCost + adminFee + otherCost;

            document.getElementById('convertGrandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', recalculateConvert);
    </script>
@endsection
