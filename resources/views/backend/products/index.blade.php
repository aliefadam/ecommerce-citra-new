@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <main class="flex-1 p-4 sm:p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Product Management</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data produk dengan tampilan datatable.</p>
            </div>
            <a href="{{ route('products.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/40">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Add New Product
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input id="productTableSearch" type="text" placeholder="Search product name or SKU..."
                        class="pl-9 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                </div>
                <div class="flex gap-2">
                    <select id="productStatusFilter"
                        class="text-sm bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">#</th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400"
                                onclick="sortProductTable(1)">Name <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400"
                                onclick="sortProductTable(2)">SKU <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400"
                                onclick="sortProductTable(3)">Price <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400"
                                onclick="sortProductTable(4)">Stock <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="sortable text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400"
                                onclick="sortProductTable(5)">Status <span class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                </table>
            </div>

            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <p id="productPaginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div class="flex items-center gap-1" id="productPaginationButtons"></div>
            </div>
        </div>

        <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
            <div
                class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-200 dark:border-slate-700 p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-2">Delete Product?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">This action cannot be undone. Are you sure you
                    want to delete this product?</p>
                <div class="flex gap-3">
                    <button id="deleteCancelBtn" type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button id="deleteConfirmBtn" type="button" onclick="confirmDelete()"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                        <span id="deleteConfirmText">Delete</span>
                        <span id="deleteConfirmLoading" class="hidden items-center gap-2">
                            <span class="w-4 h-4 rounded-full border-2 border-red-200 border-t-white animate-spin"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div id="toast" class="fixed bottom-6 right-6 z-50">
                <div
                    class="flex items-center gap-3 bg-slate-800 dark:bg-white text-white dark:text-slate-800 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
    </main>
@endsection

@section('script')
    @php
        $productItems = $products
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => (float) $product->price,
                    'price_label' => 'Rp ' . number_format((float) $product->price, 0, ',', '.'),
                    'stock' => (int) $product->stock,
                    'status' => $product->status,
                    'status_label' => ucfirst($product->status),
                ];
            })
            ->values()
            ->all();
    @endphp
    <script>
        const productItems = @json($productItems);
        const editUrlTemplate = @json(route('products.edit', ['product' => '__ID__']));
        const deleteUrlTemplate = @json(route('products.destroy', ['product' => '__ID__']));
        const csrfToken = @json(csrf_token());

        let productTableApi = null;
        let deletingFormId = null;

        function buildProductRow(product, visibleIndex) {
            const statusBadge = product.status === 'active' ?
                '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Active</span>' :
                '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Inactive</span>';

            const editUrl = editUrlTemplate.replace('__ID__', product.id);
            const deleteUrl = deleteUrlTemplate.replace('__ID__', product.id);
            const formId = `delete-product-${product.id}`;

            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${visibleIndex + 1}</td>
                    <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">${product.name}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${product.sku}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${product.price_label}</td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${product.stock}</td>
                    <td class="px-4 py-3.5">${statusBadge}</td>
                    <td class="px-4 py-3.5">
                        <div class="flex gap-1">
                            <a href="${editUrl}" class="h-fit p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Edit">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </a>
                            <form id="${formId}" action="${deleteUrl}" method="POST">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" onclick="openDeleteModal('${formId}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `;
        }

        function sortProductTable(columnIndex) {
            productTableApi?.sortBy(columnIndex);
        }

        function openDeleteModal(formId) {
            deletingFormId = formId;
            setDeleteButtonLoading(false);
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            setDeleteButtonLoading(false);
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
            deletingFormId = null;
        }

        function setDeleteButtonLoading(isLoading) {
            const confirmBtn = document.getElementById('deleteConfirmBtn');
            const cancelBtn = document.getElementById('deleteCancelBtn');
            const text = document.getElementById('deleteConfirmText');
            const loading = document.getElementById('deleteConfirmLoading');
            if (!confirmBtn || !cancelBtn || !text || !loading) return;

            confirmBtn.disabled = isLoading;
            cancelBtn.disabled = isLoading;
            confirmBtn.classList.toggle('opacity-70', isLoading);
            confirmBtn.classList.toggle('cursor-not-allowed', isLoading);
            cancelBtn.classList.toggle('opacity-70', isLoading);
            cancelBtn.classList.toggle('cursor-not-allowed', isLoading);
            text.classList.toggle('hidden', isLoading);
            loading.classList.toggle('hidden', !isLoading);
            loading.classList.toggle('inline-flex', isLoading);
        }

        function confirmDelete() {
            if (deletingFormId) {
                setDeleteButtonLoading(true);
                document.getElementById(deletingFormId).submit();
            }
        }

        productTableApi = initAdminDataTable({
            data: productItems,
            perPage: 10,
            itemLabel: 'products',
            searchInputId: 'productTableSearch',
            tbodyId: 'productTableBody',
            paginationInfoId: 'productPaginationInfo',
            paginationButtonsId: 'productPaginationButtons',
            searchFields: ['name', 'sku'],
            filters: [{
                elementId: 'productStatusFilter',
                field: 'status'
            }],
            sortMap: {
                1: (p) => p.name,
                2: (p) => p.sku,
                3: (p) => p.price,
                4: (p) => p.stock,
                5: (p) => p.status,
            },
            renderRow: (product, index) => buildProductRow(product, index),
            emptyRowHtml: '<tr><td colspan="7" class="text-center py-12 text-slate-400 dark:text-slate-500">No products found</td></tr>',
        });

        const toast = document.getElementById('toast');
        if (toast) {
            setTimeout(() => toast.remove(), 3000);
        }
    </script>
@endsection
