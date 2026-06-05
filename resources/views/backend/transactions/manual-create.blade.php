@extends('layouts.app')

@section('title', 'Buat Transaksi Manual')

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
                            <select id="customer_id" name="customer_id" onchange="syncCustomerSnapshot()"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                <option value="">Pilih customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer['id'] }}">{{ $customer['name'] }} - {{ $customer['email'] }}</option>
                                @endforeach
                            </select>
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

                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="mb-4 font-bold text-slate-800 dark:text-white">Diskon dan Ongkir</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Diskon Total</label>
                            <input id="discount_amount" name="discount_amount" type="number" min="0" step="1" value="{{ old('discount_amount', 0) }}" oninput="recalculateManualOrder()"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Ongkir Manual</label>
                            <input id="shipping_cost" name="shipping_cost" type="number" min="0" step="1" value="{{ old('shipping_cost', 0) }}" oninput="recalculateManualOrder()"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        </div>
                    </div>
                </div>
            </section>

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
    <script>
        const manualCustomers = @json($customers);
        const manualProducts = @json($products);
        let manualItemIndex = 0;

        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        function moneyValue(input) {
            return Math.max(0, Number(input?.value || 0));
        }

        function setCustomerMode(mode) {
            document.getElementById('existingCustomerPanel')?.classList.toggle('hidden', mode !== 'existing');
            document.getElementById('manualCustomerPanel')?.classList.toggle('hidden', mode !== 'manual');
        }

        function syncCustomerSnapshot() {
            const selectedId = Number(document.getElementById('customer_id')?.value || 0);
            const customer = manualCustomers.find((item) => Number(item.id) === selectedId);
            const box = document.getElementById('customerSnapshot');
            if (!box) return;

            if (!customer) {
                box.innerHTML = 'Belum ada customer dipilih.';
                return;
            }

            const address = customer.address;
            box.innerHTML = `
                <p class="font-semibold text-slate-800 dark:text-slate-200">${customer.name}</p>
                <p class="mt-1 text-xs">${customer.email || '-'}${customer.phone ? ' / ' + customer.phone : ''}</p>
                ${address ? `<p class="mt-2 text-xs leading-relaxed">${address.recipient_name || customer.name}<br>${address.phone || customer.phone || '-'}<br>${address.address_line || ''}${address.city ? ', ' + address.city : ''}${address.province ? ', ' + address.province : ''}</p>` : '<p class="mt-2 text-xs text-amber-600 dark:text-amber-400">Customer belum punya alamat tersimpan.</p>'}
            `;
        }

        function productOptionsHtml(selectedId = '') {
            return ['<option value="">Pilih produk</option>'].concat(manualProducts.map((product) => {
                const flags = [
                    product.sku ? `SKU ${product.sku}` : '',
                    `Stok ${product.stock}`,
                    product.status !== 'active' ? 'Nonaktif' : '',
                ].filter(Boolean).join(' | ');
                return `<option value="${product.id}" ${String(selectedId) === String(product.id) ? 'selected' : ''}>${product.product_name} - ${product.variant_name} (${flags})</option>`;
            })).join('');
        }

        function addManualItem(seed = {}) {
            const index = manualItemIndex++;
            const body = document.getElementById('manualItemsBody');
            const row = document.createElement('tr');
            row.dataset.itemRow = String(index);
            row.innerHTML = `
                <td class="px-3 py-3 align-top">
                    <select name="items[${index}][product_variant_id]" onchange="selectManualProduct(${index}, this.value)"
                        class="manual-product w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                        ${productOptionsHtml(seed.product_variant_id || '')}
                    </select>
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
                </td>
            `;
            body.appendChild(row);
            if (seed.product_variant_id) selectManualProduct(index, seed.product_variant_id, false);
            recalculateManualOrder();
            window.lucide?.createIcons?.();
        }

        function removeManualItem(index) {
            const row = document.querySelector(`[data-item-row="${index}"]`);
            if (row && document.querySelectorAll('[data-item-row]').length > 1) {
                row.remove();
                recalculateManualOrder();
            }
        }

        function selectManualProduct(index, productId, shouldRecalculate = true) {
            const row = document.querySelector(`[data-item-row="${index}"]`);
            const product = manualProducts.find((item) => Number(item.id) === Number(productId));
            if (!row || !product) return;

            row.querySelector('.manual-price').value = product.price || 0;
            const hint = row.querySelector('[data-stock-hint]');
            const tone = product.status !== 'active' || Number(product.stock || 0) < 1
                ? 'text-amber-600 dark:text-amber-400'
                : 'text-slate-400';
            hint.className = 'mt-1 text-xs ' + tone;
            hint.textContent = `${product.sku ? 'SKU ' + product.sku + ' | ' : ''}Stok ${product.stock}${product.status !== 'active' ? ' | Produk nonaktif' : ''}`;

            if (shouldRecalculate) recalculateManualOrder();
        }

        function recalculateManualOrder() {
            let subtotal = 0;
            document.querySelectorAll('[data-item-row]').forEach((row) => {
                const qty = Math.max(1, Number(row.querySelector('.manual-qty')?.value || 1));
                const price = moneyValue(row.querySelector('.manual-price'));
                const discount = moneyValue(row.querySelector('.manual-item-discount'));
                const itemSubtotal = Math.max(0, (qty * price) - discount);
                row.querySelector('[data-item-subtotal]').textContent = formatRupiah(itemSubtotal);
                subtotal += itemSubtotal;
            });

            const discountAmount = Math.min(subtotal, moneyValue(document.getElementById('discount_amount')));
            const shippingCost = moneyValue(document.getElementById('shipping_cost'));
            const grandTotal = Math.max(0, subtotal - discountAmount) + shippingCost;

            document.getElementById('summarySubtotal').textContent = formatRupiah(subtotal);
            document.getElementById('summaryDiscount').textContent = '- ' + formatRupiah(discountAmount);
            document.getElementById('summaryShipping').textContent = formatRupiah(shippingCost);
            document.getElementById('summaryGrandTotal').textContent = formatRupiah(grandTotal);
        }

        addManualItem();
        syncCustomerSnapshot();
    </script>
@endsection
