<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let soItemIndex = 0;

    const SO_SEARCH_CUSTOMERS_URL = "{{ route('sales-orders.search-customers') }}";
    const SO_SEARCH_PRODUCTS_URL  = "{{ route('sales-orders.search-products') }}";

    function soFormatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function soMoneyValue(input) {
        return Math.max(0, Number(input?.value || 0));
    }

    function setSoCustomerMode(mode) {
        document.getElementById('soExistingCustomerPanel')?.classList.toggle('hidden', mode !== 'existing');
        document.getElementById('soManualCustomerPanel')?.classList.toggle('hidden', mode !== 'manual');
    }

    $(function () {
        $('#so_customer_id').select2({
            width: '100%',
            placeholder: 'Cari nama atau email customer...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: SO_SEARCH_CUSTOMERS_URL,
                dataType: 'json',
                delay: 300,
                data: params => ({ q: params.term || '' }),
                processResults: data => ({ results: data.results }),
                cache: true,
            },
        });

        $('#so_customer_id').on('select2:select', function (e) {
            const item = e.params.data;
            const box  = document.getElementById('soCustomerSnapshot');
            if (!box) return;
            box.innerHTML = `<p class="font-semibold text-slate-800 dark:text-slate-200">${item.name}</p><p class="mt-1 text-xs">${item.email || '-'}${item.phone ? ' / ' + item.phone : ''}</p>`;
        });

        $('#so_customer_id').on('select2:clear', function () {
            const box = document.getElementById('soCustomerSnapshot');
            if (box) box.innerHTML = 'Belum ada customer dipilih.';
        });

        addSoItem();
    });

    function addSoItem() {
        const index = soItemIndex++;
        const body  = document.getElementById('soItemsBody');
        if (!body) return;
        const row   = document.createElement('tr');
        row.dataset.itemRow = String(index);
        row.innerHTML = `
            <td class="product-col px-3 py-3 align-top">
                <select id="so_product_select_${index}" name="items[${index}][product_variant_id]" class="so-product-select2" style="width:100%"></select>
                <p data-stock-hint class="mt-1 text-xs text-slate-400"></p>
            </td>
            <td class="px-3 py-3 align-top">
                <input name="items[${index}][qty]" type="number" min="1" step="1" value="1" oninput="recalculateSalesOrder()"
                    class="so-qty w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </td>
            <td class="px-3 py-3 align-top">
                <input name="items[${index}][price]" type="number" min="0" step="1" value="0" oninput="recalculateSalesOrder()"
                    class="so-price w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </td>
            <td class="px-3 py-3 text-right align-top">
                <span data-item-subtotal class="font-semibold text-slate-800 dark:text-slate-200">Rp 0</span>
            </td>
            <td class="px-3 py-3 text-right align-top">
                <button type="button" onclick="removeSoItem(${index})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                </button>
            </td>`;
        body.appendChild(row);

        $(`#so_product_select_${index}`).select2({
            width: 'resolve',
            placeholder: 'Cari produk atau SKU...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: SO_SEARCH_PRODUCTS_URL,
                dataType: 'json',
                delay: 300,
                data: params => ({ q: params.term || '' }),
                processResults: data => ({ results: data.results }),
                cache: true,
            },
        });

        $(`#so_product_select_${index}`).on('select2:select', function (e) {
            const product = e.params.data;
            const row     = document.querySelector(`[data-item-row="${index}"]`);
            if (!row || !product) return;

            row.querySelector('.so-price').value = product.price || 0;
            const hint  = row.querySelector('[data-stock-hint]');
            const isLow = product.status !== 'active' || Number(product.stock || 0) < 1;
            hint.className  = 'mt-1 text-xs ' + (isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400');
            hint.textContent = `${product.sku ? 'SKU ' + product.sku + ' | ' : ''}Stok ${product.stock}${product.status !== 'active' ? ' | Produk nonaktif' : ''}`;
            recalculateSalesOrder();
        });

        $(`#so_product_select_${index}`).on('select2:clear', function () {
            const row = document.querySelector(`[data-item-row="${index}"]`);
            if (!row) return;
            row.querySelector('.so-price').value = 0;
            row.querySelector('[data-stock-hint]').textContent = '';
            recalculateSalesOrder();
        });

        recalculateSalesOrder();
        window.lucide?.createIcons?.();
    }

    function removeSoItem(index) {
        const row = document.querySelector(`[data-item-row="${index}"]`);
        if (row && document.querySelectorAll('[data-item-row]').length > 1) {
            $(`#so_product_select_${index}`).select2('destroy');
            row.remove();
            recalculateSalesOrder();
        }
    }

    function recalculateSalesOrder() {
        let subtotal = 0;
        document.querySelectorAll('[data-item-row]').forEach((row) => {
            const qty     = Math.max(1, Number(row.querySelector('.so-qty')?.value || 1));
            const price   = soMoneyValue(row.querySelector('.so-price'));
            const itemSub = qty * price;
            row.querySelector('[data-item-subtotal]').textContent = soFormatRupiah(itemSub);
            subtotal += itemSub;
        });

        const discountAmount = Math.min(subtotal, soMoneyValue(document.getElementById('so_discount_amount')));
        const taxable        = Math.max(0, subtotal - discountAmount);
        const ppnRate         = Math.max(0, Math.min(100, parseFloat(document.getElementById('so_ppn_rate')?.value || 0)));
        const ppnAmount       = Math.round(taxable * ppnRate / 100);
        const shippingCost    = soMoneyValue(document.getElementById('so_shipping_cost'));
        const adminFee        = soMoneyValue(document.getElementById('so_admin_fee'));
        const otherCost       = soMoneyValue(document.getElementById('so_other_cost'));
        const grandTotal      = taxable + ppnAmount + shippingCost + adminFee + otherCost;

        document.getElementById('soSummarySubtotal').textContent   = soFormatRupiah(subtotal);
        document.getElementById('soSummaryDiscount').textContent   = discountAmount > 0 ? '- ' + soFormatRupiah(discountAmount) : 'Rp 0';
        document.getElementById('soSummaryPpn').textContent        = soFormatRupiah(ppnAmount);
        document.getElementById('soSummaryGrandTotal').textContent = soFormatRupiah(grandTotal);
    }
</script>
