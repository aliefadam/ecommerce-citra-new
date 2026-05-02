@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    @php
        $oldCatId   = old('category_id', $product->category_id);
        $oldCatName = $oldCatId ? ($categories->find($oldCatId)?->name ?? '') : '';

        if (old('variants')) {
            $oldVariants = collect(old('variants'))->map(function ($v) use ($variants) {
                $variantModel = $variants->find($v['variant_id'] ?? null);
                return ['variantId' => (int)($v['variant_id'] ?? 0) ?: null, 'variantName' => $variantModel?->name ?? '', 'sku' => $v['sku'] ?? '', 'price' => $v['price'] ?? '', 'stock' => $v['stock'] ?? ''];
            })->values()->toArray();
        } else {
            $oldVariants = $product->productVariants->map(fn($pv) => ['variantId' => $pv->variant_id, 'variantName' => $pv->variant?->name ?? '', 'sku' => $pv->sku ?? '', 'price' => $pv->price, 'stock' => $pv->stock])->values()->toArray();
            if (empty($oldVariants)) {
                $oldVariants = [['variantId' => null, 'variantName' => '', 'sku' => '', 'price' => '', 'stock' => '']];
            }
        }
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Product</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Perbarui data produk yang dipilih.</p>
        </div>

        <form action="{{ route('products.update', $product) }}" method="POST"
            x-data="productForm({
                categories: {{ $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]) }},
                allVariants: {{ $variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name]) }},
                oldCategoryId: {{ $oldCatId ?? 'null' }},
                oldCategoryName: {{ json_encode($oldCatName) }},
                oldRows: {{ json_encode($oldVariants) }},
                categoryQuickAddUrl: {{ json_encode(route('categories.quick-add')) }},
                variantQuickAddUrl: {{ json_encode(route('variants.quick-add')) }},
            })">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kolom kiri --}}
                <div class="lg:col-span-2 space-y-5">

                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Informasi Produk</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Produk</label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" placeholder="Masukkan nama produk..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('name') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                                @error('name')
                                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
                                <textarea name="description" rows="4" placeholder="Deskripsi produk (opsional)..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('description') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Varian --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300">Varian Produk</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Minimal satu varian harus diisi.</p>
                            </div>
                            <a href="{{ route('variants.create') }}" target="_blank"
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Kelola Master Varian
                            </a>
                        </div>

                        @error('variants')
                            <div class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700 rounded-lg text-xs text-red-500">{{ $message }}</div>
                        @enderror

                        <div class="hidden sm:grid grid-cols-[2fr_1fr_1fr_1fr_auto] gap-2 mb-2 px-1">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Nama Varian <span class="text-red-400">*</span></span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">SKU</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Harga (Rp) <span class="text-red-400">*</span></span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Stok <span class="text-red-400">*</span></span>
                            <span class="w-8"></span>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-2 items-start p-3 sm:p-0 bg-slate-50 dark:bg-slate-700/30 sm:bg-transparent rounded-xl sm:rounded-none border sm:border-0 border-slate-200 dark:border-slate-600">

                                    <div class="relative" @click.outside="row.open = false">
                                        <div @click="row.open = true"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm rounded-xl border cursor-pointer border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 transition-all">
                                            <input type="text" x-model="row.variantSearch"
                                                @input="row.open = true; row.variantId = null"
                                                @focus="row.open = true"
                                                placeholder="Cari atau tambah varian..."
                                                class="flex-1 bg-transparent outline-none text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 min-w-0" />
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 flex-shrink-0"><polyline points="6 9 12 15 18 9"/></svg>
                                        </div>
                                        <input type="hidden" :name="`variants[${index}][variant_id]`" :value="row.variantId ?? ''">
                                        <div x-show="row.open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                            class="absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                                            <template x-for="v in filteredVariants(row.variantSearch)" :key="v.id">
                                                <button type="button" @click="selectVariant(row, v)"
                                                    class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors"
                                                    :class="row.variantId === v.id ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 font-medium' : ''"
                                                    x-text="v.name"></button>
                                            </template>
                                            <button type="button" x-show="variantShowAddNew(row)" @click="addNewVariant(row)"
                                                class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors border-t border-slate-100 dark:border-slate-700 flex items-center gap-2">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                Tambah "<span x-text="row.variantSearch"></span>"
                                            </button>
                                            <div x-show="filteredVariants(row.variantSearch).length === 0 && !variantShowAddNew(row)" class="px-3 py-2 text-sm text-slate-400">Tidak ada varian</div>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="sm:hidden text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">SKU</span>
                                        <input type="text" :name="`variants[${index}][sku]`" x-model="row.sku" placeholder="SKU (opsional)"
                                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    </div>
                                    <div>
                                        <span class="sm:hidden text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Harga (Rp) *</span>
                                        <input type="number" min="0" step="100" :name="`variants[${index}][price]`" x-model="row.price" placeholder="0"
                                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    </div>
                                    <div>
                                        <span class="sm:hidden text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Stok *</span>
                                        <input type="number" min="0" :name="`variants[${index}][stock]`" x-model="row.stock" placeholder="0"
                                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    </div>
                                    <div class="flex items-center justify-end sm:justify-center pt-1 sm:pt-0">
                                        <button type="button" @click="removeRow(row.id)"
                                            :disabled="rows.length <= 1"
                                            :class="rows.length <= 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-500'"
                                            class="p-1.5 rounded-lg text-slate-400 transition-colors">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addRow"
                            class="mt-3 flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium px-1 py-1 transition-colors">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Tambah Varian
                        </button>
                    </div>

                </div>

                {{-- Kolom kanan --}}
                <div class="space-y-5">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Kategori</h2>
                        <div class="relative" @click.outside="categoryOpen = false">
                            <input type="text" x-model="categorySearch"
                                @input="categoryOpen = true; categoryId = null"
                                @focus="categoryOpen = true"
                                placeholder="Cari atau tambah kategori..."
                                class="w-full px-4 py-2.5 text-sm rounded-xl border focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('category_id') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                            <input type="hidden" name="category_id" :value="categoryId ?? ''">
                            <div x-show="categoryOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                                <template x-for="cat in filteredCategories" :key="cat.id">
                                    <button type="button" @click="selectCategory(cat)"
                                        class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors"
                                        :class="categoryId === cat.id ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 font-medium' : ''"
                                        x-text="cat.name"></button>
                                </template>
                                <button type="button" x-show="categoryShowAddNew" @click="addNewCategory"
                                    class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors border-t border-slate-100 dark:border-slate-700 flex items-center gap-2">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Tambah "<span x-text="categorySearch"></span>"
                                </button>
                                <div x-show="filteredCategories.length === 0 && !categoryShowAddNew" class="px-3 py-2 text-sm text-slate-400">Tidak ada kategori</div>
                            </div>
                        </div>
                        @error('category_id')
                            <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Status</h2>
                        <select name="status"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button type="submit"
                            class="w-full px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                            Update Product
                        </button>
                        <a href="{{ route('products.index') }}"
                            class="w-full text-center px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div id="toast" class="fixed bottom-6 right-6 z-50">
                    <div class="flex items-center gap-3 bg-slate-800 dark:bg-white text-white dark:text-slate-800 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
        </form>
    </main>
@endsection

@section('script')
    <script>
        function productForm({ categories, allVariants, oldCategoryId, oldCategoryName, oldRows, categoryQuickAddUrl, variantQuickAddUrl }) {
            return {
                categories, allVariants,
                categorySearch: oldCategoryName || '', categoryId: oldCategoryId || null, categoryOpen: false,
                get filteredCategories() {
                    if (!this.categorySearch.trim()) return this.categories;
                    return this.categories.filter(c => c.name.toLowerCase().includes(this.categorySearch.toLowerCase()));
                },
                get categoryShowAddNew() {
                    const s = this.categorySearch.trim().toLowerCase();
                    return s && !this.filteredCategories.some(c => c.name.toLowerCase() === s);
                },
                selectCategory(cat) { this.categoryId = cat.id; this.categorySearch = cat.name; this.categoryOpen = false; },
                async addNewCategory() {
                    const name = this.categorySearch.trim(); if (!name) return;
                    try { const res = await fetch(categoryQuickAddUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ name }) }); const data = await res.json(); this.categories = [...this.categories, data]; this.selectCategory(data); } catch {}
                },
                rows: oldRows.map((r, i) => ({ id: i, variantId: r.variantId, variantSearch: r.variantName || '', variantOpen: false, sku: r.sku || '', price: r.price || '', stock: r.stock || '' })),
                nextId: oldRows.length,
                addRow() { this.rows.push({ id: this.nextId++, variantId: null, variantSearch: '', variantOpen: false, sku: '', price: '', stock: '' }); },
                removeRow(id) { if (this.rows.length <= 1) return; this.rows = this.rows.filter(r => r.id !== id); },
                filteredVariants(search) {
                    if (!search || !search.trim()) return this.allVariants;
                    return this.allVariants.filter(v => v.name.toLowerCase().includes(search.toLowerCase()));
                },
                variantShowAddNew(row) {
                    const s = (row.variantSearch || '').trim().toLowerCase();
                    return s && !this.filteredVariants(row.variantSearch).some(v => v.name.toLowerCase() === s);
                },
                selectVariant(row, variant) { row.variantId = variant.id; row.variantSearch = variant.name; row.variantOpen = false; },
                async addNewVariant(row) {
                    const name = (row.variantSearch || '').trim(); if (!name) return;
                    try { const res = await fetch(variantQuickAddUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ name }) }); const data = await res.json(); this.allVariants = [...this.allVariants, data]; this.selectVariant(row, data); } catch {}
                },
            };
        }
        const toast = document.getElementById('toast');
        if (toast) setTimeout(() => toast.remove(), 3000);
    </script>
@endsection
