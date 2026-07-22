@extends('layouts.app')

@section('title', 'Buat Invoice Langsung')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke Sales Order</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Buat Invoice Langsung</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $salesOrder->sales_order_no }} — {{ $salesOrder->customerName() }}. Untuk kasus tagih/bayar dulu sebelum barang dikirim — Surat Jalan yang dibuat kemudian akan otomatis terhubung ke Invoice ini.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('b2b-invoices.store-direct', $salesOrder) }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf

            <section class="space-y-6">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Jatuh Tempo <span class="text-red-500">*</span></label>
                    <input type="date" name="due_date" required min="{{ now()->format('Y-m-d') }}" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}"
                        class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    <p class="mt-1 text-xs text-slate-400">Contoh: NET 30 → 30 hari dari sekarang.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-slate-800 dark:text-white">Pilih Item & Qty yang Ditagihkan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada Surat Jalan — item ditagihkan langsung dari Sales Order.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-slate-500">Produk</th>
                                    <th class="text-right px-4 py-3 text-slate-500">Harga</th>
                                    <th class="text-right px-4 py-3 text-slate-500">Sisa Belum Ter-invoice</th>
                                    <th class="text-right px-4 py-3 text-slate-500 w-32">Qty Ditagihkan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach ($salesOrder->details as $detail)
                                    @php $remaining = $detail->remainingToInvoiceDirect(); @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $detail->product_name }}</p>
                                            <p class="text-xs text-slate-400">{{ $detail->variant_name }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $remaining }} / {{ $detail->quantity }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="hidden" name="items[{{ $loop->index }}][sales_order_detail_id]" value="{{ $detail->id }}">
                                            <input type="number" name="items[{{ $loop->index }}][qty]" min="0" max="{{ $remaining }}" value="{{ $remaining }}"
                                                data-price="{{ $detail->price }}" oninput="recalculateInvbDirect()"
                                                {{ $remaining < 1 ? 'readonly' : '' }}
                                                class="invbd-qty w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-right text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:text-slate-200 {{ $remaining < 1 ? 'bg-slate-100 dark:bg-slate-800 cursor-not-allowed' : 'bg-white dark:bg-slate-700' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- Sidebar total --}}
            <aside class="xl:sticky xl:top-24 xl:h-fit">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan</h2>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <span class="text-slate-500 dark:text-slate-400 shrink-0">Subtotal</span>
                            <span id="invbdSummarySubtotal" class="font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>

                        <div class="flex items-center gap-3 px-5 py-3">
                            <label for="invbd_ppn_rate" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">PPN (%)</label>
                            <input id="invbd_ppn_rate" name="ppn_rate" type="number" min="0" max="100" step="0.01"
                                value="{{ old('ppn_rate', $defaultPpnRate) }}" oninput="recalculateInvbDirect()"
                                class="w-20 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            <span id="invbdSummaryPpn" class="shrink-0 flex-1 text-right font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>

                        <div class="flex items-center gap-3 px-5 py-3">
                            <label for="invbd_shipping_cost" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Ongkir</label>
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                                <input id="invbd_shipping_cost" name="shipping_cost" type="number" min="0" step="1"
                                    value="{{ old('shipping_cost', 0) }}" oninput="recalculateInvbDirect()"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                        </div>

                        <div class="flex items-center gap-3 px-5 py-3">
                            <label for="invbd_admin_fee" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Biaya Admin</label>
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                                <input id="invbd_admin_fee" name="admin_fee" type="number" min="0" step="1"
                                    value="{{ old('admin_fee', 0) }}" oninput="recalculateInvbDirect()"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                        </div>

                        <div class="flex items-center gap-3 px-5 py-3">
                            <label for="invbd_other_cost" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Lain-lain</label>
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                                <input id="invbd_other_cost" name="other_cost" type="number" min="0" step="1"
                                    value="{{ old('other_cost', 0) }}" oninput="recalculateInvbDirect()"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                        </div>
                        <div class="px-5 pb-3 -mt-2">
                            <input name="other_cost_note" type="text" placeholder="Keterangan biaya lain-lain (opsional)"
                                value="{{ old('other_cost_note') }}"
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">
                        </div>

                        <div class="flex items-center justify-between gap-3 px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                            <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                            <span id="invbdGrandTotal" class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp 0</span>
                        </div>
                    </div>

                    <div class="px-5 py-4 flex flex-col gap-2">
                        <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition-colors hover:bg-blue-700">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Buat Invoice
                        </button>
                        <a href="{{ route('sales-orders.show', $salesOrder) }}"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                            Batal
                        </a>
                    </div>
                </div>
            </aside>
        </form>
    </main>
@endsection

@section('script')
    <script>
        function recalculateInvbDirect() {
            let subtotal = 0;
            document.querySelectorAll('.invbd-qty').forEach((input) => {
                subtotal += Math.max(0, Number(input.value || 0)) * Number(input.dataset.price || 0);
            });

            const ppnRate      = Math.max(0, Math.min(100, parseFloat(document.getElementById('invbd_ppn_rate')?.value || 0)));
            const ppnAmount    = Math.round(subtotal * ppnRate / 100);
            const shippingCost = Math.max(0, Number(document.getElementById('invbd_shipping_cost')?.value || 0));
            const adminFee     = Math.max(0, Number(document.getElementById('invbd_admin_fee')?.value || 0));
            const otherCost    = Math.max(0, Number(document.getElementById('invbd_other_cost')?.value || 0));
            const grandTotal   = subtotal + ppnAmount + shippingCost + adminFee + otherCost;

            document.getElementById('invbdSummarySubtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('invbdSummaryPpn').textContent      = 'Rp ' + ppnAmount.toLocaleString('id-ID');
            document.getElementById('invbdGrandTotal').textContent      = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', recalculateInvbDirect);
    </script>
@endsection
