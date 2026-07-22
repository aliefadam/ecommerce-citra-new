<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let quotationItemIndex = 0;

    const SEARCH_CUSTOMERS_URL = "{{ route('quotations.search-customers') }}";
    const SEARCH_PRODUCTS_URL  = "{{ route('quotations.search-products') }}";
    const seedItems = @json($seedItems ?? []);
    const seedCustomer = @json($seedCustomer ?? null);

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
        });

        $('#customer_id').on('select2:select', function (e) {
            const item = e.params.data;
            const box  = document.getElementById('customerSnapshot');
            if (!box) return;
            box.innerHTML = `<p class="font-semibold text-slate-800 dark:text-slate-200">${item.name}</p><p class="mt-1 text-xs">${item.email || '-'}${item.phone ? ' / ' + item.phone : ''}</p>`;
        });

        $('#customer_id').on('select2:clear', function () {
            const box = document.getElementById('customerSnapshot');
            if (box) box.innerHTML = 'Belum ada customer dipilih.';
        });

        if (seedCustomer) {
            const option = new Option(seedCustomer.text, seedCustomer.id, true, true);
            $('#customer_id').append(option).trigger('change');
        }

        if (!document.getElementById('quotationItemsBody')) return; // items locked (read-only), no item builder on this page

        if (seedItems.length) {
            seedItems.forEach(seed => addQuotationItem(seed));
        } else {
            addQuotationItem();
        }
    });

    function addQuotationItem(seed) {
        seed = seed || {};
        const index = quotationItemIndex++;
        const body  = document.getElementById('quotationItemsBody');
        if (!body) return;
        const row   = document.createElement('tr');
        row.dataset.itemRow = String(index);
        row.innerHTML = `
            <td class="product-col px-3 py-3 align-top">
                <select id="product_select_${index}" name="items[${index}][product_variant_id]" class="product-select2" style="width:100%"></select>
                <p data-stock-hint class="mt-1 text-xs text-slate-400"></p>
            </td>
            <td class="px-3 py-3 align-top">
                <input name="items[${index}][qty]" type="number" min="1" step="1" value="${seed.qty || 1}" oninput="recalculateQuotation()"
                    class="quotation-qty w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </td>
            <td class="px-3 py-3 align-top">
                <input name="items[${index}][price]" type="number" min="0" step="1" value="${seed.price || 0}" oninput="recalculateQuotation()"
                    class="quotation-price w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </td>
            <td class="px-3 py-3 align-top">
                <input name="items[${index}][note]" type="text" value="${seed.note || ''}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
            </td>
            <td class="px-3 py-3 text-right align-top">
                <span data-item-subtotal class="font-semibold text-slate-800 dark:text-slate-200">Rp 0</span>
            </td>
            <td class="px-3 py-3 text-right align-top">
                <button type="button" onclick="removeQuotationItem(${index})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                </button>
            </td>`;
        body.appendChild(row);

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

        if (seed.product_variant_id) {
            const option = new Option(seed.text || seed.product_name || 'Produk', seed.product_variant_id, true, true);
            $(`#product_select_${index}`).append(option).trigger('change');
            const hint = row.querySelector('[data-stock-hint]');
            if (hint && seed.sku) hint.textContent = 'SKU ' + seed.sku;
        }

        $(`#product_select_${index}`).on('select2:select', function (e) {
            const product = e.params.data;
            const row     = document.querySelector(`[data-item-row="${index}"]`);
            if (!row || !product) return;

            row.querySelector('.quotation-price').value = product.price || 0;
            const hint  = row.querySelector('[data-stock-hint]');
            const isLow = product.status !== 'active' || Number(product.stock || 0) < 1;
            hint.className  = 'mt-1 text-xs ' + (isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400');
            hint.textContent = `${product.sku ? 'SKU ' + product.sku + ' | ' : ''}Stok ${product.stock}${product.status !== 'active' ? ' | Produk nonaktif' : ''}`;
            recalculateQuotation();
        });

        $(`#product_select_${index}`).on('select2:clear', function () {
            const row = document.querySelector(`[data-item-row="${index}"]`);
            if (!row) return;
            row.querySelector('.quotation-price').value = 0;
            row.querySelector('[data-stock-hint]').textContent = '';
            recalculateQuotation();
        });

        recalculateQuotation();
        window.lucide?.createIcons?.();
    }

    function removeQuotationItem(index) {
        const row = document.querySelector(`[data-item-row="${index}"]`);
        if (row && document.querySelectorAll('[data-item-row]').length > 1) {
            $(`#product_select_${index}`).select2('destroy');
            row.remove();
            recalculateQuotation();
        }
    }

    function recalculateQuotation() {
        let subtotal = 0;
        document.querySelectorAll('[data-item-row]').forEach((row) => {
            const qty     = Math.max(1, Number(row.querySelector('.quotation-qty')?.value || 1));
            const price   = moneyValue(row.querySelector('.quotation-price'));
            const itemSub = qty * price;
            row.querySelector('[data-item-subtotal]').textContent = formatRupiah(itemSub);
            subtotal += itemSub;
        });

        const discountAmount = Math.min(subtotal, moneyValue(document.getElementById('discount_amount')));
        const taxable        = Math.max(0, subtotal - discountAmount);
        const ppnRate         = Math.max(0, Math.min(100, parseFloat(document.getElementById('ppn_rate')?.value || 0)));
        const ppnAmount       = Math.round(taxable * ppnRate / 100);
        const shippingCost    = moneyValue(document.getElementById('shipping_cost'));
        const adminFee        = moneyValue(document.getElementById('admin_fee'));
        const otherCost       = moneyValue(document.getElementById('other_cost'));
        const grandTotal      = taxable + ppnAmount + shippingCost + adminFee + otherCost;

        document.getElementById('summarySubtotal').textContent   = formatRupiah(subtotal);
        document.getElementById('summaryDiscount').textContent   = discountAmount > 0 ? '- ' + formatRupiah(discountAmount) : 'Rp 0';
        if (document.getElementById('summaryPpn')) document.getElementById('summaryPpn').textContent = formatRupiah(ppnAmount);
        document.getElementById('summaryGrandTotal').textContent = formatRupiah(grandTotal);
    }
</script>
