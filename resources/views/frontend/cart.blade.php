@extends('layouts.user')

@section('title', 'Keranjang - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
        <div
            class="flex items-center gap-3 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <span id="toast-msg">Berhasil</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5">
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-slate-100 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Keranjang Belanja
                        </h2>
                        <span class="text-sm text-slate-500" id="itemCountText">0 item</span>
                    </div>
                    <div class="px-6 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="selectAllCart" class="accent-blue-500" onchange="toggleSelectAll(this.checked)" />
                            <span class="text-sm text-slate-600">Pilih semua</span>
                        </label>
                    </div>
                    <div id="cartItems" class="divide-y divide-slate-100 p-4 space-y-0"></div>
                </div>
            </div>
            <aside>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sticky top-20">
                    <h3 class="font-bold text-slate-800 mb-4">Ringkasan Belanja</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Total Item</span>
                            <span id="sumItems">0 item</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span id="subtotalAmt">Rp 0</span>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 mt-4 pt-4 flex items-center justify-between">
                        <span class="font-semibold text-slate-700">Total</span>
                        <span id="grandTotal" class="font-extrabold text-blue-600">Rp 0</span>
                    </div>
                    <a href="{{ route('frontend.checkout') }}" id="checkoutBtn"
                        class="mt-5 w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold py-3 rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all shadow-lg shadow-blue-200">
                        Checkout
                    </a>
                </div>
            </aside>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const initialCartItems = @json($cartItems ?? []);
        const csrfToken = @json(csrf_token());
        const cartUpdateUrlTemplate = @json(route('frontend.cart.update', ['cart' => '__ID__']));
        const cartDeleteUrlTemplate = @json(route('frontend.cart.destroy', ['cart' => '__ID__']));
        const prepareCheckoutUrl = @json(route('frontend.cart.prepare-checkout'));
        const detailProductBaseUrl = @json(url('/detail-produk'));
        const cartIndexUrl = @json(route('frontend.index'));

        let cartItems = Array.isArray(initialCartItems) ? [...initialCartItems] : [];
        const selectedCartIds = new Set(cartItems.map((item) => Number(item.cartId)));
        const pendingCartIds = new Set();
        let isCheckoutLoading = false;

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const text = document.getElementById('toast-msg');
            if (!toast || !text) return;
            text.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function getErrorMessage(error, fallbackMessage) {
            if (error && typeof error === 'object' && 'message' in error && error.message) {
                return String(error.message);
            }

            return fallbackMessage;
        }

        async function parseJsonSafe(res) {
            try {
                return await res.json();
            } catch (error) {
                return null;
            }
        }

        function formatCurrency(amount) {
            return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
        }

        function setCheckoutLoadingState(isLoading) {
            isCheckoutLoading = isLoading;
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (!checkoutBtn) return;

            checkoutBtn.classList.toggle('pointer-events-none', isLoading);
            checkoutBtn.classList.toggle('opacity-60', isLoading);
            checkoutBtn.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
            checkoutBtn.textContent = isLoading ? 'Menyiapkan Checkout...' : 'Checkout';
        }

        function updateSummary() {
            const selectedItems = cartItems.filter((item) => selectedCartIds.has(Number(item.cartId)));
            const totalItems = selectedItems.reduce((sum, item) => sum + Number(item.qty || 0), 0);
            const subtotal = selectedItems.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0);
            document.getElementById('itemCountText').textContent = cartItems.length ? (totalItems + ' item dipilih') : 'Keranjang kosong';
            document.getElementById('sumItems').textContent = totalItems + ' item';
            document.getElementById('subtotalAmt').textContent = formatCurrency(subtotal);
            document.getElementById('grandTotal').textContent = formatCurrency(subtotal);
            const checkoutBtn = document.getElementById('checkoutBtn');
            const shouldDisableCheckout = totalItems <= 0 || isCheckoutLoading;
            checkoutBtn.classList.toggle('pointer-events-none', shouldDisableCheckout);
            checkoutBtn.classList.toggle('opacity-60', shouldDisableCheckout);
            checkoutBtn.setAttribute('aria-disabled', shouldDisableCheckout ? 'true' : 'false');
            const selectAll = document.getElementById('selectAllCart');
            if (selectAll) {
                const activeTotal = cartItems.length;
                const selectedTotal = cartItems.filter((item) => selectedCartIds.has(Number(item.cartId))).length;
                selectAll.checked = activeTotal > 0 && activeTotal === selectedTotal;
                selectAll.indeterminate = selectedTotal > 0 && selectedTotal < activeTotal;
            }
        }

        async function changeQty(idx, delta) {
            const item = cartItems[idx];
            if (!item || pendingCartIds.has(Number(item.cartId))) return;
            const nextQty = Math.max(1, Number(item.qty || 1) + delta);
            if (nextQty === Number(item.qty || 1)) return;
            const url = cartUpdateUrlTemplate.replace('__ID__', String(item.cartId));
            pendingCartIds.add(Number(item.cartId));
            renderCart();

            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        quantity: nextQty
                    }),
                });
                const data = await parseJsonSafe(res);
                if (!res.ok) {
                    throw new Error(data?.message || 'Gagal memperbarui jumlah item.');
                }

                cartItems[idx].qty = nextQty;
                renderCart();
                showToast(data?.message || 'Jumlah item berhasil diperbarui.');
                window.dispatchEvent(new Event('cart:updated'));
            } catch (error) {
                showToast(getErrorMessage(error, 'Gagal memperbarui jumlah item.'));
            } finally {
                pendingCartIds.delete(Number(item.cartId));
                renderCart();
            }
        }

        async function removeItem(idx) {
            const item = cartItems[idx];
            if (!item || pendingCartIds.has(Number(item.cartId))) return;
            const url = cartDeleteUrlTemplate.replace('__ID__', String(item.cartId));
            pendingCartIds.add(Number(item.cartId));
            renderCart();

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await parseJsonSafe(res);
                if (!res.ok) {
                    throw new Error(data?.message || 'Gagal menghapus item dari keranjang.');
                }

                selectedCartIds.delete(Number(item.cartId));
                cartItems.splice(idx, 1);
                renderCart();
                showToast(data?.message || 'Item berhasil dihapus dari keranjang.');
                window.dispatchEvent(new Event('cart:updated'));
            } catch (error) {
                showToast(getErrorMessage(error, 'Gagal menghapus item dari keranjang.'));
            } finally {
                pendingCartIds.delete(Number(item.cartId));
                renderCart();
            }
        }

        function toggleSelectAll(checked) {
            if (checked) {
                cartItems.forEach((item) => selectedCartIds.add(Number(item.cartId)));
            } else {
                selectedCartIds.clear();
            }
            renderCart();
        }

        function toggleItemSelection(cartId, checked) {
            const id = Number(cartId);
            if (checked) selectedCartIds.add(id);
            else selectedCartIds.delete(id);
            updateSummary();
        }

        async function proceedCheckout(e) {
            if (e) e.preventDefault();
            if (isCheckoutLoading) return;
            const ids = Array.from(selectedCartIds.values());
            if (!ids.length) {
                showToast('Pilih minimal 1 produk untuk checkout.');
                return;
            }

            setCheckoutLoadingState(true);
            updateSummary();

            try {
                const res = await fetch(prepareCheckoutUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        cart_ids: ids
                    }),
                });
                const data = await parseJsonSafe(res);
                if (!res.ok) {
                    throw new Error(data?.message || 'Gagal menyiapkan checkout.');
                }

                window.location.href = data?.redirect || "{{ route('frontend.checkout') }}";
            } catch (error) {
                showToast(getErrorMessage(error, 'Gagal menyiapkan checkout.'));
                setCheckoutLoadingState(false);
                updateSummary();
            }
        }

        function createCartItemElement(item, idx) {
            const row = document.createElement('div');
            row.className = 'relative flex gap-3 py-4 pr-7';
            if (idx > 0) {
                row.classList.add('border-t', 'border-slate-100');
            }
            if (pendingCartIds.has(Number(item.cartId))) {
                row.classList.add('opacity-60');
            }

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'mt-5 accent-blue-500 flex-shrink-0';
            checkbox.checked = selectedCartIds.has(Number(item.cartId));
            checkbox.disabled = pendingCartIds.has(Number(item.cartId));
            checkbox.setAttribute('aria-label', `Pilih produk ${item.name}`);
            checkbox.addEventListener('change', function() {
                toggleItemSelection(Number(item.cartId), this.checked);
            });

            const productLink = document.createElement('a');
            productLink.href = `${detailProductBaseUrl}/${encodeURIComponent(item.slug || '')}`;
            productLink.className = 'flex-shrink-0';

            const image = document.createElement('img');
            image.src = item.image || '';
            image.alt = item.name || 'Produk';
            image.className = 'w-16 h-16 rounded-xl object-cover';
            productLink.appendChild(image);

            const content = document.createElement('div');
            content.className = 'flex-1 min-w-0';

            const title = document.createElement('a');
            title.href = productLink.href;
            title.className = 'font-semibold text-slate-800 text-sm line-clamp-2 mb-0.5 hover:text-blue-600 transition-colors block';
            title.textContent = item.name || 'Produk';

            const variant = document.createElement('p');
            variant.className = 'text-xs text-slate-500 mb-2';
            variant.textContent = item.variant || '-';

            const meta = document.createElement('div');
            meta.className = 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2';

            const priceWrap = document.createElement('div');

            const price = document.createElement('span');
            price.className = 'font-bold text-slate-900 text-sm';
            price.textContent = formatCurrency(item.price);
            priceWrap.appendChild(price);

            if (Number(item.origPrice || 0) > Number(item.price || 0)) {
                const origPrice = document.createElement('span');
                origPrice.className = 'text-xs text-slate-400 line-through ml-1';
                origPrice.textContent = formatCurrency(item.origPrice);
                priceWrap.appendChild(origPrice);
            }

            const qtyControl = document.createElement('div');
            qtyControl.className = 'inline-flex items-center border border-slate-200 rounded-lg overflow-hidden self-start sm:self-auto';

            const decreaseBtn = document.createElement('button');
            decreaseBtn.type = 'button';
            decreaseBtn.className = 'px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed';
            decreaseBtn.textContent = '-';
            decreaseBtn.disabled = pendingCartIds.has(Number(item.cartId)) || Number(item.qty || 1) <= 1;
            decreaseBtn.setAttribute('aria-label', `Kurangi jumlah ${item.name}`);
            decreaseBtn.addEventListener('click', () => changeQty(idx, -1));

            const qtyText = document.createElement('span');
            qtyText.className = 'px-3 py-1 text-sm font-semibold border-x border-slate-200';
            qtyText.textContent = String(item.qty || 1);

            const increaseBtn = document.createElement('button');
            increaseBtn.type = 'button';
            increaseBtn.className = 'px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed';
            increaseBtn.textContent = '+';
            increaseBtn.disabled = pendingCartIds.has(Number(item.cartId)) || Number(item.qty || 0) >= Number(item.stock || 0);
            increaseBtn.setAttribute('aria-label', `Tambah jumlah ${item.name}`);
            increaseBtn.addEventListener('click', () => changeQty(idx, 1));

            qtyControl.append(decreaseBtn, qtyText, increaseBtn);

            meta.append(priceWrap, qtyControl);
            content.append(title, variant, meta);

            if (Number(item.stock || 0) > 0) {
                const stockNote = document.createElement('p');
                stockNote.className = 'mt-2 text-[11px] text-slate-400';
                stockNote.textContent = `Stok tersedia: ${Number(item.stock || 0)}`;
                content.appendChild(stockNote);
            }

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'absolute top-4 right-0 sm:static text-slate-300 hover:text-red-400 transition-colors flex-shrink-0 self-start disabled:opacity-50 disabled:cursor-not-allowed';
            removeBtn.disabled = pendingCartIds.has(Number(item.cartId));
            removeBtn.setAttribute('aria-label', `Hapus produk ${item.name} dari keranjang`);
            removeBtn.innerHTML =
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            removeBtn.addEventListener('click', () => removeItem(idx));

            row.append(checkbox, productLink, content, removeBtn);

            return row;
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            if (!cartItems.length) {
                container.innerHTML = '';

                const emptyState = document.createElement('div');
                emptyState.className = 'py-10 text-center';

                const text = document.createElement('p');
                text.className = 'text-slate-500 text-sm mb-3';
                text.textContent = 'Keranjang masih kosong.';

                const link = document.createElement('a');
                link.href = cartIndexUrl;
                link.className = 'inline-flex items-center gap-2 text-blue-600 text-sm font-semibold hover:text-blue-700';
                link.textContent = 'Belanja sekarang';

                emptyState.append(text, link);
                container.appendChild(emptyState);
                updateSummary();
                return;
            }

            container.innerHTML = '';
            cartItems.forEach((item, i) => {
                container.appendChild(createCartItemElement(item, i));
            });

            updateSummary();
        }

        document.getElementById('checkoutBtn')?.addEventListener('click', proceedCheckout);
        renderCart();
    </script>
@endsection
