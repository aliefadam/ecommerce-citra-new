@extends('layouts.app')

@section('title', 'Buat Transaksi Manual')

@section('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: white;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b;
            font-size: 0.875rem;
            padding-left: 1rem;
            padding-right: 2rem;
            line-height: 40px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
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
        .dark .select2-container--default .select2-selection--single {
            background: #334155;
            border-color: #475569;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #e2e8f0;
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
        }
        .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #3b82f6;
            color: white;
        }
        .dark .select2-container--default .select2-results__option {
            background-color: #1e293b;
        }
        .dark .select2-container--default .select2-results__option:hover {
            background-color: #334155;
        }
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
                                    <th class="px-3 py-3">Produk</th>
                                    <th class="w-24 px-3 py-3">Qty</th>
                                    <th class="w-36 px-3 py-3">Harga</th>
                                    <th class="w-36 px-3 py-3">Diskon</th>
                                    <th class="px-3 py-3">Catatan</th>
                                    <th class="w-36 px-3 py-3 text-right">Subtotal</th>
                                    <th class="w-12 px-3 py-3"></th>
                                </tr>
                            </thead>
                            <tbody id="manualItemsBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Diskon, Ongkir, PPN --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="mb-4 font-bold text-slate-800 dark:text-white">Diskon, Ongkir & PPN</h2>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Diskon Total (Rp)</label>
                            <input id="discount_amount" name="discount_amount" type="number" min="0" step="1" value="{{ old('discount_amount', 0) }}" oninput="recalculateManualOrder()"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Ongkir Manual (Rp)</label>
                            <input id="shipping_cost" name="shipping_cost" type="number" min="0" step="1" value="{{ old('shipping_cost', 0) }}" oninput="recalculateManualOrder()"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                PPN (%)
                                <span class="ml-1 text-xs font-normal text-slate-400">0 = tidak kena PPN</span>
                            </label>
                            <input id="ppn_rate" name="ppn_rate" type="number" min="0" max="100" step="0.01" value="{{ old('ppn_rate', 0) }}" oninput="recalculateManualOrder()"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
                                placeholder="mis. 11">
                        </div>
                    </div>
                </div>
            </section>

            {{-- Sidebar total --}}
            <aside class="xl:sticky xl:top-24 xl:h-fit">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">Total Transaksi</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3 text-slate-500 dark:text-slate-400">
                            <span>Subtotal Produk</span>
                            <span id="summarySubtotal" class="font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>
                        <div class="flex justify-between gap-3 text-slate-500 dark:text-slate-400">
                            <span>Diskon Total</span>
                            <span id="summaryDiscount" class="font-semibold text-emerald-600">- Rp 0</span>
                        </div>
                        <div class="flex justify-between gap-3 text-slate-500 dark:text-slate-400">
                            <span>Ongkir</span>
                            <span id="summaryShipping" class="font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>
                        <div id="summaryPpnRow" class="hidden flex justify-between gap-3 text-slate-500 dark:text-slate-400">
                            <span id="summaryPpnLabel">PPN (11%)</span>
                            <span id="summaryPpn" class="font-semibold text-slate-700 dark:text-slate-200">Rp 0</span>
                        </div>
                        <div class="border-t border-slate-100 pt-4 dark:border-slate-700">
                            <div class="flex justify-between gap-3 text-base font-bold text-blue-600">
                                <span>Grand Total</span>
                                <span id="summaryGrandTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="mt-5 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition-colors hover:bg-blue-700">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Simpan Transaksi
                    </button>
                    <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">Backend akan menghitung ulang total dan mengurangi stok saat transaksi disimpan.</p>
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
                <td class="px-3 py-3 align-top" style="min-width:220px">
                    <select id="product_select_${index}" name="items[${index}][product_variant_id]" class="w-full product-select2"></select>
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
                width: '100%',
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

            document.getElementById('summarySubtotal').textContent  = formatRupiah(subtotal);
            document.getElementById('summaryDiscount').textContent  = '- ' + formatRupiah(discountAmount);
            document.getElementById('summaryShipping').textContent  = formatRupiah(shippingCost);
            document.getElementById('summaryGrandTotal').textContent = formatRupiah(grandTotal);

            const ppnRow   = document.getElementById('summaryPpnRow');
            const ppnLabel = document.getElementById('summaryPpnLabel');
            const ppnEl    = document.getElementById('summaryPpn');
            if (ppnRate > 0) {
                ppnRow.classList.remove('hidden');
                ppnRow.classList.add('flex');
                ppnLabel.textContent = `PPN (${ppnRate}%)`;
                ppnEl.textContent    = formatRupiah(ppnAmount);
            } else {
                ppnRow.classList.add('hidden');
                ppnRow.classList.remove('flex');
            }
        }

        addManualItem();
    </script>
@endsection
