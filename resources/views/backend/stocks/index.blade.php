@extends('layouts.app')

@section('title', 'Stock')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Stock</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Riwayat stok barang masuk dan keluar per varian produk.</p>
            </div>
            <button type="button" onclick="openStockModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Data
            </button>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        @php
            $lowStockVariants = $variants->filter(fn($v) => (int) $v->stock <= (int) ($v->low_stock_threshold ?? 10))->values();
        @endphp
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-900/20 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-3">
                <div>
                    <h2 class="font-bold text-amber-800 dark:text-amber-300">Low Stock Alert</h2>
                    <p class="text-sm text-amber-700 dark:text-amber-400">{{ $lowStockVariants->count() }} varian berada di bawah batas stok rendah.</p>
                </div>
            </div>
            @if ($lowStockVariants->isNotEmpty())
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach ($lowStockVariants->take(6) as $variant)
                        @php
                            $variantName = $variant->attributeSummary();
                        @endphp
                        <form method="POST" action="{{ route('stocks.threshold', $variant) }}" class="rounded-xl bg-white/80 dark:bg-slate-800/80 border border-amber-100 dark:border-amber-900/50 p-3">
                            @csrf
                            @method('PATCH')
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $variant->product?->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $variantName ?: 'Variant' }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs font-bold px-2 py-1 rounded-full {{ (int) $variant->stock === 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">Stok {{ (int) $variant->stock }}</span>
                                <input type="number" min="0" name="low_stock_threshold" value="{{ (int) ($variant->low_stock_threshold ?? 10) }}"
                                    class="w-20 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-2 py-1 text-xs dark:text-slate-200">
                                <button class="text-xs font-semibold text-blue-600 hover:underline">Simpan batas</button>
                            </div>
                        </form>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input id="stockSearch" type="text" placeholder="Cari produk / varian / deskripsi..."
                        class="pl-9 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 w-12">#</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Produk / Varian</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Tipe</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Jumlah</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Stok</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Deskripsi</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <p id="stockPaginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div class="flex items-center gap-1" id="stockPaginationButtons"></div>
            </div>
        </div>

        <div id="stockModal" class="fixed inset-0 z-[99998] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeStockModal()"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-xl border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Tambah Data Stock</h3>
                    <button type="button" onclick="closeStockModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('stocks.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Varian Produk</label>
                        <div class="relative">
                            <input id="variantSearchInput" type="text"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Cari varian produk..." autocomplete="off">
                            <input type="hidden" name="product_variant_id" id="variantIdInput" required>
                            <div id="variantDropdown"
                                class="hidden absolute z-20 mt-1 w-full max-h-56 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-lg"></div>
                        </div>
                        <p id="variantSelectedText" class="mt-1 text-xs text-slate-500 dark:text-slate-400"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tipe</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2.5 cursor-pointer">
                                <input type="radio" name="type" value="in" class="text-blue-500 focus:ring-blue-400" checked>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Stok Masuk</span>
                            </label>
                            <label class="flex items-center gap-2 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2.5 cursor-pointer">
                                <input type="radio" name="type" value="out" class="text-blue-500 focus:ring-blue-400">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Stok Keluar</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah</label>
                        <input name="quantity" type="number" min="1" required
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan jumlah">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Deskripsi</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Opsional"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Simpan
                    </button>
                </form>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @php
        $stockItems = $movements
            ->map(function ($m) {
                $productName = (string) ($m->productVariant?->product?->name ?? '-');
                $variantText = (string) ($m->productVariant?->attributeSummary() ?? '');
                return [
                    'id' => $m->id,
                    'product' => $productName,
                    'variant' => $variantText ?: '-',
                    'type' => $m->type,
                    'quantity' => (int) $m->quantity,
                    'stock_before' => (int) $m->stock_before,
                    'stock_after' => (int) $m->stock_after,
                    'description' => (string) ($m->description ?? ''),
                    'source' => (string) ($m->source ?? 'manual'),
                    'created_at' => optional($m->created_at)->format('d M Y H:i'),
                ];
            })
            ->values()
            ->all();

        $variantItems = $variants
            ->map(function ($v) {
                $productName = (string) ($v->product?->name ?? '-');
                $variantText = (string) $v->skuLabel();
                return [
                    'id' => $v->id,
                    'label' => $productName . ' - ' . ($variantText ?: 'Variant') . ' (stok: ' . ((int) $v->stock) . ')',
                ];
            })
            ->values()
            ->all();
    @endphp
    <script>
        const stockItems = @json($stockItems);
        const variantItems = @json($variantItems);

        function typeBadge(type) {
            if (String(type) === 'in') {
                return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Masuk</span>';
            }
            return '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Keluar</span>';
        }

        function renderStockRow(item, visibleIndex) {
            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${visibleIndex + 1}</td>
                    <td class="px-4 py-3.5">
                        <div class="font-medium text-slate-800 dark:text-slate-200">${item.product}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">${item.variant}</div>
                    </td>
                    <td class="px-4 py-3.5">${typeBadge(item.type)}</td>
                    <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">${Number(item.quantity || 0).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${item.stock_before} → <span class="font-semibold text-slate-700 dark:text-slate-300">${item.stock_after}</span></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${item.description || '-'}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${item.created_at || '-'}</td>
                </tr>
            `;
        }

        initAdminDataTable({
            data: stockItems,
            perPage: 10,
            itemLabel: 'stock movements',
            searchInputId: 'stockSearch',
            tbodyId: 'stockTableBody',
            paginationInfoId: 'stockPaginationInfo',
            paginationButtonsId: 'stockPaginationButtons',
            searchFields: ['product', 'variant', 'description', 'source'],
            renderRow: (item, index) => renderStockRow(item, index),
            emptyRowHtml: '<tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-slate-500">No stock data found</td></tr>',
        });

        function openStockModal() {
            const modal = document.getElementById('stockModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('variantSearchInput')?.focus();
        }

        function closeStockModal() {
            const modal = document.getElementById('stockModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            hideVariantDropdown();
        }

        function hideVariantDropdown() {
            document.getElementById('variantDropdown')?.classList.add('hidden');
        }

        function renderVariantDropdown(query = '') {
            const dropdown = document.getElementById('variantDropdown');
            if (!dropdown) return;
            const q = String(query || '').toLowerCase().trim();
            const filtered = variantItems.filter((v) => v.label.toLowerCase().includes(q)).slice(0, 30);
            if (!filtered.length) {
                dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-slate-400">Data varian tidak ditemukan</div>';
                dropdown.classList.remove('hidden');
                return;
            }
            dropdown.innerHTML = filtered.map((v) =>
                `<button type="button" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700" onclick="selectVariant(${v.id})">${v.label}</button>`
            ).join('');
            dropdown.classList.remove('hidden');
        }

        function selectVariant(id) {
            const item = variantItems.find((v) => Number(v.id) === Number(id));
            if (!item) return;
            const searchInput = document.getElementById('variantSearchInput');
            const idInput = document.getElementById('variantIdInput');
            const selectedText = document.getElementById('variantSelectedText');
            if (searchInput) searchInput.value = item.label;
            if (idInput) idInput.value = String(item.id);
            if (selectedText) selectedText.textContent = 'Terpilih: ' + item.label;
            hideVariantDropdown();
        }

        document.getElementById('variantSearchInput')?.addEventListener('focus', function() {
            renderVariantDropdown(this.value);
        });
        document.getElementById('variantSearchInput')?.addEventListener('input', function() {
            document.getElementById('variantIdInput').value = '';
            renderVariantDropdown(this.value);
        });
        document.addEventListener('click', function(e) {
            const wrap = document.getElementById('variantSearchInput')?.closest('.relative');
            if (wrap && !wrap.contains(e.target)) {
                hideVariantDropdown();
            }
        });
    </script>
@endsection
