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
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input id="txSearch" type="text" placeholder="Cari invoice / order / customer..."
                        class="pl-9 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">#</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Invoice</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Order ID</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Customer</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Grand Total</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="txTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <p id="txPaginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div class="flex items-center gap-1" id="txPaginationButtons"></div>
            </div>
        </div>

        <div id="txDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeTxDetailModal()"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Detail Transaction</h3>
                    <button type="button" onclick="closeTxDetailModal()" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div id="txDetailContent" class="space-y-3"></div>
            </div>
        </div>

        <div id="shipModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeShipModal()"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Kirim Pesanan</h3>
                    <button type="button" onclick="closeShipModal()" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="space-y-3">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Nomor Resi</label>
                    <input id="shipTrackingNumber" type="text"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Masukkan nomor resi">
                    <p id="shipError" class="text-xs text-red-500 hidden"></p>
                    <button id="shipSubmitBtn" type="button"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Submit Pengiriman
                    </button>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @php
        $txItems = $transactions->map(function ($tx) {
            return [
                'id' => $tx->id,
                'invoice_no' => $tx->invoice_no,
                'order_id' => $tx->order_id,
                'customer' => $tx->user?->name ?? '-',
                'status' => $tx->status,
                'payment_type' => $tx->payment_type ?? '-',
                'payment_method' => $tx->payment_method ?? '-',
                'shipping_cost' => (int) $tx->shipping_cost,
                'grand_total' => (int) $tx->grand_total,
                'tracking_number' => $tx->tracking_number,
                'details' => $tx->details->map(fn ($d) => [
                    'product_name' => $d->product_name,
                    'variant_name' => $d->variant_name,
                    'quantity' => (int) $d->quantity,
                    'price' => (int) $d->price,
                    'subtotal' => (int) $d->subtotal,
                    'item_note' => $d->item_note,
                ])->values()->all(),
            ];
        })->values()->all();
    @endphp
    <script>
        const txItems = @json($txItems);
        const processUrlTemplate = @json(route('transactions.process', ['transaction' => '__ID__']));
        const shipUrlTemplate = @json(route('transactions.ship', ['transaction' => '__ID__']));
        const csrfToken = @json(csrf_token());
        let txTableApi = null;
        let activeShipTxId = null;

        function txStatusBadge(status) {
            const s = String(status || '').toLowerCase();
            if (['settlement', 'capture', 'paid'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Paid</span>';
            }
            if (['process'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">Diproses</span>';
            }
            if (['kirim'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Dikirim</span>';
            }
            if (['cancel', 'expire', 'deny', 'failed'].includes(s)) {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">Failed</span>';
            }
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Pending</span>';
        }

        function actionMenu(tx) {
            const s = String(tx.status || '').toLowerCase();
            let items = `
                <button type="button" onclick="openTxDetailModal(${tx.id}); closeActionMenu(${tx.id})" class="w-full text-left px-3 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Show Detail</button>
            `;
            if (['paid', 'settlement', 'capture'].includes(s)) {
                items += `<button type="button" onclick="processTransaction(${tx.id}); closeActionMenu(${tx.id})" class="w-full text-left px-3 py-2 text-xs text-blue-700 hover:bg-blue-50">Proses Transaksi</button>`;
            }
            if (s === 'process') {
                items += `<button type="button" onclick="openShipModal(${tx.id}); closeActionMenu(${tx.id})" class="w-full text-left px-3 py-2 text-xs text-blue-700 hover:bg-blue-50">Kirim Pesanan</button>`;
            }
            if (s === 'kirim') {
                items += `<button type="button" onclick="showToast('Fitur lacak pesanan belum diaktifkan.'); closeActionMenu(${tx.id})" class="w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">Lacak Pesanan</button>`;
            }
            return `
                <div class="relative inline-block text-left">
                    <button type="button" onclick="toggleActionMenu(${tx.id})" class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                    </button>
                    <div id="tx-action-menu-${tx.id}" class="hidden absolute right-0 mt-1 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-20 py-1">
                        ${items}
                    </div>
                </div>
            `;
        }

        function renderTxRow(tx, visibleIndex) {
            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${visibleIndex + 1}</td>
                    <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">${tx.invoice_no}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
                        <div>${tx.order_id}</div>
                        ${tx.tracking_number ? `<div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Resi: ${tx.tracking_number}</div>` : ''}
                    </td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${tx.customer}</td>
                    <td class="px-4 py-3.5">${txStatusBadge(tx.status)}</td>
                    <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">Rp ${Number(tx.grand_total || 0).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-3.5">
                        ${actionMenu(tx)}
                    </td>
                </tr>
            `;
        }

        function openTxDetailModal(id) {
            const tx = txItems.find((i) => Number(i.id) === Number(id));
            if (!tx) return;
            const detailRows = (tx.details || []).map((d) => `
                <tr class="border-b border-slate-100 dark:border-slate-700/60">
                    <td class="py-2 pr-3 text-slate-700 dark:text-slate-200">${d.product_name}${d.variant_name ? ` <span class="text-xs text-slate-500">(${d.variant_name})</span>` : ''}</td>
                    <td class="py-2 px-3 text-slate-500 dark:text-slate-400 text-center">${d.quantity}</td>
                    <td class="py-2 px-3 text-slate-500 dark:text-slate-400 text-right">Rp ${Number(d.price || 0).toLocaleString('id-ID')}</td>
                    <td class="py-2 pl-3 text-slate-700 dark:text-slate-200 text-right">Rp ${Number(d.subtotal || 0).toLocaleString('id-ID')}</td>
                </tr>
                ${d.item_note ? `<tr><td colspan="4" class="pb-2 text-xs text-slate-500 dark:text-slate-400">Catatan: ${d.item_note}</td></tr>` : ''}
            `).join('');

            const html = `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-500 dark:text-slate-400">Invoice:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">${tx.invoice_no}</span></div>
                    <div><span class="text-slate-500 dark:text-slate-400">Order ID:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">${tx.order_id}</span></div>
                    <div><span class="text-slate-500 dark:text-slate-400">Customer:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">${tx.customer}</span></div>
                    <div><span class="text-slate-500 dark:text-slate-400">Metode:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">${tx.payment_method}</span></div>
                    <div><span class="text-slate-500 dark:text-slate-400">Resi:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">${tx.tracking_number || '-'}</span></div>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="text-left py-2 pr-3">Produk</th>
                                <th class="text-center py-2 px-3">Qty</th>
                                <th class="text-right py-2 px-3">Harga</th>
                                <th class="text-right py-2 pl-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows}</tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-700 pt-3 text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Ongkir</span><span class="font-medium text-slate-700 dark:text-slate-200">Rp ${Number(tx.shipping_cost || 0).toLocaleString('id-ID')}</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-slate-800 dark:text-slate-200">Grand Total</span><span class="font-bold text-blue-600">Rp ${Number(tx.grand_total || 0).toLocaleString('id-ID')}</span></div>
                </div>
            `;

            document.getElementById('txDetailContent').innerHTML = html;
            document.getElementById('txDetailModal').classList.remove('hidden');
            document.getElementById('txDetailModal').classList.add('flex');
        }

        function closeTxDetailModal() {
            document.getElementById('txDetailModal').classList.add('hidden');
            document.getElementById('txDetailModal').classList.remove('flex');
        }

        function showToast(msg) {
            alert(msg);
        }

        function toggleActionMenu(id) {
            document.querySelectorAll('[id^="tx-action-menu-"]').forEach((el) => {
                if (el.id !== `tx-action-menu-${id}`) el.classList.add('hidden');
            });
            const menu = document.getElementById(`tx-action-menu-${id}`);
            if (!menu) return;
            menu.classList.toggle('hidden');
        }

        function closeActionMenu(id) {
            const menu = document.getElementById(`tx-action-menu-${id}`);
            if (menu) menu.classList.add('hidden');
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
                    tracking_number: trackingNumber
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
            searchFields: ['invoice_no', 'order_id', 'customer'],
            renderRow: (tx, index) => renderTxRow(tx, index),
            emptyRowHtml: '<tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-slate-500">No transactions found</td></tr>',
        });

        document.getElementById('shipSubmitBtn')?.addEventListener('click', submitShip);
        document.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof Element)) return;
            if (!target.closest('[id^="tx-action-menu-"]') && !target.closest('button[onclick*="toggleActionMenu"]')) {
                document.querySelectorAll('[id^="tx-action-menu-"]').forEach((el) => el.classList.add('hidden'));
            }
        });
    </script>
@endsection
