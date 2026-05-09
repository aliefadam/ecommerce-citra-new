@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Transactions</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar transaksi checkout dari frontend.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input id="txSearch" type="text" placeholder="Cari invoice / customer / email..."
                        class="pl-9 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="overflow: visible;">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">#</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Invoice</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Customer</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Email</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Grand Total
                            </th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="txTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                </table>
            </div>

            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <p id="txPaginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div class="flex items-center gap-1" id="txPaginationButtons"></div>
            </div>
        </div>

        {{-- Floating Action Menu (outside table, fixed positioning) --}}
        <div id="floatingActionMenu"
            class="fixed z-[99999] hidden w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl py-1">
        </div>

        {{-- Detail Modal --}}
        <div id="txDetailModal" class="fixed inset-0 z-[99998] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeTxDetailModal()"></div>
            <div
                class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                {{-- Header --}}
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 shrink-0">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white">Detail Transaksi</h3>
                        <p id="txDetailInvoice" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"></p>
                    </div>
                    <button type="button" onclick="closeTxDetailModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
                {{-- Scrollable Body --}}
                <div class="overflow-y-auto flex-1 px-6 py-4 space-y-5" id="txDetailContent"></div>
            </div>
        </div>

        {{-- Ship Modal --}}
        <div id="shipModal" class="fixed inset-0 z-[99998] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeShipModal()"></div>
            <div
                class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Kirim Pesanan</h3>
                    <button type="button" onclick="closeShipModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Kurir / Layanan</label>
                    <input id="shipShippingLabel" type="text"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: JNE REG">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Nomor Resi</label>
                    <input id="shipTrackingNumber" type="text"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Masukkan nomor resi">
                    <textarea id="shipShippingNote" rows="3"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Catatan pengiriman (opsional)"></textarea>
                    <p id="shipError" class="text-xs text-red-500 hidden"></p>
                    <button id="shipSubmitBtn" type="button"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Submit Pengiriman
                    </button>
                </div>
            </div>
        </div>

        {{-- Tracking Modal --}}
        <div id="trackingModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeTrackingModal()"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 shrink-0">
                    <div>
                        <h3 class="font-bold text-base text-slate-800 dark:text-white">Lacak Pesanan</h3>
                        <p id="adminTrackingResi" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"></p>
                    </div>
                    <button onclick="closeTrackingModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 px-6 py-5">
                    {{-- Courier info --}}
                    <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3 mb-5 border border-slate-100 dark:border-slate-700">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 13h12l1-13"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p id="adminTrackingCourier" class="text-sm font-semibold text-slate-800 dark:text-white"></p>
                            <p id="adminTrackingResiSmall" class="text-xs text-slate-500 dark:text-slate-400 truncate"></p>
                        </div>
                        <span class="ml-auto shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">Dalam Pengiriman</span>
                    </div>
                    {{-- Timeline --}}
                    <div id="adminTrackingTimeline" class="space-y-0"></div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @php
        $txItems = $transactions
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'invoice_no' => $tx->invoice_no,
                    'order_id' => $tx->order_id,
                    'customer' => $tx->user?->name ?? '-',
                    'customer_email' => $tx->user?->email ?? '-',
                    'status' => $tx->status,
                    'payment_type' => $tx->payment_type ?? '-',
                    'payment_method' => $tx->payment_method ?? '-',
                    'shipping_cost' => (int) $tx->shipping_cost,
                    'discount_amount' => (int) ($tx->discount_amount ?? 0),
                    'coupon_code' => (string) ($tx->coupon_code ?? ''),
                    'shipping_label' => $tx->shipping_label ?? '-',
                    'shipping_note' => (string) ($tx->shipping_note ?? ''),
                    'grand_total' => (int) $tx->grand_total,
                    'invoice_url' => route('invoice.show', ['transaction' => $tx->id]),
                    'detail_url' => route('transactions.show', ['transaction' => $tx->id]),
                    'payment_type_raw' => (string) ($tx->payment_type ?? ''),
                    'payment_proof_url' => $tx->payment_proof_path ? asset(ltrim((string) $tx->payment_proof_path, '/')) : '',
                    'tracking_number' => $tx->tracking_number,
                    'shipping_recipient_name' => $tx->shipping_recipient_name ?? '',
                    'shipping_phone' => $tx->shipping_phone ?? '',
                    'shipping_address_line' => $tx->shipping_address_line ?? '',
                    'shipping_city' => $tx->shipping_city ?? '',
                    'shipping_province' => $tx->shipping_province ?? '',
                    'shipping_postal_code' => $tx->shipping_postal_code ?? '',
                    'cancel_reason' => $tx->cancel_reason ?? '',
                    'details' => $tx->details
                        ->map(
                            fn($d) => [
                                'product_name' => $d->product_name,
                                'variant_name' => $d->variant_name,
                                'image' => $d->image_url ?? '',
                                'quantity' => (int) $d->quantity,
                                'price' => (int) $d->price,
                                'subtotal' => (int) $d->subtotal,
                                'item_note' => $d->item_note,
                            ],
                        )
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    @endphp
    <script>
        const txItems = @json($txItems);
        const processUrlTemplate = @json(route('transactions.process', ['transaction' => '__ID__']));
        const shipUrlTemplate = @json(route('transactions.ship', ['transaction' => '__ID__']));
        const csrfToken = @json(csrf_token());
        let txTableApi = null;
        let activeShipTxId = null;
        let activeMenuTxId = null;

        function txStatusBadge(status) {
            const s = String(status || '').toLowerCase();
            if (['settlement', 'capture', 'paid'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Paid</span>';
            }
            if (s === 'menunggu_verifikasi') {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Menunggu Verifikasi</span>';
            }
            if (['process'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">Diproses</span>';
            }
            if (['kirim'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Dikirim</span>';
            }
            if (['cancel', 'expire', 'deny', 'failed', 'dibatalkan'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">Dibatalkan</span>';
            }
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Pending</span>';
        }

        function renderTxRow(tx, visibleIndex) {
            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${visibleIndex + 1}</td>
                    <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">
                        <div>${tx.invoice_no}</div>
                        ${tx.tracking_number ? `<div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Resi: ${tx.tracking_number}</div>` : ''}
                    </td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
                        <div>${tx.customer}</div>
                    </td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
                        <div>${tx.customer_email || '-'}</div>
                    </td>
                    <td class="px-4 py-3.5">${txStatusBadge(tx.status)}</td>
                    <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">Rp ${Number(tx.grand_total || 0).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-3.5">
                        <button type="button" data-tx-id="${tx.id}" onclick="toggleActionMenu(${tx.id}, this)" class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                        </button>
                    </td>
                </tr>
            `;
        }

        function buildMenuItems(tx) {
            const s = String(tx.status || '').toLowerCase();
            let html = `<button type="button" onclick="openTxDetailModal(${tx.id}); closeFloatingMenu()" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 flex items-center gap-2.5 transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-slate-400"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Show Detail
            </button>`;
            html += `<a href="${tx.detail_url}" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 flex items-center gap-2.5 transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M9 18l6-6-6-6"/></svg>
                Halaman Detail
            </a>`;
            html += `<a href="${tx.invoice_url}" target="_blank" class="w-full text-left px-4 py-2.5 text-sm text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center gap-2.5 transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Print Invoice
            </a>`;
            if (['paid', 'settlement', 'capture'].includes(s)) {
                html += `<button type="button" onclick="processTransaction(${tx.id}); closeFloatingMenu()" class="w-full text-left px-4 py-2.5 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 flex items-center gap-2.5 transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Proses Transaksi
                </button>`;
            }
            if (s === 'process') {
                html += `<button type="button" onclick="openShipModal(${tx.id}); closeFloatingMenu()" class="w-full text-left px-4 py-2.5 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 flex items-center gap-2.5 transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Kirim Pesanan
                </button>`;
            }
            if (s === 'kirim' && tx.tracking_number) {
                html += `<button type="button" onclick="openTrackingModal(${tx.id}); closeFloatingMenu()" class="w-full text-left px-4 py-2.5 text-sm text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center gap-2.5 transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Lacak Pesanan
                </button>`;
            }
            return html;
        }

        function toggleActionMenu(id, btn) {
            const menu = document.getElementById('floatingActionMenu');
            if (activeMenuTxId === id) {
                closeFloatingMenu();
                return;
            }
            closeFloatingMenu();
            activeMenuTxId = id;
            const tx = txItems.find((i) => Number(i.id) === Number(id));
            if (!tx) return;
            menu.innerHTML = buildMenuItems(tx);
            menu.classList.remove('hidden');

            const rect = btn.getBoundingClientRect();
            const menuWidth = 192;
            let left = rect.right - menuWidth;
            let top = rect.bottom + 4;
            if (left < 8) left = 8;
            if (top + 160 > window.innerHeight) top = rect.top - 4 - 160;

            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
        }

        function closeFloatingMenu() {
            document.getElementById('floatingActionMenu').classList.add('hidden');
            activeMenuTxId = null;
        }

        function openTxDetailModal(id) {
            const tx = txItems.find((i) => Number(i.id) === Number(id));
            if (!tx) return;

            document.getElementById('txDetailInvoice').textContent = tx.invoice_no;

            const hasAddress = tx.shipping_recipient_name || tx.shipping_phone || tx.shipping_address_line;
            const addressHtml = hasAddress ? `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Alamat Pengiriman</h4>
                    <div class="bg-slate-50 dark:bg-slate-700/40 rounded-xl p-4 space-y-1.5">
                        ${tx.shipping_recipient_name ? `<p class="font-semibold text-slate-800 dark:text-slate-200 text-sm">${tx.shipping_recipient_name}</p>` : ''}
                        ${tx.shipping_phone ? `<p class="text-sm text-slate-600 dark:text-slate-400 flex items-center gap-1.5"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.62 4.45 2 2 0 0 1 3.6 2.24h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.09 6.09l.95-.95a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 17z"/></svg>${tx.shipping_phone}</p>` : ''}
                        ${tx.shipping_address_line ? `<p class="text-sm text-slate-600 dark:text-slate-400">${tx.shipping_address_line}${tx.shipping_city ? ', ' + tx.shipping_city : ''}${tx.shipping_province ? ', ' + tx.shipping_province : ''}${tx.shipping_postal_code ? ' ' + tx.shipping_postal_code : ''}</p>` : ''}
                        ${tx.shipping_label && tx.shipping_label !== '-' ? `<span class="inline-block mt-1 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 px-2 py-0.5 rounded-full font-medium">${tx.shipping_label}</span>` : ''}
                    </div>
                </div>
            ` : '';

            const infoHtml = `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Informasi Transaksi</h4>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm">
                        <div>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mb-1.5">Customer</p>
                            <p class="font-medium text-slate-800 dark:text-slate-200">${tx.customer}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mb-1.5">Email</p>
                            <p class="font-medium text-slate-800 dark:text-slate-200">${tx.customer_email || '-'}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mb-1.5">Metode Pembayaran</p>
                            <p class="font-medium text-slate-800 dark:text-slate-200">${tx.payment_method}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mb-1.5">Status</p>
                            <div>${txStatusBadge(tx.status)}</div>
                        </div>
                        ${tx.tracking_number ? `<div>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-1.5">Nomor Resi</p>
                                    <p class="font-medium text-slate-800 dark:text-slate-200">${tx.tracking_number}</p>
                                </div>` : ''}
                        ${tx.cancel_reason ? `<div class="col-span-2">
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-1.5">Alasan Pembatalan</p>
                                    <p class="font-medium text-red-600 dark:text-red-400">${tx.cancel_reason}</p>
                                </div>` : ''}
                    </div>
                </div>
            `;

            const detailRows = (tx.details || []).map((d) => `
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-lg overflow-hidden shrink-0 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
                        ${d.image ? `<img src="${d.image}" alt="${d.product_name}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-slate-400\'><svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg></div>'" />` : '<div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>'}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200 leading-snug">${d.product_name}${d.variant_name ? `<span class="ml-1 text-xs text-slate-400 font-normal">(${d.variant_name})</span>` : ''}</p>
                        ${d.item_note ? `<p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Catatan: ${d.item_note}</p>` : ''}
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">${d.quantity} × Rp ${Number(d.price || 0).toLocaleString('id-ID')}</p>
                    </div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 shrink-0">Rp ${Number(d.subtotal || 0).toLocaleString('id-ID')}</div>
                </div>
            `).join('');

            const productsHtml = `
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Produk</h4>
                    <div class="space-y-3">
                        ${detailRows || '<p class="text-sm text-slate-400">Tidak ada produk.</p>'}
                    </div>
                </div>
            `;

            const summaryHtml = `
                <div class="bg-slate-50 dark:bg-slate-700/40 rounded-xl p-4 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>Ongkos Kirim</span>
                        <span>Rp ${Number(tx.shipping_cost || 0).toLocaleString('id-ID')}</span>
                    </div>
                    ${Number(tx.discount_amount || 0) > 0 ? `<div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                        <span>Voucher ${tx.coupon_code || ''}</span>
                        <span>- Rp ${Number(tx.discount_amount || 0).toLocaleString('id-ID')}</span>
                    </div>` : ''}
                    <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-600">
                        <span class="font-bold text-slate-800 dark:text-slate-200">Grand Total</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400 text-base">Rp ${Number(tx.grand_total || 0).toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;

            document.getElementById('txDetailContent').innerHTML = infoHtml + (hasAddress ? addressHtml : '') +
                productsHtml + summaryHtml;
            document.getElementById('txDetailModal').classList.remove('hidden');
            document.getElementById('txDetailModal').classList.add('flex');
        }

        function closeTxDetailModal() {
            document.getElementById('txDetailModal').classList.add('hidden');
            document.getElementById('txDetailModal').classList.remove('flex');
        }

        async function processTransaction(id) {
            const url = processUrlTemplate.replace('__ID__', id);
            const res = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                alert(json?.message || 'Gagal memproses transaksi.');
                return;
            }
            window.location.reload();
        }

        function openShipModal(id) {
            activeShipTxId = id;
            const modal = document.getElementById('shipModal');
            const err = document.getElementById('shipError');
            const input = document.getElementById('shipTrackingNumber');
            if (err) err.classList.add('hidden');
            if (input) input.value = '';
            const label = document.getElementById('shipShippingLabel');
            const note = document.getElementById('shipShippingNote');
            const tx = txItems.find((item) => Number(item.id) === Number(id));
            if (label) label.value = tx?.shipping_label && tx.shipping_label !== '-' ? tx.shipping_label : '';
            if (note) note.value = tx?.shipping_note || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeShipModal() {
            const modal = document.getElementById('shipModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            activeShipTxId = null;
        }

        async function submitShip() {
            const id = activeShipTxId;
            if (!id) return;
            const input = document.getElementById('shipTrackingNumber');
            const err = document.getElementById('shipError');
            const trackingNumber = String(input?.value || '').trim();
            const shippingLabel = String(document.getElementById('shipShippingLabel')?.value || '').trim();
            const shippingNote = String(document.getElementById('shipShippingNote')?.value || '').trim();
            if (!trackingNumber) {
                err.textContent = 'Nomor resi wajib diisi.';
                err.classList.remove('hidden');
                return;
            }
            const url = shipUrlTemplate.replace('__ID__', id);
            const res = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    tracking_number: trackingNumber,
                    shipping_label: shippingLabel,
                    shipping_note: shippingNote
                }),
            });
            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                err.textContent = json?.message || 'Gagal kirim pesanan.';
                err.classList.remove('hidden');
                return;
            }
            closeShipModal();
            window.location.reload();
        }

        txTableApi = initAdminDataTable({
            data: txItems,
            perPage: 10,
            itemLabel: 'transactions',
            searchInputId: 'txSearch',
            tbodyId: 'txTableBody',
            paginationInfoId: 'txPaginationInfo',
            paginationButtonsId: 'txPaginationButtons',
            searchFields: ['invoice_no', 'customer', 'customer_email'],
            renderRow: (tx, index) => renderTxRow(tx, index),
            emptyRowHtml: '<tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-slate-500">No transactions found</td></tr>',
        });

        document.getElementById('shipSubmitBtn')?.addEventListener('click', submitShip);

        document.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof Element)) return;
            const isMenuBtn = target.closest('button[data-tx-id]');
            const isFloatingMenu = target.closest('#floatingActionMenu');
            if (!isMenuBtn && !isFloatingMenu) {
                closeFloatingMenu();
            }
        });

        window.addEventListener('scroll', closeFloatingMenu, true);
        window.addEventListener('resize', closeFloatingMenu);

        function openTrackingModal(id) {
            const tx = txItems.find(t => Number(t.id) === Number(id));
            if (!tx) return;

            const resi = tx.tracking_number || '-';
            const courier = tx.shipping_label || 'Ekspedisi';

            document.getElementById('adminTrackingResi').textContent = 'No. Resi: ' + resi;
            document.getElementById('adminTrackingResiSmall').textContent = resi;
            document.getElementById('adminTrackingCourier').textContent = courier;

            // Dummy timeline
            const steps = [
                { done: true,  label: 'Pesanan Dibuat',       desc: `Invoice ${tx.invoice_no} berhasil dibuat.` },
                { done: true,  label: 'Pembayaran Diterima',  desc: 'Pembayaran telah dikonfirmasi.' },
                { done: true,  label: 'Pesanan Diproses',     desc: 'Pesanan sedang disiapkan oleh tim gudang.' },
                { done: true,  label: 'Diserahkan ke Kurir',  desc: `Paket diserahkan ke ${courier} dengan resi ${resi}.` },
                { done: false, label: 'Dalam Perjalanan',     desc: 'Paket sedang dalam perjalanan.' },
                { done: false, label: 'Tiba di Kota Tujuan',  desc: `Paket tiba di kota ${tx.shipping_city || 'tujuan'}.` },
                { done: false, label: 'Diterima Penerima',    desc: `Paket diterima oleh ${tx.shipping_recipient_name || 'penerima'}.` },
            ];

            document.getElementById('adminTrackingTimeline').innerHTML = steps.map((step, i) => {
                const isLast = i === steps.length - 1;
                const dot  = step.done ? 'bg-blue-500' : 'bg-slate-200 dark:bg-slate-600';
                const line = step.done ? 'bg-blue-200 dark:bg-blue-800' : 'bg-slate-100 dark:bg-slate-700';
                const lbl  = step.done ? 'text-slate-800 dark:text-white font-semibold' : 'text-slate-400 dark:text-slate-500';
                const dsc  = step.done ? 'text-slate-500 dark:text-slate-400' : 'text-slate-300 dark:text-slate-600';
                return `
                <div class="flex gap-3">
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-3.5 h-3.5 rounded-full ${dot} mt-0.5 ring-4 ring-white dark:ring-slate-800 relative z-10"></div>
                        ${!isLast ? `<div class="w-0.5 flex-1 ${line} my-1"></div>` : ''}
                    </div>
                    <div class="pb-5 min-w-0 flex-1">
                        <p class="text-sm ${lbl}">${step.label}</p>
                        <p class="text-xs ${dsc} mt-0.5 leading-relaxed">${step.desc}</p>
                    </div>
                </div>`;
            }).join('');

            const modal = document.getElementById('trackingModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeTrackingModal() {
            const modal = document.getElementById('trackingModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection
