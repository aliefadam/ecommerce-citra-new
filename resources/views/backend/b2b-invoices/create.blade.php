@extends('layouts.app')

@section('title', 'Buat Invoice')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke Sales Order</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Buat Invoice dari Surat Jalan</h1>
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
                        @php $dnSubtotal = $dn->details->sum(fn ($d) => $d->quantity * (int) ($d->salesOrderDetail?->price ?? 0)); @endphp
                        <label class="flex items-start gap-3 px-5 py-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <input type="checkbox" name="delivery_note_ids[]" value="{{ $dn->id }}" checked
                                data-subtotal="{{ $dnSubtotal }}" onchange="recalculateInvb()"
                                class="invb-dn-check mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
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

                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="invb_ppn_rate" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">PPN (%)</label>
                        <input id="invb_ppn_rate" name="ppn_rate" type="number" min="0" max="100" step="0.01" value="{{ old('ppn_rate', $defaultPpnRate) }}" oninput="recalculateInvb()"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                    <div>
                        <label for="invb_shipping_cost" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Ongkir</label>
                        <input id="invb_shipping_cost" name="shipping_cost" type="number" min="0" step="1" value="{{ old('shipping_cost', 0) }}" oninput="recalculateInvb()"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                    <div>
                        <label for="invb_admin_fee" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Biaya Admin</label>
                        <input id="invb_admin_fee" name="admin_fee" type="number" min="0" step="1" value="{{ old('admin_fee', 0) }}" oninput="recalculateInvb()"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                    <div>
                        <label for="invb_other_cost" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Lain-lain</label>
                        <input id="invb_other_cost" name="other_cost" type="number" min="0" step="1" value="{{ old('other_cost', 0) }}" oninput="recalculateInvb()"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                    <div class="md:col-span-2">
                        <input name="other_cost_note" type="text" placeholder="Keterangan biaya lain-lain (opsional)" value="{{ old('other_cost_note') }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between bg-blue-50 dark:bg-blue-900/20">
                    <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                    <span id="invbGrandTotal" class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp 0</span>
                </div>

                <div class="px-5 py-4 flex justify-end gap-2">
                    <a href="{{ route('sales-orders.show', $salesOrder) }}" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</a>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Buat Invoice</button>
                </div>
            </div>
        </form>
    </main>
@endsection

@section('script')
    <script>
        function recalculateInvb() {
            let subtotal = 0;
            document.querySelectorAll('.invb-dn-check:checked').forEach((el) => {
                subtotal += Number(el.dataset.subtotal || 0);
            });

            const ppnRate      = Math.max(0, Math.min(100, parseFloat(document.getElementById('invb_ppn_rate')?.value || 0)));
            const ppnAmount    = Math.round(subtotal * ppnRate / 100);
            const shippingCost = Math.max(0, Number(document.getElementById('invb_shipping_cost')?.value || 0));
            const adminFee     = Math.max(0, Number(document.getElementById('invb_admin_fee')?.value || 0));
            const otherCost    = Math.max(0, Number(document.getElementById('invb_other_cost')?.value || 0));
            const grandTotal   = subtotal + ppnAmount + shippingCost + adminFee + otherCost;

            document.getElementById('invbGrandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', recalculateInvb);
    </script>
@endsection
