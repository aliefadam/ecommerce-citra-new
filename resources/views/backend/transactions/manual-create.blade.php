@extends('layouts.app')

@section('title', 'Buat Transaksi Manual')

@section('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* ── Select2 base ────────────────────────────────────────────── */
        .select2-container--default .select2-selection--single {
            height: 42px;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: white;
            position: relative;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b;
            font-size: 0.875rem;
            line-height: 42px;
            padding-left: 1rem;
            padding-right: 3rem;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            width: 28px;
            position: absolute;
            top: 0;
            right: 0;
        }
        /* × clear button: posisi kanan sebelum arrow */
        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute;
            top: 50%;
            right: 28px;
            transform: translateY(-50%);
            float: none;
            margin: 0;
            font-size: 16px;
            line-height: 1;
            color: #94a3b8;
            padding: 0 4px;
            font-weight: bold;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #ef4444;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #3b82f6;
        }
        .select2-dropdown {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .select2-search--dropdown .select2-search__field {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            font-size: 0.875rem;
        }
        .select2-results__option {
            font-size: 0.875rem;
            padding: 8px 12px;
        }
        /* ── Dark mode ───────────────────────────────────────────────── */
        .dark .select2-container--default .select2-selection--single {
            background: #334155;
            border-color: #475569;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #e2e8f0;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__clear {
            color: #64748b;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #f87171;
        }
        .dark .select2-dropdown {
            background: #1e293b;
            border-color: #475569;
        }
        .dark .select2-search--dropdown .select2-search__field {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }
        .dark .select2-results__option {
            color: #e2e8f0;
            background-color: #1e293b;
        }
        .dark .select2-results__option:hover {
            background-color: #334155;
        }
        .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #3b82f6;
            color: white;
        }
        /* ── Produk column: lebar fixed agar tidak mendorong kolom lain */
        .product-col { width: 300px; min-width: 300px; max-width: 300px; }
        .product-col .select2-container { width: 100% !important; }
    </style>
@endsection

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Kembali ke transaksi</a>
                <h1 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Buat Transaksi Manual</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Order langsung dari admin untuk pesanan chat, telepon, atau customer langganan.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-violet-100 px-3 py-1.5 text-xs font-semibold text-violet-700 dark:bg-violet-900/35 dark:text-violet-300">
                Manual Admin
            </span>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="manualOrderForm" method="POST" action="{{ route('transactions.store-manual') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf

            <section class="space-y-6">
                {{-- Customer --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-white">Customer</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pilih customer existing atau input customer manual.</p>
                        </div>
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-700/70">
                            <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 has-[:checked]:bg-white has-[:checked]:text-blue-700 has-[:checked]:shadow-sm dark:text-slate-300 dark:has-[:checked]:bg-slate-800 dark:has-[:checked]:text-blue-300">
                                <input class="sr-only" type="radio" name="customer_mode" value="existing" checked onchange="setCustomerMode('existing')">
                                Existing
                            </label>
                            <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 has-[:checked]:bg-white has-[:checked]:text-blue-700 has-[:checked]:shadow-sm dark:text-slate-300 dark:has-[:checked]:bg-slate-800 dark:has-[:checked]:text-blue-300">
                                <input class="sr-only" type="radio" name="customer_mode" value="manual" onchange="setCustomerMode('manual')">
                                Manual
                            </label>
                        </div>
                    </div>

                    <div id="existingCustomerPanel" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
                        <div>
                            <label for="customer_id" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Customer</label>
                            <select id="customer_id" name="customer_id" class="w-full"></select>
                        </div>
                        <div id="customerSnapshot" class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-700/40 dark:text-slate-400">
                            Belum ada customer dipilih.
                        </div>
                    </div>

                    <div id="manualCustomerPanel" class="hidden grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nama</label>
                            <input name="manual_customer_name" type="text" value="{{ old('manual_customer_name') }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Nomor HP</label>
                            <input name="manual_customer_phone" type="text" value="{{ old('manual_customer_phone') }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Email</label>
                            <input name="manual_customer_email" type="email" value="{{ old('manual_customer_email') }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                    </div>
                </div>

                {{-- Produk --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-white">Produk</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan produk, qty, harga, diskon item, dan catatan.</p>
                        </div>
                        <button type="button" onclick="addManualItem()"
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-sm shadow-blue-500/20 transition-colors hover:bg-blue-700">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Tambah Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[58rem] text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-400 dark:bg-slate-700/50 dark:text-slate-500">
                                <tr>
                                    <th class="product-col px-3 py-3">Produk</th>
                                    <th class="w-20 px-3 py-3">Qty</th>
                                    <th class="w-32 px-3 py-3">Harga</th>
                                    <th class="w-32 px-3 py-3">Diskon</th>
                                    <th class="px-3 py-3">Catatan</th>
                                    <th class="w-32 px-3 py-3 text-right">Subtotal</th>
                                    <th class="w-10 px-3 py-3"></th>
                                </tr>
                            </thead>
                            <tbody id="manualItemsBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Faktur Pajak --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-white">Faktur Pajak</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Opsional — isi jika customer membutuhkan faktur pajak (e-Faktur).</p>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-3">
                            <input type="checkbox" id="requestTaxInvoice" name="request_tax_invoice" value="1"
                                class="peer sr-only" onchange="toggleTaxInvoicePanel(this.checked)"
                                {{ old('request_tax_invoice') ? 'checked' : '' }}>
                            <div class="relative h-6 w-11 rounded-full bg-slate-200 transition-colors
                                        peer-checked:bg-blue-600
                                        dark:bg-slate-600 dark:peer-checked:bg-blue-500
                                        after:absolute after:left-[2px] after:top-[2px]
                                        after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow
                                        after:transition-all after:content-['']
                                        peer-checked:after:translate-x-full"></div>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Minta Faktur Pajak</span>
                        </label>
                    </div>

                    <div id="taxInvoicePanel" class="{{ old('request_tax_invoice') ? '' : 'hidden' }} mt-5 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nama Wajib Pajak <span class="text-red-500">*</span>
                            </label>
                            <input name="tax_taxpayer_name" type="text" value="{{ old('tax_taxpayer_name') }}"
                                placeholder="PT / CV / Nama Perorangan"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                NPWP <span class="text-red-500">*</span>
                            </label>
                            <input name="tax_taxpayer_number" type="text" value="{{ old('tax_taxpayer_number') }}"
                                placeholder="000000000000000"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Alamat Wajib Pajak <span class="text-red-500">*</span>
                            </label>
                            <input name="tax_taxpayer_address" type="text" value="{{ old('tax_taxpayer_address') }}"
                                placeholder="Alamat sesuai NPWP"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Email Penerima Faktur
                            </label>
                            <input name="tax_taxpayer_email" type="email" value="{{ old('tax_taxpayer_email') }}"
                                placeholder="email@perusahaan.com"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            <p class="mt-1 text-xs text-slate-400">Faktur pajak akan dikirim ke email ini. Kosongkan untuk gunakan email customer.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Catatan</label>
                            <input name="tax_customer_note" type="text" value="{{ old('tax_customer_note') }}"
                                placeholder="Keterangan tambahan (opsional)"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                    </div>
                </div>

            </section>

            {{-- Sidebar total --}}
            <aside class="xl:sticky xl:top-24 xl:h-fit">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan</h2>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                        {{-- Subtotal (read-only) --}}
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <span class="text-slate-500 dark:text-slate-400 shrink-0">Subtotal</span>
                            <span id="summarySubtotal" class="font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>

                        {{-- Diskon --}}
                        <div class="flex items-center gap-3 px-5 py-3">
                            <label for="discount_amount" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Diskon</label>
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                                <input id="discount_amount" name="discount_amount" type="number" min="0" step="1"
                                    value="{{ old('discount_amount', 0) }}" oninput="recalculateManualOrder()"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <span id="summaryDiscount" class="shrink-0 w-24 text-right font-semibold text-emerald-600">- Rp 0</span>
                        </div>

                        {{-- Ongkir --}}
                        <div class="flex items-center gap-3 px-5 py-3">
                            <label for="shipping_cost" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">Ongkir</label>
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                                <input id="shipping_cost" name="shipping_cost" type="number" min="0" step="1"
                                    value="{{ old('shipping_cost', 0) }}" oninput="recalculateManualOrder()"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <span id="summaryShipping" class="shrink-0 w-24 text-right font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>

                        {{-- PPN --}}
                        <div class="flex items-center gap-3 px-5 py-3">
                            <label for="ppn_rate" class="shrink-0 text-slate-500 dark:text-slate-400 w-20">PPN</label>
                            <div class="relative flex-1">
                                <input id="ppn_rate" name="ppn_rate" type="number" min="0" max="100" step="0.01"
                                    value="{{ old('ppn_rate', 0) }}" oninput="recalculateManualOrder()"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-3 pr-8 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">%</span>
                            </div>
                            <span id="summaryPpn" class="shrink-0 w-24 text-right font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>

                        {{-- Grand Total --}}
                        <div class="flex items-center justify-between gap-3 px-5 py-4 bg-blue-50 dark:bg-blue-900/20">
                            <span class="font-bold text-blue-700 dark:text-blue-400">Grand Total</span>
                            <span id="summaryGrandTotal" class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp 0</span>
                        </div>
                    </div>

                    <div class="px-5 py-4">
                        <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition-colors hover:bg-blue-700">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Simpan Transaksi
                        </button>
                        <p class="mt-3 text-xs text-center text-slate-400 dark:text-slate-500">Stok akan dikurangi saat transaksi disimpan.</p>
                    </div>
                </div>
            </aside>
        </form>
    </main>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let manualItemIndex = 0;

        const SEARCH_CUSTOMERS_URL = "{{ route('transactions.create-manual.search-customers') }}";
        const SEARCH_PRODUCTS_URL  = "{{ route('transactions.create-manual.search-products') }}";

        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        function moneyValue(input) {
            return Math.max(0, Number(input?.value || 0));
        }

        // ── Tax invoice toggle ────────────────────────────────────────────
        function toggleTaxInvoicePanel(show) {
            document.getElementById('taxInvoicePanel')?.classList.toggle('hidden', !show);
        }

        // ── Customer mode toggle ──────────────────────────────────────────
        function setCustomerMode(mode) {
            document.getElementById('existingCustomerPanel')?.classList.toggle('hidden', mode !== 'existing');
            document.getElementById('manualCustomerPanel')?.classList.toggle('hidden', mode !== 'manual');
        }

        // ── Select2: customer ─────────────────────────────────────────────
        $(function () {
            $('#customer_id').select2({
                width: '100%',
                placeholder: 'Cari nama atau email customer...',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: SEARCH_CUSTOMERS_URL,
                    dataType: 'json',
                    delay: 300,
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({ results: data.results }),
                    cache: true,
                },
                templateResult: function (item) {
                    if (item.loading) return item.text;
                    return $('<span>').text(item.text);
                },
            });

            $('#customer_id').on('select2:select', function (e) {
                const item = e.params.data;
                const box  = document.getElementById('customerSnapshot');
                if (!box) return;

                const addr = item.address;
                box.innerHTML = `
                    <p class="font-semibold text-slate-800 dark:text-slate-200">${item.name}</p>
                    <p class="mt-1 text-xs">${item.email || '-'}${item.phone ? ' / ' + item.phone : ''}</p>
                    ${addr
                        ? `<p class="mt-2 text-xs leading-relaxed">${addr.recipient_name || item.name}<br>${addr.phone || item.phone || '-'}<br>${addr.address_line || ''}${addr.city ? ', ' + addr.city : ''}${addr.province ? ', ' + addr.province : ''}</p>`
                        : '<p class="mt-2 text-xs text-amber-600 dark:text-amber-400">Customer belum punya alamat tersimpan.</p>'
                    }`;
            });

            $('#customer_id').on('select2:clear', function () {
                const box = document.getElementById('customerSnapshot');
                if (box) box.innerHTML = 'Belum ada customer dipilih.';
            });
        });

        // ── Item rows ─────────────────────────────────────────────────────
        function addManualItem(seed) {
            seed = seed || {};
            const index = manualItemIndex++;
            const body  = document.getElementById('manualItemsBody');
            const row   = document.createElement('tr');
            row.dataset.itemRow = String(index);
            row.innerHTML = `
                <td class="product-col px-3 py-3 align-top">
                    <select id="product_select_${index}" name="items[${index}][product_variant_id]" class="product-select2" style="width:100%"></select>
                    <p data-stock-hint class="mt-1 text-xs text-slate-400"></p>
                </td>
                <td class="px-3 py-3 align-top">
                    <input name="items[${index}][qty]" type="number" min="1" step="1" value="${seed.qty || 1}" oninput="recalculateManualOrder()"
                        class="manual-qty w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </td>
                <td class="px-3 py-3 align-top">
                    <input name="items[${index}][unit_price]" type="number" min="0" step="1" value="${seed.unit_price || 0}" oninput="recalculateManualOrder()"
                        class="manual-price w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </td>
                <td class="px-3 py-3 align-top">
                    <input name="items[${index}][discount_amount]" type="number" min="0" step="1" value="${seed.discount_amount || 0}" oninput="recalculateManualOrder()"
                        class="manual-item-discount w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </td>
                <td class="px-3 py-3 align-top">
                    <input name="items[${index}][note]" type="text" value="${seed.note || ''}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                </td>
                <td class="px-3 py-3 text-right align-top">
                    <span data-item-subtotal class="font-semibold text-slate-800 dark:text-slate-200">Rp 0</span>
                </td>
                <td class="px-3 py-3 text-right align-top">
                    <button type="button" onclick="removeManualItem(${index})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                    </button>
                </td>`;
            body.appendChild(row);

            // init Select2 AJAX for this product row
            $(`#product_select_${index}`).select2({
                width: 'resolve',
                placeholder: 'Cari produk atau SKU...',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: SEARCH_PRODUCTS_URL,
                    dataType: 'json',
                    delay: 300,
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({ results: data.results }),
                    cache: true,
                },
            });

            $(`#product_select_${index}`).on('select2:select', function (e) {
                const product = e.params.data;
                const row     = document.querySelector(`[data-item-row="${index}"]`);
                if (!row || !product) return;

                row.querySelector('.manual-price').value = product.price || 0;
                const hint  = row.querySelector('[data-stock-hint]');
                const isLow = product.status !== 'active' || Number(product.stock || 0) < 1;
                hint.className  = 'mt-1 text-xs ' + (isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400');
                hint.textContent = `${product.sku ? 'SKU ' + product.sku + ' | ' : ''}Stok ${product.stock}${product.status !== 'active' ? ' | Produk nonaktif' : ''}`;
                recalculateManualOrder();
            });

            $(`#product_select_${index}`).on('select2:clear', function () {
                const row = document.querySelector(`[data-item-row="${index}"]`);
                if (!row) return;
                row.querySelector('.manual-price').value = 0;
                row.querySelector('[data-stock-hint]').textContent = '';
                recalculateManualOrder();
            });

            recalculateManualOrder();
            window.lucide?.createIcons?.();
        }

        function removeManualItem(index) {
            const row = document.querySelector(`[data-item-row="${index}"]`);
            if (row && document.querySelectorAll('[data-item-row]').length > 1) {
                // destroy Select2 before removing
                $(`#product_select_${index}`).select2('destroy');
                row.remove();
                recalculateManualOrder();
            }
        }

        // ── Recalculate ───────────────────────────────────────────────────
        function recalculateManualOrder() {
            let subtotal = 0;
            document.querySelectorAll('[data-item-row]').forEach((row) => {
                const qty      = Math.max(1, Number(row.querySelector('.manual-qty')?.value || 1));
                const price    = moneyValue(row.querySelector('.manual-price'));
                const discount = moneyValue(row.querySelector('.manual-item-discount'));
                const itemSub  = Math.max(0, (qty * price) - discount);
                row.querySelector('[data-item-subtotal]').textContent = formatRupiah(itemSub);
                subtotal += itemSub;
            });

            const discountAmount = Math.min(subtotal, moneyValue(document.getElementById('discount_amount')));
            const shippingCost   = moneyValue(document.getElementById('shipping_cost'));
            const ppnRate        = Math.max(0, Math.min(100, parseFloat(document.getElementById('ppn_rate')?.value || 0)));
            const taxableAmount  = Math.max(0, subtotal - discountAmount);
            const ppnAmount      = Math.round(taxableAmount * ppnRate / 100);
            const grandTotal     = taxableAmount + shippingCost + ppnAmount;

            document.getElementById('summarySubtotal').textContent   = formatRupiah(subtotal);
            document.getElementById('summaryDiscount').textContent   = discountAmount > 0 ? '- ' + formatRupiah(discountAmount) : 'Rp 0';
            document.getElementById('summaryShipping').textContent   = formatRupiah(shippingCost);
            document.getElementById('summaryPpn').textContent        = formatRupiah(ppnAmount);
            document.getElementById('summaryGrandTotal').textContent = formatRupiah(grandTotal);
        }

        addManualItem();
    </script>
@endsection
