@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Product Management</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data produk dengan tampilan datatable.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="openImportModal()"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-emerald-200 dark:shadow-emerald-900/40">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import Excel
                </button>
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/40">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add New Product
                </a>
            </div>
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
                    <input id="productTableSearch" type="text" placeholder="Search product name..."
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
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 cursor-pointer select-none"
                                onclick="sortProductTable(1)">Name <span class="text-slate-300 dark:text-slate-600">↕</span>
                            </th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 cursor-pointer select-none"
                                onclick="sortProductTable(2)">Category <span
                                    class="text-slate-300 dark:text-slate-600">↕</span></th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400">Variants</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 cursor-pointer select-none"
                                onclick="sortProductTable(4)">Status <span
                                    class="text-slate-300 dark:text-slate-600">↕</span></th>
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
                <div
                    class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-2">Delete Product?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">This action cannot be undone.</p>
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

        <div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeImportModal()"></div>
            <div
                class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-700">
                {{-- Header --}}
                <div class="flex items-start justify-between p-6 pb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-800 dark:text-white">Import Product via Excel</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Upload file sesuai template untuk
                                import produk & varian</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeImportModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors ml-2 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 pb-6 space-y-4">
                    {{-- Download template --}}
                    <a href="{{ route('products.import-template') }}"
                        class="flex items-center gap-3 p-3.5 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors group">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-800/50 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-sm font-semibold text-blue-700 dark:text-blue-300 group-hover:text-blue-800 dark:group-hover:text-blue-200">
                                Download Template Excel</p>
                            <p class="text-xs text-blue-500 dark:text-blue-400">Belum punya template? Unduh di sini</p>
                        </div>
                        <svg class="w-4 h-4 text-blue-400 dark:text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf
                        {{-- Custom file input --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">File Excel
                                (.xlsx / .xls)</label>
                            <input id="import_file" name="import_file" type="file" accept=".xlsx,.xls"
                                class="hidden" onchange="handleFileChange(this)" />
                            <label for="import_file" id="file-drop-area"
                                class="flex flex-col items-center justify-center gap-2 w-full py-7 px-4 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/30 cursor-pointer hover:border-emerald-400 dark:hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 transition-all">
                                <div id="file-icon-wrap"
                                    class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-400 dark:text-slate-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div id="file-placeholder" class="text-center">
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Klik untuk pilih
                                        file</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">atau drag & drop di sini
                                    </p>
                                </div>
                                <div id="file-chosen" class="hidden text-center">
                                    <p id="file-name"
                                        class="text-sm font-semibold text-emerald-700 dark:text-emerald-400 break-all"></p>
                                    <p id="file-size" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"></p>
                                </div>
                            </label>
                            @error('import_file')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="button" onclick="closeImportModal()"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-colors shadow-sm shadow-emerald-200 dark:shadow-none">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Upload & Import
                            </button>
                        </div>
                    </form>
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
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => trim(($p->mainCategory?->name ?? '-') . ' > ' . ($p->categoryDetail?->name ?? '-')),
                    'variants_count' => $p->productVariants->count(),
                    'status' => $p->status,
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
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${product.category}</td>
                    <td class="px-4 py-3.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">${product.variants_count} varian</span>
                    </td>
                    <td class="px-4 py-3.5">${statusBadge}</td>
                    <td class="px-4 py-3.5">
                        <div class="flex gap-1">
                            <a href="${editUrl}" class="h-fit p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Edit">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <form id="${formId}" action="${deleteUrl}" method="POST">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" onclick="openDeleteModal('${formId}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>`;
        }

        function sortProductTable(col) {
            productTableApi?.sortBy(col);
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

        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.getElementById('importModal').classList.add('flex');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.getElementById('importModal').classList.remove('flex');
            resetFileInput();
        }

        function handleFileChange(input) {
            const placeholder = document.getElementById('file-placeholder');
            const chosen = document.getElementById('file-chosen');
            const nameEl = document.getElementById('file-name');
            const sizeEl = document.getElementById('file-size');
            const iconWrap = document.getElementById('file-icon-wrap');
            const dropArea = document.getElementById('file-drop-area');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                nameEl.textContent = file.name;
                sizeEl.textContent = sizeMB + ' MB';
                placeholder.classList.add('hidden');
                chosen.classList.remove('hidden');
                iconWrap.innerHTML =
                    `<svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
                iconWrap.className =
                    'w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center';
                dropArea.classList.add('border-emerald-400', 'dark:border-emerald-500', 'bg-emerald-50/50');
                dropArea.classList.remove('border-slate-200', 'dark:border-slate-600', 'bg-slate-50');
            }
        }

        function resetFileInput() {
            const placeholder = document.getElementById('file-placeholder');
            const chosen = document.getElementById('file-chosen');
            const iconWrap = document.getElementById('file-icon-wrap');
            const dropArea = document.getElementById('file-drop-area');
            document.getElementById('import_file').value = '';
            placeholder.classList.remove('hidden');
            chosen.classList.add('hidden');
            iconWrap.innerHTML =
                `<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>`;
            iconWrap.className = 'w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center';
            dropArea.classList.remove('border-emerald-400', 'dark:border-emerald-500', 'bg-emerald-50/50');
            dropArea.classList.add('border-slate-200', 'dark:border-slate-600', 'bg-slate-50');
        }
        document.addEventListener('DOMContentLoaded', function() {
            const dropArea = document.getElementById('file-drop-area');
            if (!dropArea) return;
            ['dragenter', 'dragover'].forEach(evt => dropArea.addEventListener(evt, e => {
                e.preventDefault();
                dropArea.classList.add('border-emerald-400');
            }));
            ['dragleave', 'drop'].forEach(evt => dropArea.addEventListener(evt, e => {
                e.preventDefault();
                dropArea.classList.remove('border-emerald-400');
            }));
            dropArea.addEventListener('drop', function(e) {
                const fileInput = document.getElementById('import_file');
                fileInput.files = e.dataTransfer.files;
                handleFileChange(fileInput);
            });
        });

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
            searchFields: ['name', 'category'],
            filters: [{
                elementId: 'productStatusFilter',
                field: 'status'
            }],
            sortMap: {
                1: (p) => p.name,
                2: (p) => p.category,
                4: (p) => p.status,
            },
            renderRow: (product, index) => buildProductRow(product, index),
            emptyRowHtml: '<tr><td colspan="6" class="text-center py-12 text-slate-400 dark:text-slate-500">No products found</td></tr>',
        });

        const toast = document.getElementById('toast');
        if (toast) setTimeout(() => toast.remove(), 3000);
        @if ($errors->has('import_file'))
            openImportModal();
        @endif
    </script>
@endsection
