@extends('layouts.user')

@section('title', 'Keranjang - Ecommerce Citra')

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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
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

        let cartItems = Array.isArray(initialCartItems) ? [...initialCartItems] : [];
        const selectedCartIds = new Set(cartItems.map((item) => Number(item.cartId)));

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const text = document.getElementById('toast-msg');
            if (!toast || !text) return;
            text.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function updateSummary() {
            const selectedItems = cartItems.filter((item) => selectedCartIds.has(Number(item.cartId)));
            const totalItems = selectedItems.reduce((sum, item) => sum + Number(item.qty || 0), 0);
            const subtotal = selectedItems.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0);
            document.getElementById('itemCountText').textContent = totalItems + ' item dipilih';
            document.getElementById('sumItems').textContent = totalItems + ' item';
            document.getElementById('subtotalAmt').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('grandTotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            const checkoutBtn = document.getElementById('checkoutBtn');
            checkoutBtn.classList.toggle('pointer-events-none', totalItems <= 0);
            checkoutBtn.classList.toggle('opacity-60', totalItems <= 0);
            const selectAll = document.getElementById('selectAllCart');
            if (selectAll) {
                const activeTotal = cartItems.length;
                const selectedTotal = cartItems.filter((item) => selectedCartIds.has(Number(item.cartId))).length;
                selectAll.checked = activeTotal > 0 && activeTotal === selectedTotal;
            }
        }

        async function changeQty(idx, delta) {
            const item = cartItems[idx];
            if (!item) return;
            const nextQty = Math.max(1, Number(item.qty || 1) + delta);
            const url = cartUpdateUrlTemplate.replace('__ID__', String(item.cartId));
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
            if (!res.ok) return;
            cartItems[idx].qty = nextQty;
            renderCart();
            window.dispatchEvent(new Event('cart:updated'));
        }

        async function removeItem(idx) {
            const item = cartItems[idx];
            if (!item) return;
            const url = cartDeleteUrlTemplate.replace('__ID__', String(item.cartId));
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) return;
            selectedCartIds.delete(Number(item.cartId));
            cartItems.splice(idx, 1);
            renderCart();
            showToast('Item berhasil dihapus dari keranjang.');
            window.dispatchEvent(new Event('cart:updated'));
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
            const ids = Array.from(selectedCartIds.values());
            if (!ids.length) {
                showToast('Pilih minimal 1 produk untuk checkout.');
                return;
            }
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
            if (!res.ok) {
                showToast('Gagal menyiapkan checkout.');
                return;
            }
            const data = await res.json();
            window.location.href = data.redirect || "{{ route('frontend.checkout') }}";
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            if (!cartItems.length) {
                container.innerHTML = `
                    <div class="py-10 text-center">
                        <p class="text-slate-500 text-sm mb-3">Keranjang masih kosong.</p>
                        <a href="{{ route('frontend.index') }}" class="inline-flex items-center gap-2 text-blue-600 text-sm font-semibold hover:text-blue-700">
                            Belanja sekarang
                        </a>
                    </div>`;
                updateSummary();
                return;
            }

            container.innerHTML = cartItems.map((item, i) => `
                <div class="relative flex gap-3 py-4 pr-7 ${i > 0 ? 'border-t border-slate-100' : ''}">
                  <input type="checkbox" class="mt-5 accent-blue-500 flex-shrink-0" ${selectedCartIds.has(Number(item.cartId)) ? 'checked' : ''} onchange="toggleItemSelection(${Number(item.cartId)}, this.checked)" />
                  <a href="{{ url('/detail-produk') }}/${item.slug}" class="flex-shrink-0">
                    <img src="${item.image}" alt="${item.name}" class="w-16 h-16 rounded-xl object-cover" />
                  </a>
                  <div class="flex-1 min-w-0">
                    <a href="{{ url('/detail-produk') }}/${item.slug}" class="font-semibold text-slate-800 text-sm line-clamp-2 mb-0.5 hover:text-blue-600 transition-colors block">${item.name}</a>
                    <p class="text-xs text-slate-500 mb-2">${item.variant}</p>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                      <div>
                        <span class="font-bold text-slate-900 text-sm">Rp ${Number(item.price || 0).toLocaleString('id-ID')}</span>
                        ${Number(item.origPrice || 0) > Number(item.price || 0) ? `<span class="text-xs text-slate-400 line-through ml-1">Rp ${Number(item.origPrice || 0).toLocaleString('id-ID')}</span>` : ''}
                      </div>
                      <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden self-start sm:self-auto">
                        <button class="px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm" onclick="changeQty(${i}, -1)">-</button>
                        <span class="px-3 py-1 text-sm font-semibold border-x border-slate-200">${item.qty}</span>
                        <button class="px-2.5 py-1 text-slate-500 hover:bg-slate-50 transition-colors text-sm" onclick="changeQty(${i}, 1)">+</button>
                      </div>
                    </div>
                  </div>
                  <button onclick="removeItem(${i})" class="absolute top-4 right-0 sm:static text-slate-300 hover:text-red-400 transition-colors flex-shrink-0 self-start">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>`).join('');

            updateSummary();
        }

        document.getElementById('checkoutBtn')?.addEventListener('click', proceedCheckout);
        renderCart();
    </script>
@endsection
