@extends('layouts.app')

@section('title', 'Create Flash Sale')

@section('content')
    @php
        $oldItems = collect(old('items', []))
            ->map(
                fn($i) => [
                    'product_variant_id' => $i['product_variant_id'] ?? '',
                    'discount_price' => $i['discount_price'] ?? '',
                    'quota' => $i['quota'] ?? '',
                    'is_active' => !empty($i['is_active']),
                ],
            )
            ->values()
            ->toArray();

        if (empty($oldItems)) {
            $oldItems = [['product_variant_id' => '', 'discount_price' => '', 'quota' => '', 'is_active' => true]];
        }
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Create Flash Sale</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Buat campaign flash sale beserta item promonya.</p>
        </div>

        <form action="{{ route('flash-sales.store') }}" method="POST" x-data="flashSaleForm({ oldItems: {{ json_encode($oldItems) }} })">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Informasi Flash Sale</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama
                                    Campaign</label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    placeholder="Contoh: Flash Sale Akhir Pekan"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('name') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                                @error('name')
                                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mulai</label>
                                <input type="datetime-local" name="start_at" value="{{ old('start_at') }}"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 {{ $errors->has('start_at') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                                @error('start_at')
                                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Selesai</label>
                                <input type="datetime-local" name="end_at" value="{{ old('end_at') }}"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 {{ $errors->has('end_at') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                                @error('end_at')
                                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan</label>
                                <textarea name="notes" rows="3" placeholder="Catatan opsional..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('notes') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300">Item Flash Sale</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Minimal satu item harus diisi.</p>
                            </div>
                        </div>

                        @error('items')
                            <div
                                class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700 rounded-lg text-xs text-red-500">
                                {{ $message }}</div>
                        @enderror

                        <div class="space-y-3">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div
                                    class="border border-slate-200 dark:border-slate-600 rounded-xl p-4 space-y-3 bg-slate-50/50 dark:bg-slate-700/20">
                                    <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-2 items-end">
                                        <div class="relative" @click.outside="row.open = false">
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Varian
                                                Produk <span class="text-red-400">*</span></label>
                                            <div
                                                class="flex items-center gap-1 w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 cursor-text">
                                                <input type="text" x-model="row.search"
                                                    @input="row.open = true; row.product_variant_id = ''"
                                                    @focus="row.open = true" placeholder="Cari varian..."
                                                    class="flex-1 bg-transparent outline-none text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 min-w-0" />
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="text-slate-400 flex-shrink-0">
                                                    <polyline points="6 9 12 15 18 9" />
                                                </svg>
                                            </div>
                                            <input type="hidden" :name="`items[${index}][product_variant_id]`"
                                                :value="row.product_variant_id">
                                            <div x-show="row.open" x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                class="absolute z-30 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg overflow-hidden max-h-52 overflow-y-auto">
                                                <template x-for="option in filteredVariants(row.search)"
                                                    :key="option.id">
                                                    <button type="button" @click="selectVariant(row, option)"
                                                        class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors"
                                                        :class="String(row.product_variant_id) === String(option.id) ?
                                                            'bg-blue-50 dark:bg-blue-900/20 text-blue-600 font-medium' :
                                                            ''"
                                                        x-text="option.label"></button>
                                                </template>
                                                <div x-show="filteredVariants(row.search).length === 0"
                                                    class="px-3 py-2 text-sm text-slate-400">Varian tidak ditemukan</div>
                                            </div>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga
                                                Normal</label>
                                            <input type="text" :value="formatRupiah(selectedVariant(row)?.price || 0)"
                                                readonly
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/70 dark:text-slate-200 text-slate-600 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga
                                                Promo <span class="text-red-400">*</span></label>
                                            <input type="number" min="0" step="100"
                                                :name="`items[${index}][discount_price]`" x-model="row.discount_price"
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Kuota
                                                <span class="text-red-400">*</span></label>
                                            <input type="number" min="1" :name="`items[${index}][quota]`"
                                                x-model="row.quota"
                                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        </div>
                                        <div class="flex items-center gap-2 pb-1">
                                            <input type="hidden" :name="`items[${index}][is_active]`"
                                                :value="row.is_active ? 1 : 0">
                                            <button type="button" @click="row.is_active = !row.is_active"
                                                :class="row.is_active ?
                                                    'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20' :
                                                    'text-slate-400 bg-slate-100 dark:bg-slate-700'"
                                                class="px-2 py-1 rounded-lg text-xs font-semibold">Active</button>
                                            <button type="button" @click="removeRow(row.id)"
                                                :disabled="rows.length <= 1"
                                                :class="rows.length <= 1 ? 'opacity-30 cursor-not-allowed' :
                                                    'hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-500'"
                                                class="p-1.5 rounded-lg text-slate-400 transition-colors">
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
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addRow"
                            class="mt-3 flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium px-1 py-1 transition-colors">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            Tambah Item
                        </button>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Status Campaign</h2>
                        <select name="status"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            <option value="active" @selected(old('status') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button type="submit"
                            class="w-full px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                            Save Flash Sale
                        </button>
                        <a href="{{ route('flash-sales.index') }}"
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
    @php
        $variantOptions = $productVariants
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'price' => (float) $v->price,
                    'stock' => (int) $v->stock,
                    'label' => trim(
                        ($v->product?->name ?? '-') .
                            ' - ' .
                            $v->skuLabel() .
                            ' | Stock ' .
                            (int) $v->stock,
                    ),
                ];
            })
            ->values()
            ->all();
    @endphp
    <script>
        function flashSaleForm({
            oldItems
        }) {
            const variants = @json($variantOptions);

            return {
                variants,
                rows: oldItems.map((r, i) => ({
                    id: i,
                    product_variant_id: String(r.product_variant_id ?? ''),
                    search: '',
                    open: false,
                    discount_price: r.discount_price ?? '',
                    quota: r.quota ?? '',
                    is_active: r.is_active ?? true,
                })),
                nextId: oldItems.length,
                init() {
                    this.rows = this.rows.map((row) => {
                        const selected = this.variants.find(v => String(v.id) === String(row.product_variant_id));
                        return {
                            ...row,
                            search: selected ? selected.label : ''
                        };
                    });
                },
                filteredVariants(search) {
                    if (!search || !search.trim()) return this.variants;
                    const q = search.toLowerCase();
                    return this.variants.filter(v => v.label.toLowerCase().includes(q));
                },
                selectedVariant(row) {
                    return this.variants.find(v => String(v.id) === String(row.product_variant_id));
                },
                selectVariant(row, option) {
                    row.product_variant_id = String(option.id);
                    row.search = option.label;
                    row.open = false;
                    if (!row.discount_price) row.discount_price = option.price;
                },
                formatRupiah(value) {
                    return `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
                },
                addRow() {
                    this.rows.push({
                        id: this.nextId++,
                        product_variant_id: '',
                        search: '',
                        open: false,
                        discount_price: '',
                        quota: '',
                        is_active: true
                    });
                },
                removeRow(id) {
                    if (this.rows.length <= 1) return;
                    this.rows = this.rows.filter(r => r.id !== id);
                },
            };
        }
    </script>
@endsection
