@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    @php
        $oldCatId = old('category_id', old('category_detail_id', $product->category_detail_id));
        $oldCatName = $oldCatId ? $categories->find($oldCatId)?->name ?? '' : '';

        if (old('variants')) {
            $oldVariants = collect(old('variants'))
                ->map(function ($v) use ($variants) {
                    $variantModel = $variants->find($v['variant_id'] ?? null);
                    return [
                        'variantId' => (int) ($v['variant_id'] ?? 0) ?: null,
                        'variantName' => $variantModel?->name ?? '',
                        'variantValue' => $variantModel?->value ?? '',
                        'sku' => $v['sku'] ?? '',
                        'price' => $v['price'] ?? '',
                        'stock' => $v['stock'] ?? '',
                        'imagePath' => !empty($v['existing_image'])
                            ? (\Illuminate\Support\Str::startsWith($v['existing_image'], ['http://', 'https://'])
                                ? $v['existing_image']
                                : asset('storage/' . $v['existing_image']))
                            : null,
                        'imageStoredPath' => $v['existing_image'] ?? '',
                    ];
                })
                ->values()
                ->toArray();
        } else {
            $oldVariants = $product->productVariants
                ->map(function ($pv) {
                    return [
                        'variantId' => $pv->variant_id,
                        'variantName' => $pv->variant?->name ?? '',
                        'variantValue' => $pv->variant?->value ?? '',
                        'sku' => $pv->sku ?? '',
                        'price' => $pv->price,
                        'stock' => $pv->stock,
                        'imagePath' => $pv->image
                            ? (\Illuminate\Support\Str::startsWith($pv->image, ['http://', 'https://'])
                                ? $pv->image
                                : asset('storage/' . $pv->image))
                            : null,
                        'imageStoredPath' => $pv->image ?? '',
                    ];
                })
                ->values()
                ->toArray();
        }

        if (empty($oldVariants)) {
            $oldVariants = [
                [
                    'variantId' => null,
                    'variantName' => '',
                    'variantValue' => '',
                    'sku' => '',
                    'price' => '',
                    'stock' => '',
                    'imagePath' => null,
                    'imageStoredPath' => '',
                ],
            ];
        }
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Product</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Perbarui data produk yang dipilih.</p>
        </div>

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
            x-data="productForm({
                categories: {{ $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'group' => $c->group_name ?? '-', 'detail' => $c->detail_name ?? $c->name]) }},
                allVariants: {{ $variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'value' => $v->value]) }},
                oldProductName: {{ json_encode(old('name', $product->name)) }},
                oldCategoryId: {{ $oldCatId ?? 'null' }},
                oldCategoryName: {{ json_encode($oldCatName) }},
                oldRows: {{ json_encode($oldVariants) }},
                variantQuickAddUrl: {{ json_encode(route('variants.quick-add')) }},
            })">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kolom kiri --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Informasi Produk --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Informasi Produk</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama
                                    Produk</label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                    x-model="productName"
                                    placeholder="Masukkan nama produk..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('name') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                                @error('name')
                                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="12" y1="8" x2="12" y2="12" />
                                            <line x1="12" y1="16" x2="12.01" y2="16" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
                                <div
                                    class="rounded-xl overflow-hidden border {{ $errors->has('description') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600' : 'border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700' }}">
                                    <div
                                        class="flex flex-wrap items-center gap-1 p-2 border-b border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800">
                                        <button type="button" data-editor-action="bold"
                                            class="px-2 py-1 text-xs rounded hover:bg-slate-100 dark:hover:bg-slate-700">Bold</button>
                                        <button type="button" data-editor-action="italic"
                                            class="px-2 py-1 text-xs rounded hover:bg-slate-100 dark:hover:bg-slate-700">Italic</button>
                                        <button type="button" data-editor-action="insertUnorderedList"
                                            class="px-2 py-1 text-xs rounded hover:bg-slate-100 dark:hover:bg-slate-700">Bullet</button>
                                        <button type="button" data-editor-action="insertOrderedList"
                                            class="px-2 py-1 text-xs rounded hover:bg-slate-100 dark:hover:bg-slate-700">Number</button>
                                    </div>
                                    <div id="description-editor" contenteditable="true"
                                        class="min-h-[140px] px-4 py-3 text-sm focus:outline-none dark:text-slate-200">
                                        {!! old('description', $product->description) !!}</div>
                                </div>
                                <textarea id="description-input" name="description" class="hidden">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="12" y1="8" x2="12" y2="12" />
                                            <line x1="12" y1="16" x2="12.01" y2="16" />
                                        </svg>
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
                            <button type="button" @click="addRow"
                                class="mt-3 flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium px-1 py-1 transition-colors">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                Tambah Varian
                            </button>
                        </div>

                        @error('variants')
                            <div
                                class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700 rounded-lg text-xs text-red-500">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="space-y-3">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div
                                    class="border border-slate-200 dark:border-slate-600 rounded-xl p-4 space-y-3 bg-slate-50/50 dark:bg-slate-700/20">

                                    {{-- Tipe + Nilai + Delete --}}
                                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2 items-start">

                                        {{-- Tipe Varian --}}
                                        <div class="relative" @click.outside="row.nameOpen = false">
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tipe
                                                <span class="text-red-400">*</span></label>
                                            <div
                                                class="flex items-center gap-1 w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 cursor-text">
                                                <input type="text" x-model="row.nameSearch"
                                                    @input="row.nameOpen = true; row.selectedName = null; row.valueSearch = ''; row.variantId = null"
                                                    @focus="row.nameOpen = true" placeholder="Cari tipe..."
                                                    class="flex-1 bg-transparent outline-none text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 min-w-0" />
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="text-slate-400 flex-shrink-0">
                                                    <polyline points="6 9 12 15 18 9" />
                                                </svg>
                                            </div>
                                            <div x-show="row.nameOpen"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                class="absolute z-30 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-44 overflow-y-auto">
                                                <template x-for="n in filteredNames(row.nameSearch)"
                                                    :key="n">
                                                    <button type="button" @click="selectName(row, n)"
                                                        class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors"
                                                        :class="row.selectedName === n ?
                                                            'bg-blue-50 dark:bg-blue-900/20 text-blue-600 font-medium' :
                                                            ''"
                                                        x-text="n"></button>
                                                </template>
                                                <button type="button" x-show="nameShowAddNew(row)"
                                                    @click="addLocalName(row)"
                                                    class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors border-t border-slate-100 dark:border-slate-700 flex items-center gap-2">
                                                    <svg width="13" height="13" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2.5"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="12" y1="5" x2="12"
                                                            y2="19" />
                                                        <line x1="5" y1="12" x2="19"
                                                            y2="12" />
                                                    </svg>
                                                    Tambah "<span x-text="row.nameSearch"></span>"
                                                </button>
                                                <div x-show="filteredNames(row.nameSearch).length === 0 && !nameShowAddNew(row)"
                                                    class="px-3 py-2 text-sm text-slate-400">Ketik untuk mencari...</div>
                                            </div>
                                        </div>

                                        {{-- Nilai Varian --}}
                                        <div class="relative" @click.outside="row.valueOpen = false">
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nilai
                                                <span class="text-red-400">*</span></label>
                                            <div class="flex items-center gap-1 w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 cursor-text"
                                                :class="!row.selectedName ? 'opacity-60' : ''">
                                                <input type="text" x-model="row.valueSearch"
                                                    @input="row.valueOpen = true; row.variantId = null"
                                                    @focus="if(row.selectedName) row.valueOpen = true"
                                                    :disabled="!row.selectedName" placeholder="Cari nilai..."
                                                    class="flex-1 bg-transparent outline-none text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 min-w-0 disabled:cursor-not-allowed" />
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="text-slate-400 flex-shrink-0">
                                                    <polyline points="6 9 12 15 18 9" />
                                                </svg>
                                            </div>
                                            <input type="hidden" :name="`variants[${index}][variant_id]`"
                                                :value="row.variantId ?? ''">
                                            <input type="hidden" :name="`variants[${index}][existing_image]`"
                                                :value="row.imageStoredPath ?? ''">
                                            <div x-show="row.valueOpen && row.selectedName"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                class="absolute z-30 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-44 overflow-y-auto">
                                                <template x-for="v in filteredValues(row)" :key="v.id">
                                                    <button type="button" @click="selectValue(row, v)"
                                                        class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors"
                                                        :class="row.variantId === v.id ?
                                                            'bg-blue-50 dark:bg-blue-900/20 text-blue-600 font-medium' :
                                                            ''"
                                                        x-text="v.value"></button>
                                                </template>
                                                <button type="button" x-show="valueShowAddNew(row)"
                                                    @click="addNewValue(row)"
                                                    class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors border-t border-slate-100 dark:border-slate-700 flex items-center gap-2">
                                                    <svg width="13" height="13" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2.5"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="12" y1="5" x2="12"
                                                            y2="19" />
                                                        <line x1="5" y1="12" x2="19"
                                                            y2="12" />
                                                    </svg>
                                                    Tambah "<span x-text="row.valueSearch"></span>"
                                                </button>
                                                <div x-show="filteredValues(row).length === 0 && !valueShowAddNew(row)"
                                                    class="px-3 py-2 text-sm text-slate-400">Tidak ada nilai untuk tipe ini
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Delete button --}}
                                        <div class="flex items-end pb-0.5">
                                            <button type="button" @click="removeRow(row.id)"
                                                :disabled="rows.length <= 1"
                                                :class="rows.length <= 1 ? 'opacity-30 cursor-not-allowed' :
                                                    'hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-500'"
                                                class="mt-5 p-1.5 rounded-lg text-slate-400 transition-colors">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Gambar --}}
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Gambar</label>
                                        <div class="flex items-center gap-3">
                                            <div x-show="row.imagePreview" class="flex-shrink-0">
                                                <img :src="row.imagePreview"
                                                    class="w-14 h-14 object-cover rounded-lg border border-slate-200 dark:border-slate-600" />
                                            </div>
                                            <label
                                                class="flex-1 flex items-center gap-2 px-3 py-2.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-500 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition-colors bg-white dark:bg-slate-700/50">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="text-slate-400 flex-shrink-0">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                                    <polyline points="21 15 16 10 5 21" />
                                                </svg>
                                                <span class="text-xs text-slate-400 truncate"
                                                    x-text="row.imagePreview ? 'Ganti gambar...' : 'Pilih gambar...'"></span>
                                                <input type="file" :name="`variants[${index}][image]`"
                                                    accept="image/*" @change="handleImageChange(row, $event)"
                                                    class="hidden" />
                                            </label>
                                        </div>
                                    </div>

                                    {{-- SKU + Harga + Stok --}}
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">SKU</label>
                                            <input type="text" :name="`variants[${index}][sku]`" :value="generatedSku(row)"
                                                readonly
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/70 dark:text-slate-200 text-slate-600 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga
                                                (Rp) <span class="text-red-400">*</span></label>
                                            <input type="number" min="0" step="100"
                                                :name="`variants[${index}][price]`" x-model="row.price" placeholder="0"
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Stok
                                                <span class="text-red-400">*</span></label>
                                            <input type="number" min="0" :name="`variants[${index}][stock]`"
                                                x-model="row.stock" placeholder="0"
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>

                    </div>

                </div>

                {{-- Kolom kanan --}}
                <div class="space-y-5">

                    {{-- Kategori --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Kategori</h2>
                        <div class="relative" @click.outside="categoryOpen = false">
                            <input type="text" x-model="categorySearch"
                                @input="categoryOpen = true; categoryId = null" @focus="categoryOpen = true"
                                placeholder="Cari atau tambah kategori..."
                                class="w-full px-4 py-2.5 text-sm rounded-xl border focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('category_id') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                            <input type="hidden" name="category_id" :value="categoryId ?? ''">
                            <div x-show="categoryOpen" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                                <template x-for="group in filteredCategoryGroups" :key="group.name">
                                    <div class="border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                        <div class="px-3 py-1.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider"
                                            x-text="group.name"></div>
                                        <template x-for="cat in group.items" :key="cat.id">
                                            <button type="button" @click="selectCategory(cat)"
                                                class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors"
                                                :class="categoryId === cat.id ?
                                                    'bg-blue-50 dark:bg-blue-900/20 text-blue-600 font-medium' : ''"
                                                x-text="cat.detail"></button>
                                        </template>
                                    </div>
                                </template>
                                <div x-show="filteredCategories.length === 0"
                                    class="px-3 py-2 text-sm text-slate-400">Tidak ada kategori</div>
                            </div>
                        </div>
                        @error('category_id')
                            <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Status</h2>
                        <select name="status"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    {{-- Actions --}}
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
        </form>
    </main>
@endsection

@section('script')
    <script>
        (function() {
            const editor = document.getElementById('description-editor');
            const input = document.getElementById('description-input');
            if (!editor || !input) return;

            document.querySelectorAll('[data-editor-action]').forEach((button) => {
                button.addEventListener('click', () => {
                    document.execCommand(button.dataset.editorAction, false, null);
                    editor.focus();
                    input.value = editor.innerHTML.trim();
                });
            });

            editor.addEventListener('input', () => {
                input.value = editor.innerHTML.trim();
            });

            const form = editor.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    input.value = editor.innerHTML.trim();
                });
            }
        })();

        function productForm({
            categories,
            allVariants,
            oldProductName,
            oldCategoryId,
            oldCategoryName,
            oldRows,
            variantQuickAddUrl
        }) {
            return {
                categories,
                allVariants,
                productName: oldProductName || '',
                categorySearch: oldCategoryName || '',
                categoryId: oldCategoryId || null,
                categoryOpen: false,

                get filteredCategories() {
                    const keyword = this.categorySearch.trim().toLowerCase();
                    if (!keyword) return this.categories;

                    const selected = this.categories.find((c) => Number(c.id) === Number(this.categoryId));
                    if (selected && String(selected.name || '').toLowerCase() === keyword) {
                        return this.categories;
                    }

                    return this.categories.filter((c) => {
                        const full = String(c.name || '').toLowerCase();
                        const group = String(c.group || '').toLowerCase();
                        const detail = String(c.detail || '').toLowerCase();
                        return full.includes(keyword) || group.includes(keyword) || detail.includes(keyword);
                    });
                },
                get filteredCategoryGroups() {
                    const groups = {};
                    this.filteredCategories.forEach((cat) => {
                        const key = cat.group || 'Lainnya';
                        if (!groups[key]) groups[key] = [];
                        groups[key].push(cat);
                    });
                    return Object.keys(groups).map((name) => ({
                        name,
                        items: groups[name]
                    }));
                },
                selectCategory(cat) {
                    this.categoryId = cat.id;
                    this.categorySearch = cat.name;
                    this.categoryOpen = false;
                },

                rows: oldRows.map((r, i) => ({
                    id: i,
                    nameSearch: r.variantName || '',
                    nameOpen: false,
                    selectedName: r.variantName || null,
                    valueSearch: r.variantValue || '',
                    valueOpen: false,
                    variantId: r.variantId,
                    imagePreview: r.imagePath || null,
                    imageStoredPath: r.imageStoredPath || '',
                    sku: r.sku || '',
                    price: r.price || '',
                    stock: r.stock || '',
                })),
                nextId: oldRows.length,

                variantNames() {
                    return [...new Set(this.allVariants.map(v => v.name))].sort();
                },
                filteredNames(search) {
                    const names = this.variantNames();
                    if (!search || !search.trim()) return names;
                    return names.filter(n => n.toLowerCase().includes(search.toLowerCase()));
                },
                nameShowAddNew(row) {
                    const s = (row.nameSearch || '').trim().toLowerCase();
                    return s && !this.filteredNames(row.nameSearch).some(n => n.toLowerCase() === s);
                },
                selectName(row, name) {
                    if (row.selectedName !== name) {
                        row.valueSearch = '';
                        row.variantId = null;
                    }
                    row.selectedName = name;
                    row.nameSearch = name;
                    row.nameOpen = false;
                },
                addLocalName(row) {
                    const name = (row.nameSearch || '').trim();
                    if (!name) return;
                    row.selectedName = name;
                    row.nameOpen = false;
                    row.valueSearch = '';
                    row.variantId = null;
                },
                slugify(value) {
                    return String(value || '')
                        .normalize('NFKD')
                        .replace(/[^\w\s-]/g, '')
                        .trim()
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .toUpperCase();
                },
                generatedSku(row) {
                    const parts = [
                        this.slugify(this.productName),
                        this.slugify(row.selectedName || row.nameSearch),
                        this.slugify(row.valueSearch),
                    ].filter(Boolean);
                    return parts.join('-');
                },

                filteredValues(row) {
                    if (!row.selectedName) return [];
                    const vals = this.allVariants.filter(v => v.name === row.selectedName);
                    if (!row.valueSearch || !row.valueSearch.trim()) return vals;
                    return vals.filter(v => v.value.toLowerCase().includes(row.valueSearch.toLowerCase()));
                },
                valueShowAddNew(row) {
                    if (!row.selectedName) return false;
                    const s = (row.valueSearch || '').trim().toLowerCase();
                    return s && !this.filteredValues(row).some(v => v.value.toLowerCase() === s);
                },
                selectValue(row, variant) {
                    row.variantId = variant.id;
                    row.valueSearch = variant.value;
                    row.valueOpen = false;
                },
                async addNewValue(row) {
                    const name = row.selectedName;
                    const value = (row.valueSearch || '').trim();
                    if (!name || !value) return;
                    try {
                        const res = await fetch(variantQuickAddUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({
                                name,
                                value
                            })
                        });
                        const data = await res.json();
                        this.allVariants = [...this.allVariants, data];
                        this.selectValue(row, data);
                    } catch {}
                },

                addRow() {
                    this.rows.push({
                        id: this.nextId++,
                        nameSearch: '',
                        nameOpen: false,
                        selectedName: null,
                        valueSearch: '',
                        valueOpen: false,
                        variantId: null,
                        imagePreview: null,
                        imageStoredPath: '',
                        sku: '',
                        price: '',
                        stock: ''
                    });
                },
                removeRow(id) {
                    if (this.rows.length <= 1) return;
                    this.rows = this.rows.filter(r => r.id !== id);
                },
                handleImageChange(row, event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        row.imagePreview = e.target.result;
                        row.imageStoredPath = '';
                    };
                    reader.readAsDataURL(file);
                },
            };
        }
    </script>
@endsection
