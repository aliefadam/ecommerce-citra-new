@extends('layouts.app')

@section('title', 'Return / Refund')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Return / Refund</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola pengajuan refund uang dan ganti barang dari customer.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"></i>
                    <input id="returnSearch" type="text" placeholder="Cari nomor pengajuan / invoice / customer..."
                        class="pl-9 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
                <select id="returnStatusFilter"
                    class="px-4 py-2 text-sm bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                    <option value="">Semua status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Pengajuan</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Customer</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Jenis</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Nominal</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="returnTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <p id="returnPaginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div class="flex items-center gap-1" id="returnPaginationButtons"></div>
            </div>
        </div>

        <div id="returnDetailModal" class="fixed inset-0 z-[99998] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeReturnDetailModal()"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 shrink-0">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white">Detail Return / Refund</h3>
                        <p id="returnDetailNo" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"></p>
                    </div>
                    <button type="button" onclick="closeReturnDetailModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div id="returnDetailContent" class="overflow-y-auto flex-1 px-6 py-4 space-y-5"></div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @php
        $items = $returnRequests
            ->map(function ($rr) {
                $photos = collect((array) ($rr->photos ?? []))
                    ->map(function ($photo) {
                        $photo = (string) $photo;
                        if ($photo === '') return null;
                        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://') || str_starts_with($photo, '/')) {
                            return $photo;
                        }
                        return asset(ltrim($photo, '/'));
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => (int) $rr->id,
                    'request_no' => (string) $rr->request_no,
                    'invoice_no' => (string) ($rr->transaction?->invoice_no ?? '-'),
                    'customer' => (string) ($rr->user?->name ?? '-'),
                    'email' => (string) ($rr->user?->email ?? '-'),
                    'type' => (string) $rr->type,
                    'status' => (string) $rr->status,
                    'refund_amount' => (int) $rr->refund_amount,
                    'reason' => (string) $rr->reason,
                    'customer_note' => (string) ($rr->customer_note ?? ''),
                    'admin_note' => (string) ($rr->admin_note ?? ''),
                    'created_at' => optional($rr->created_at)->translatedFormat('d M Y H:i'),
                    'photos' => $photos,
                    'items' => $rr->items
                        ->map(fn($item) => [
                            'product_name' => (string) $item->product_name,
                            'variant_name' => (string) ($item->variant_name ?? ''),
                            'quantity' => (int) $item->quantity,
                            'price' => (int) $item->price,
                            'subtotal' => (int) $item->subtotal,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    @endphp
    <script>
        const returnItems = @json($items);
        const returnUpdateUrlTemplate = @json(route('return-requests.update', ['returnRequest' => '__ID__']));
        const csrfTokenReturn = @json(csrf_token());

        function returnStatusBadge(status) {
            const s = String(status || '').toLowerCase();
            const map = {
                menunggu: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                disetujui: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                ditolak: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                diproses: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                selesai: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
            };
            return `<span class="px-2.5 py-1 rounded-full text-xs font-semibold ${map[s] || map.menunggu}">${s || '-'}</span>`;
        }

        function renderReturnRow(item) {
            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3.5">
                        <div class="font-semibold text-slate-800 dark:text-slate-200">${item.request_no}</div>
                        <div class="text-xs text-slate-400 mt-0.5">${item.invoice_no} &bull; ${item.created_at}</div>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="text-slate-700 dark:text-slate-300">${item.customer}</div>
                        <div class="text-xs text-slate-400">${item.email}</div>
                    </td>
                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">${item.type === 'refund' ? 'Refund uang' : 'Ganti barang'}</td>
                    <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">Rp ${Number(item.refund_amount || 0).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-3.5">${returnStatusBadge(item.status)}</td>
                    <td class="px-4 py-3.5">
                        <button type="button" onclick="openReturnDetailModal(${item.id})" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Detail</button>
                    </td>
                </tr>
            `;
        }

        function openReturnDetailModal(id) {
            const item = returnItems.find((row) => Number(row.id) === Number(id));
            if (!item) return;

            document.getElementById('returnDetailNo').textContent = `${item.request_no} / ${item.invoice_no}`;
            const products = (item.items || []).map((row) => `
                <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-100 dark:border-slate-700 p-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">${row.product_name}${row.variant_name ? ` <span class="text-xs text-slate-400 font-normal">(${row.variant_name})</span>` : ''}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">${row.quantity} x Rp ${Number(row.price || 0).toLocaleString('id-ID')}</p>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Rp ${Number(row.subtotal || 0).toLocaleString('id-ID')}</p>
                </div>
            `).join('');

            const photos = (item.photos || []).length ? `
                <div class="flex flex-wrap gap-2">
                    ${(item.photos || []).map((photo) => `<a href="${photo}" target="_blank" class="block w-20 h-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700"><img src="${photo}" class="w-full h-full object-cover" /></a>`).join('')}
                </div>
            ` : '<p class="text-sm text-slate-400">Tidak ada foto bukti.</p>';

            document.getElementById('returnDetailContent').innerHTML = `
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/40 p-4">
                        <p class="text-xs text-slate-400 mb-1">Customer</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">${item.customer}</p>
                        <p class="text-slate-500 dark:text-slate-400">${item.email}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/40 p-4">
                        <p class="text-xs text-slate-400 mb-1">Status</p>
                        ${returnStatusBadge(item.status)}
                        <p class="mt-2 font-semibold text-blue-600 dark:text-blue-300">Rp ${Number(item.refund_amount || 0).toLocaleString('id-ID')}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Produk</h4>
                    <div class="space-y-2">${products}</div>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Alasan Customer</h4>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/40 p-4 text-sm text-slate-600 dark:text-slate-300">
                        <p>${item.reason}</p>
                        ${item.customer_note ? `<p class="mt-2 text-slate-500 dark:text-slate-400">${item.customer_note}</p>` : ''}
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Foto Bukti</h4>
                    ${photos}
                </div>
                <form method="POST" action="${returnUpdateUrlTemplate.replace('__ID__', item.id)}" class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                    <input type="hidden" name="_token" value="${csrfTokenReturn}">
                    <input type="hidden" name="_method" value="PATCH">
                    <div>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 block">Update Status</label>
                        <select name="status" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            ${['menunggu', 'disetujui', 'ditolak', 'diproses', 'selesai'].map((status) => `<option value="${status}" ${status === item.status ? 'selected' : ''}>${status}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 block">Catatan Admin</label>
                        <textarea name="admin_note" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-sm bg-white dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none h-24" placeholder="Catatan transfer refund, alasan penolakan, atau informasi barang pengganti">${item.admin_note || ''}</textarea>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Simpan Status</button>
                </form>
            `;

            document.getElementById('returnDetailModal').classList.remove('hidden');
            document.getElementById('returnDetailModal').classList.add('flex');
            if (window.lucide) window.lucide.createIcons();
        }

        function closeReturnDetailModal() {
            document.getElementById('returnDetailModal').classList.add('hidden');
            document.getElementById('returnDetailModal').classList.remove('flex');
        }

        initAdminDataTable({
            data: returnItems,
            perPage: 10,
            itemLabel: 'pengajuan',
            searchInputId: 'returnSearch',
            tbodyId: 'returnTableBody',
            paginationInfoId: 'returnPaginationInfo',
            paginationButtonsId: 'returnPaginationButtons',
            searchFields: ['request_no', 'invoice_no', 'customer', 'email'],
            filters: [{ elementId: 'returnStatusFilter', field: 'status' }],
            renderRow: (item) => renderReturnRow(item),
            emptyRowHtml: '<tr><td colspan="6" class="text-center py-12 text-slate-400 dark:text-slate-500">Belum ada pengajuan return/refund</td></tr>',
        });
    </script>
@endsection
