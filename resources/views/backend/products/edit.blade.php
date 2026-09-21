@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    @php
        $decimalForInput = static function ($value, int $scale = 3): string {
            if ($value === null || $value === '') {
                return '';
            }

            return rtrim(rtrim(number_format((float) $value, $scale, '.', ''), '0'), '.');
        };
        $oldCategoryType = old('category_detail_id', $product->category_detail_id) ? 'detail' : (old('main_category_id', $product->main_category_id) ? 'main' : 'category');
        $oldCatId = old('category_detail_id', old('main_category_id', old('category_id', $product->category_detail_id ?: ($product->main_category_id ?: $product->category_id))));
        $oldCat = collect($categories)->first(fn ($category) => (int) $category['id'] === (int) $oldCatId && $category['type'] === $oldCategoryType);
        $oldCatName = $oldCat['name'] ?? '';

        if (old('variants')) {
            $oldVariants = collect(old('variants'))
                ->map(function ($v) {
                    $attributes = collect($v['attributes'] ?? [])
                        ->mapWithKeys(function ($attribute, $key) {
                            $definitionId = (int) ($attribute['attribute_definition_id'] ?? $key);

                            return [$definitionId => [
                                'attributeDefinitionId' => $definitionId,
                                'valueText' => $attribute['value_text'] ?? '',
                                'valueNumber' => $attribute['value_number'] ?? '',
                            ]];
                        })
                        ->all();

                    return [
                        'productVariantId' => (int) ($v['product_variant_id'] ?? 0) ?: null,
                        'sku' => $v['sku'] ?? '',
                        'price' => $v['price'] ?? '',
                        'stock' => $v['stock'] ?? '',
                        'weightGrams' => $v['weight_grams'] ?? '',
                        'lengthCm' => $v['length_cm'] ?? '',
                        'widthCm' => $v['width_cm'] ?? '',
                        'heightCm' => $v['height_cm'] ?? '',
                        'attributes' => $attributes,
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
                ->map(function ($pv) use ($decimalForInput) {
                    $attributes = $pv->attributeValues
                        ->mapWithKeys(function ($attribute) use ($decimalForInput) {
                            return [$attribute->attribute_definition_id => [
                                'attributeDefinitionId' => $attribute->attribute_definition_id,
                                'valueText' => $attribute->value_text ?? '',
                                'valueNumber' => $decimalForInput($attribute->value_number),
                            ]];
                        })
                        ->all();

                    return [
                        'productVariantId' => $pv->id,
                        'sku' => $pv->sku ?? '',
                        'price' => (string) max(0, (int) round((float) $pv->price)),
                        'stock' => $pv->stock,
                        'weightGrams' => $pv->weight_grams,
                        'lengthCm' => $decimalForInput($pv->length_cm, 2),
                        'widthCm' => $decimalForInput($pv->width_cm, 2),
                        'heightCm' => $decimalForInput($pv->height_cm, 2),
                        'attributes' => $attributes,
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
                    'sku' => '',
                    'price' => '',
                    'stock' => '',
                    'weightGrams' => '',
                    'lengthCm' => '',
                    'widthCm' => '',
                    'heightCm' => '',
                    'attributes' => $attributeDefinitions->mapWithKeys(fn ($definition) => [
                        $definition->id => [
                            'attributeDefinitionId' => $definition->id,
                            'valueText' => '',
                            'valueNumber' => '',
                        ],
                    ])->all(),
                    'imagePath' => null,
                    'imageStoredPath' => '',
                ],
            ];
        }
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Product</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Perbarui data produk yang dipilih.</p>
            </div>

            @if ($product->status === 'active')
                <a href="{{ route('frontend.detail-produk', ['slug' => $product->slug]) }}" target="_blank"
                    rel="noopener noreferrer" data-testid="view-product-link"
                    class="group inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition-colors hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-blue-800 dark:bg-blue-950/50 dark:text-blue-300 dark:hover:border-blue-700 dark:hover:bg-blue-900/50 sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    Lihat Produk
                    <svg class="h-3.5 w-3.5 opacity-60 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h6v6" />
                        <path d="M10 14 21 3" />
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                    </svg>
                </a>
            @else
                <span data-testid="view-product-disabled" title="Aktifkan produk agar dapat dilihat di website"
                    class="inline-flex min-h-11 w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500 sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    Produk Belum Aktif
                </span>
            @endif
        </div>

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
            @submit.prevent="confirmOpen = true"
            x-data="productForm({
                categories: @js($categories),
                specificationTemplates: @js($specificationTemplates),
                attributeDefinitions: @js($attributeDefinitions->map(fn($definition) => ['id' => $definition->id, 'code' => $definition->code, 'name' => $definition->name, 'dataType' => $definition->data_type, 'unit' => $definition->unit])->values()),
                oldProductName: @js(old('name', $product->name)),
                oldCategoryId: @js($oldCatId),
                oldCategoryType: @js($oldCategoryType),
                oldCategoryName: @js($oldCatName),
                oldIsRedeemProduct: @js((bool) old('is_redeem_product', $product->is_redeem_product)),
                oldRedeemPoints: @js(old('redeem_points', $product->redeem_points)),
                oldRows: @js($oldVariants),
                variantQuickAddUrl: @js(route('variants.quick-add')),
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
                            <button type="button" @click="addRow()"
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

                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="rowLabel(row)"></p>
                                            <p class="text-xs text-slate-400 mt-0.5">Sistem membentuk SKU internal dari atribut teknis varian ini.</p>
                                        </div>
                                        <input type="hidden" :name="`variants[${index}][product_variant_id]`"
                                            :value="row.productVariantId ?? ''">
                                        <input type="hidden" :name="`variants[${index}][existing_image]`"
                                            :value="row.imageStoredPath ?? ''">
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
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
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
                                            <input type="text" inputmode="numeric" placeholder="0"
                                                x-model="row.priceDisplay"
                                                @focus="row.priceDisplay = sanitizeNumericInput(row.priceDisplay || row.price)"
                                                @input="syncNumericField(row, 'price', 'priceDisplay')"
                                                @blur="formatNumericField(row, 'price', 'priceDisplay')"
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                            <input type="hidden" :name="`variants[${index}][price]`"
                                                :value="row.price" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Stok
                                                <span class="text-red-400">*</span></label>
                                            <input type="text" inputmode="numeric" placeholder="0"
                                                x-model="row.stockDisplay"
                                                @focus="row.stockDisplay = sanitizeNumericInput(row.stockDisplay || row.stock)"
                                                @input="syncNumericField(row, 'stock', 'stockDisplay')"
                                                @blur="formatNumericField(row, 'stock', 'stockDisplay')"
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                            <input type="hidden" :name="`variants[${index}][stock]`"
                                                :value="row.stock" />
                                        </div>
                                    </div>

                                    {{-- Logistik --}}
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white/70 dark:bg-slate-800/40 p-3">
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <div>
                                                <h3 class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Logistik</h3>
                                                <p class="text-[11px] text-slate-400 mt-0.5">Berat dipakai untuk perhitungan ongkir. Dimensi disimpan untuk referensi packing.</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Berat (gram) <span class="text-red-400">*</span></label>
                                                <input type="number" min="1" step="1" placeholder="100"
                                                    :name="`variants[${index}][weight_grams]`" x-model="row.weightGrams"
                                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Panjang (cm)</label>
                                                <input type="number" min="0" step="0.01" placeholder="0"
                                                    :name="`variants[${index}][length_cm]`" x-model="row.lengthCm"
                                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Lebar (cm)</label>
                                                <input type="number" min="0" step="0.01" placeholder="0"
                                                    :name="`variants[${index}][width_cm]`" x-model="row.widthCm"
                                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tinggi (cm)</label>
                                                <input type="number" min="0" step="0.01" placeholder="0"
                                                    :name="`variants[${index}][height_cm]`" x-model="row.heightCm"
                                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Spesifikasi Teknis --}}
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white/70 dark:bg-slate-800/40 p-3">
                                        <div class="mb-3">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <h3 class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Spesifikasi Teknis</h3>
                                                <span x-show="activeTemplate" class="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700" x-text="activeTemplate?.name"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Isi atribut yang relevan untuk varian ini agar bisa dipakai untuk filter dan informasi produk.</p>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
                                            @foreach ($attributeDefinitions as $definition)
                                                @php
                                                    $defId    = $definition->id;
                                                    $isNumber = $definition->data_type === 'number';
                                                    $fieldKey = $isNumber ? 'valueNumber' : 'valueText';
                                                    $fieldName = $isNumber ? 'value_number' : 'value_text';
                                                    $opts     = json_encode($attributeOptions->get($defId, []));
                                                    $placeholder = $definition->unit ?: 'Isi ' . strtolower($definition->name) . '...';
                                                @endphp
                                                <div x-show="isDefinitionActive({{ $defId }})" x-data="{
                                                        open: false,
                                                        query: '',
                                                        get opts() { return activeOptions({{ $defId }}, {{ $opts }}); },
                                                        get curVal() { return row.attributes['{{ $defId }}']['{{ $fieldKey }}']; },
                                                        set curVal(v) { row.attributes['{{ $defId }}']['{{ $fieldKey }}'] = v; },
                                                        get filtered() {
                                                            const q = this.query.toLowerCase().trim();
                                                            if (!q) return this.opts;
                                                            return this.opts.filter(o => String(o).toLowerCase().includes(q));
                                                        },
                                                        pick(opt) { this.curVal = String(opt); this.query = ''; this.open = false; },
                                                        onOpen() { this.query = ''; this.open = true; }
                                                    }"
                                                    class="relative" @click.outside="open = false; query = ''">
                                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                                        {{ $definition->name }}@if($definition->unit) ({{ $definition->unit }}) @endif
                                                    </label>
                                                    <input type="hidden"
                                                        :name="`variants[${index}][attributes][{{ $defId }}][attribute_definition_id]`"
                                                        value="{{ $defId }}" />
                                                    <div class="relative">
                                                        <input type="text"
                                                            :name="`variants[${index}][attributes][{{ $defId }}][{{ $fieldName }}]`"
                                                            :value="curVal"
                                                            @input="curVal = $event.target.value; query = $event.target.value"
                                                            @focus="onOpen()"
                                                            @keydown.escape="open = false; query = ''"
                                                            @keydown.enter.prevent="if (filtered.length) { pick(filtered[0]); }"
                                                            @keydown.arrow-down.prevent="onOpen()"
                                                            placeholder="{{ $placeholder }}"
                                                            autocomplete="off"
                                                            class="w-full px-3 py-2 pr-7 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                                        <button type="button" tabindex="-1"
                                                            @mousedown.prevent="open ? (open = false) : onOpen()"
                                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline :points="open ? '18 15 12 9 6 15' : '6 9 12 15 18 9'"></polyline>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div x-show="open && opts.length > 0"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        class="absolute z-50 mt-1 w-full max-h-44 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg">
                                                        <p x-show="filtered.length === 0" class="px-3 py-2 text-xs text-slate-400">Tidak ada pilihan cocok</p>
                                                        <template x-for="opt in filtered" :key="opt">
                                                            <button type="button"
                                                                @mousedown.prevent="pick(opt)"
                                                                :class="curVal === String(opt) ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60'"
                                                                class="w-full text-left px-3 py-2 text-sm"
                                                                x-text="opt">
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            @endforeach
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
                                @input="categoryOpen = true; categoryId = null; categoryType = null" @focus="categoryOpen = true"
                                placeholder="Cari atau tambah kategori..."
                                class="w-full px-4 py-2.5 text-sm rounded-xl border focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('category_id') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                            <input type="hidden" name="category_id" :value="categoryType === 'category' ? categoryId : ''">
                            <input type="hidden" name="main_category_id" :value="categoryType === 'main' ? categoryId : ''">
                            <input type="hidden" name="category_detail_id" :value="categoryType === 'detail' ? categoryId : ''">
                            <div x-show="categoryOpen" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                                <template x-for="group in filteredCategoryGroups" :key="group.name">
                                    <div class="border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                        <div class="px-3 py-1.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider"
                                            x-text="group.name"></div>
                                        <template x-for="cat in group.items" :key="`${cat.type}-${cat.id}`">
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
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Redeem Point</h2>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="is_redeem_product" value="1" x-model="isRedeemProduct"
                                class="mt-1 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Aktifkan sebagai produk redeem</p>
                                <p class="text-xs text-slate-400 mt-1">Produk ini akan muncul di halaman redeem point ketika fitur frontend-nya diaktifkan.</p>
                            </div>
                        </label>

                        <div x-show="isRedeemProduct" x-transition class="mt-4">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Harga Point</label>
                            <input type="number" min="1" name="redeem_points" x-model="redeemPoints"
                                placeholder="Contoh: 10"
                                class="w-full px-4 py-2.5 text-sm rounded-xl border focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('redeem_points') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                            <p class="text-xs text-slate-400 mt-1.5">Isi jumlah point yang dibutuhkan untuk redeem 1 produk.</p>
                            @error('redeem_points')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

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
                            class="w-full cursor-pointer px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                            Update Product
                        </button>
                        <a href="{{ route('products.index') }}"
                            class="w-full text-center px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Cancel
                        </a>
                    </div>

                </div>
            </div>

            @include('backend.products.partials.submit-confirmation', [
                'title' => 'Perbarui produk ini?',
                'message' => 'Perubahan pada produk berikut akan langsung disimpan:',
                'confirmLabel' => 'Ya, Perbarui Produk',
            ])
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
            specificationTemplates,
            attributeDefinitions,
            oldProductName,
            oldCategoryId,
            oldCategoryType,
            oldCategoryName,
            oldIsRedeemProduct,
            oldRedeemPoints,
            oldRows,
            variantQuickAddUrl
        }) {
            const normalizeAttributes = (attributes = {}) => {
                const map = {};
                attributeDefinitions.forEach((definition) => {
                    const current = attributes?.[definition.id] || attributes?.[String(definition.id)] || {};
                    map[String(definition.id)] = {
                        attributeDefinitionId: definition.id,
                        valueText: current.valueText ?? current.value_text ?? '',
                        valueNumber: current.valueNumber ?? current.value_number ?? '',
                    };
                });
                return map;
            };

            return {
                categories,
                specificationTemplates,
                attributeDefinitions,
                productName: oldProductName || '',
                categorySearch: oldCategoryName || '',
                categoryId: oldCategoryId || null,
                categoryType: oldCategoryType || null,
                categoryOpen: false,
                confirmOpen: false,
                isSubmitting: false,
                isRedeemProduct: !!oldIsRedeemProduct,
                redeemPoints: oldRedeemPoints || '',

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
                get selectedCategory() {
                    return this.categories.find((category) => String(category.id) === String(this.categoryId) && category.type === this.categoryType) || null;
                },
                get activeTemplate() {
                    const templateId = this.selectedCategory?.templateId;
                    return templateId ? (this.specificationTemplates[String(templateId)] || null) : null;
                },
                get activeAttributeDefinitions() {
                    return this.activeTemplate?.fields || this.attributeDefinitions;
                },
                isDefinitionActive(definitionId) {
                    return this.activeAttributeDefinitions.some((definition) => Number(definition.id) === Number(definitionId));
                },
                activeOptions(definitionId, legacyOptions = []) {
                    const field = this.activeTemplate?.fields?.find((definition) => Number(definition.id) === Number(definitionId));
                    return field ? (field.options || []) : legacyOptions;
                },
                selectCategory(cat) {
                    const previousTemplateId = this.selectedCategory?.templateId || null;
                    const hasSpecificationValues = this.rows.some((row) => Object.values(row.attributes || {}).some((attribute) =>
                        String(attribute.valueText || '').trim() !== '' || String(attribute.valueNumber || '').trim() !== ''
                    ));
                    if (this.selectedCategory && String(previousTemplateId || '') !== String(cat.templateId || '') && hasSpecificationValues) {
                        if (!window.confirm('Kategori memakai template spesifikasi berbeda. Nilai yang tidak kompatibel akan dikosongkan. Lanjutkan?')) return;
                    }
                    this.categoryId = cat.id;
                    this.categoryType = cat.type;
                    this.categorySearch = cat.name;
                    this.categoryOpen = false;
                    const allowedIds = new Set(this.activeAttributeDefinitions.map((definition) => String(definition.id)));
                    this.rows.forEach((row) => Object.entries(row.attributes || {}).forEach(([id, attribute]) => {
                        if (!allowedIds.has(String(id))) {
                            attribute.valueText = '';
                            attribute.valueNumber = '';
                        }
                    }));
                },
                submitConfirmed() {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;
                    this.confirmOpen = false;
                    this.$nextTick(() => this.$root.submit());
                },

                rows: oldRows.map((r, i) => ({
                    id: i,
                    productVariantId: r.productVariantId || null,
                    imagePreview: r.imagePath || null,
                    imageStoredPath: r.imageStoredPath || '',
                    sku: r.sku || '',
                    price: r.price || '',
                    priceDisplay: '',
                    stock: r.stock || '',
                    stockDisplay: '',
                    weightGrams: r.weightGrams || '',
                    lengthCm: r.lengthCm || '',
                    widthCm: r.widthCm || '',
                    heightCm: r.heightCm || '',
                    attributes: normalizeAttributes(r.attributes || {}),
                })),
                nextId: oldRows.length,
                slugify(value) {
                    return String(value || '')
                        .normalize('NFKD')
                        .replace(/[^\w\s-]/g, '')
                        .trim()
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .toUpperCase();
                },
                attributeState(row, code) {
                    const definition = this.attributeDefinitions.find((item) => item.code === code);
                    if (!definition) return null;
                    return row.attributes?.[String(definition.id)] || null;
                },
                rowLabel(row) {
                    const values = this.activeAttributeDefinitions
                        .filter((definition) => definition.affectsVariant !== false)
                        .map((definition) => {
                            const state = row.attributes?.[String(definition.id)] || {};
                            const value = definition.dataType === 'number' ? state.valueNumber : state.valueText;
                            return value && definition.unit ? `${value}${definition.unit}` : value;
                        }).filter(Boolean);
                    return values.join(' - ') || 'Varian Baru';
                },
                generatedSku(row) {
                    const parts = [
                        this.slugify(this.productName),
                        this.slugify(this.rowLabel(row)),
                    ].filter(Boolean);
                    return parts.join('-');
                },
                sanitizeNumericInput(value) {
                    const raw = String(value ?? '').trim();
                    if (!raw) return '';

                    if (/^\d+[.,]\d{1,2}$/.test(raw)) {
                        return String(Math.round(Number(raw.replace(',', '.'))));
                    }

                    return raw.replace(/\D+/g, '');
                },
                formatNumericInput(value) {
                    const digits = this.sanitizeNumericInput(value);
                    return digits ? new Intl.NumberFormat('id-ID').format(Number(digits)) : '';
                },
                syncNumericField(row, field, displayField) {
                    row[field] = this.sanitizeNumericInput(row[displayField]);
                    row[displayField] = row[field];
                },
                formatNumericField(row, field, displayField) {
                    row[field] = this.sanitizeNumericInput(row[displayField]);
                    row[displayField] = this.formatNumericInput(row[field]);
                },

                addRow() {
                    this.rows.push({
                        id: this.nextId++,
                        productVariantId: null,
                        imagePreview: null,
                        imageStoredPath: '',
                        sku: '',
                        price: '',
                        priceDisplay: '',
                        stock: '',
                        stockDisplay: '',
                        weightGrams: '',
                        lengthCm: '',
                        widthCm: '',
                        heightCm: '',
                        attributes: normalizeAttributes()
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
                init() {
                    this.rows = this.rows.map((row) => ({
                        ...row,
                        priceDisplay: this.formatNumericInput(row.price),
                        stockDisplay: this.formatNumericInput(row.stock),
                    }));
                },
            };
        }
    </script>
@endsection
