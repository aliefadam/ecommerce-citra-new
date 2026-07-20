@extends('layouts.user')

@section('title', 'Hasil Pencarian - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12); }
        .filter-chip { display:inline-flex; align-items:center; gap:8px; border-radius:999px; padding:8px 12px; font-size:12px; font-weight:600; background:#eff6ff; color:#1d4ed8; }
        .filter-drawer-handle { width:40px; height:5px; background:#cbd5e1; border-radius:9999px; margin:0 auto 16px; cursor:grab; touch-action:none; }
        .filter-drawer-handle:active { cursor:grabbing; }
        @media (max-width: 1023px) {
            #filterSidebar.mobile-filter-drawer { position:fixed; inset:0; z-index:60; display:flex; align-items:flex-end; background:rgba(15, 23, 42, 0); opacity:0; transition:background 0.28s ease, opacity 0.28s ease; }
            #filterSidebar.mobile-filter-drawer:not(.mobile-filter-open) { pointer-events:none; }
            #filterSidebar.mobile-filter-drawer.mobile-filter-open { background:rgba(15, 23, 42, 0.4); opacity:1; pointer-events:auto; }
            #filterPanel.mobile-filter-panel { width:100%; max-height:85vh; overflow-y:auto; overscroll-behavior:contain; border:0; border-radius:24px 24px 0 0; position:relative; top:auto; transform:translateY(calc(100% + 24px)); transition:transform 0.32s cubic-bezier(0.22, 1, 0.36, 1); will-change:transform; }
            #filterSidebar.mobile-filter-open #filterPanel.mobile-filter-panel { transform:translateY(0); }
        }
    </style>
@endsection
@section('content')
    @include('partials.navbar-user')

    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-800 font-medium">Hasil Pencarian</span>
            </nav>
        </div>
    </div>

    <section class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h1 class="text-2xl md:text-3xl font-bold mb-2">Hasil Pencarian</h1>
            <p id="searchMeta" class="text-blue-100 text-sm"></p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div id="activeFilters" class="hidden flex flex-wrap gap-2 mb-4"></div>

        <div class="flex flex-col lg:flex-row gap-6">
            <aside id="filterSidebar" class="hidden lg:block lg:w-72 flex-shrink-0">
                <div id="filterPanel" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sticky top-20 space-y-5">
                    <div id="filterDrawerHandle" class="filter-drawer-handle lg:hidden"></div>
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">Filter Pencarian</h3>
                        <div class="flex items-center gap-3">
                            <button onclick="resetFilters()" class="text-xs text-blue-600 font-medium">Reset</button>
                            <button onclick="closeMobileFilter()" class="lg:hidden text-xs text-slate-500 font-medium">Tutup</button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Kategori</h4>
                        <div id="categoryFilterList" class="space-y-2"></div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Harga</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <input id="priceMin" type="number" min="0" placeholder="Min" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
                            <input id="priceMax" type="number" min="0" placeholder="Max" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Status Produk</h4>
                        <label class="flex items-center gap-2 text-sm text-slate-600 mb-2"><input id="filterPromo" type="checkbox" class="accent-blue-500"> Hanya promo / flash sale</label>
                        <label class="flex items-center gap-2 text-sm text-slate-600"><input id="filterStock" type="checkbox" class="accent-blue-500"> Hanya stok tersedia</label>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Rating</h4>
                        <select id="ratingMin" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:border-blue-400 bg-white">
                            <option value="0">Semua rating</option>
                            <option value="4">4 ke atas</option>
                            <option value="4.5">4.5 ke atas</option>
                            <option value="5">5 saja</option>
                        </select>
                    </div>

                    <div id="variantFilterList" class="space-y-5"></div>

                    <button onclick="applyFilters()" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 rounded-xl transition-colors text-sm">Terapkan Filter</button>
                </div>
            </aside>

            <main class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <p class="text-sm text-slate-500" id="resultCount">Menampilkan 0 produk</p>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex items-center gap-2 sm:hidden">
                            <button type="button" onclick="openMobileFilter()" class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 flex items-center justify-center"><i class="ri-filter-3-line"></i></button>
                        </div>
                        <select id="sortSel" onchange="applyFilters()" class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-blue-400 bg-white">
                            <option value="relevant">Paling Relevan</option>
                            <option value="newest">Terbaru</option>
                            <option value="cheap">Harga Termurah</option>
                            <option value="expensive">Harga Termahal</option>
                            <option value="rating">Rating Tertinggi</option>
                            <option value="sold">Terlaris</option>
                        </select>
                    </div>
                </div>

                <div id="searchResultGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-4"></div>
                <div id="emptyState" class="hidden text-center py-16 bg-white rounded-2xl border border-slate-100">
                    <div class="text-5xl mb-3">🔎</div>
                    <p class="text-lg font-semibold text-slate-700">Produk tidak ditemukan</p>
                    <p class="text-slate-500 text-sm mt-1 mb-5">Coba ubah filter atau gunakan kata kunci yang lebih umum.</p>
                    <button onclick="resetFilters()" class="bg-blue-500 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-blue-600 transition-colors">Reset Filter</button>
                </div>
            </main>
        </div>
    </section>
@endsection

@section('script')
<script>
    const query = @json($query);
    const allProducts = @json($results ?? []);
    const searchMainCategories = @json($searchMainCategories ?? []);
    const filterOptionPreviewLimit = 4;
    let selectedVariantFilters = {};

    function normalizeFilterValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    }

    function renderCategoryFilters() {
        const container = document.getElementById('categoryFilterList');
        container.innerHTML = searchMainCategories.map(cat => `
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" class="filter-cat accent-blue-500" value="${cat.slug}">
                <span>${cat.name} (${cat.count})</span>
            </label>
        `).join('');
    }

    function renderVariantFilters() {
        const container = document.getElementById('variantFilterList');
        const groups = new Map();
        allProducts.forEach(product => {
            (Array.isArray(product.variants) ? product.variants : []).forEach(variant => {
                const name = String(variant.name || '').trim();
                const value = String(variant.value || '').trim();
                if (!name || !value) return;
                if (!groups.has(name)) groups.set(name, new Map());
                groups.get(name).set(normalizeFilterValue(value), value);
            });
        });

        container.innerHTML = Array.from(groups.entries()).map(([name, values]) => {
            const groupKey = normalizeFilterValue(name);
            return `<div class="filter-variant-group border-t border-slate-100 pt-3" data-variant-group="${encodeURIComponent(groupKey)}">
                <button type="button"
                    class="filter-variant-group-toggle flex w-full items-center justify-between gap-3 text-left"
                    data-variant-group="${encodeURIComponent(groupKey)}"
                    aria-expanded="false"
                    onclick="toggleVariantGroup(this)">
                    <span class="text-sm font-semibold text-slate-700">${escapeHtml(name)}</span>
                    <i class="ri-arrow-down-s-line text-lg text-slate-400 transition-transform"></i>
                </button>
                <div class="filter-variant-panel hidden pt-3" data-variant-group="${encodeURIComponent(groupKey)}">
                    <div class="filter-variant-options space-y-2" data-variant-group="${encodeURIComponent(groupKey)}">${Array.from(values.entries()).map(([key, label]) => `
                        <label class="filter-variant-option flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" class="filter-variant accent-blue-500" data-variant-name="${encodeURIComponent(groupKey)}" data-variant-value="${encodeURIComponent(key)}" onchange="updateVariantOptionVisibility(this.dataset.variantName || ''); openCheckedVariantGroups();">
                            <span>${escapeHtml(label)}</span>
                        </label>`).join('')}
                    </div>
                    <button type="button"
                        class="filter-variant-toggle mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700 ${values.size <= filterOptionPreviewLimit ? 'hidden' : ''}"
                        data-variant-group="${encodeURIComponent(groupKey)}"
                        data-expanded="false"
                        onclick="toggleVariantOptions(this)">
                        Lihat semua
                    </button>
                </div>
            </div>`;
        }).join('');

        document.querySelectorAll('.filter-variant-options').forEach((group) => updateVariantOptionVisibility(group.dataset.variantGroup || ''));
    }

    function toggleVariantGroup(button) {
        setVariantGroupExpanded(button.dataset.variantGroup || '', button.getAttribute('aria-expanded') !== 'true');
    }

    function setVariantGroupExpanded(group, expanded) {
        const button = document.querySelector(`.filter-variant-group-toggle[data-variant-group="${group}"]`);
        const panel = document.querySelector(`.filter-variant-panel[data-variant-group="${group}"]`);
        if (!button || !panel) return;

        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        panel.classList.toggle('hidden', !expanded);
        button.querySelector('i')?.classList.toggle('rotate-180', expanded);
    }

    function openCheckedVariantGroups() {
        document.querySelectorAll('.filter-variant:checked').forEach((input) => {
            setVariantGroupExpanded(input.dataset.variantName || '', true);
        });
    }

    function toggleVariantOptions(button) {
        button.dataset.expanded = button.dataset.expanded === 'true' ? 'false' : 'true';
        updateVariantOptionVisibility(button.dataset.variantGroup || '');
    }

    function updateVariantOptionVisibility(group) {
        const toggle = document.querySelector(`.filter-variant-toggle[data-variant-group="${group}"]`);
        const options = Array.from(document.querySelectorAll(`.filter-variant-options[data-variant-group="${group}"] .filter-variant-option`));
        const isExpanded = toggle?.dataset.expanded === 'true';

        options.forEach((option, index) => {
            const isChecked = option.querySelector('.filter-variant')?.checked;
            const isVisible = isExpanded || index < filterOptionPreviewLimit || isChecked;
            option.classList.toggle('hidden', !isVisible);
        });

        if (toggle) {
            toggle.classList.toggle('hidden', options.length <= filterOptionPreviewLimit);
            toggle.textContent = isExpanded ? 'Ringkas' : `Lihat semua (${options.length})`;
        }
    }

    function collectVariantFilters() {
        selectedVariantFilters = {};
        document.querySelectorAll('.filter-variant:checked').forEach(input => {
            const name = decodeURIComponent(input.dataset.variantName || '');
            const value = decodeURIComponent(input.dataset.variantValue || '');
            if (!selectedVariantFilters[name]) selectedVariantFilters[name] = new Set();
            selectedVariantFilters[name].add(value);
        });
    }

    function getFilteredProducts() {
        collectVariantFilters();
        const selectedCats = Array.from(document.querySelectorAll('.filter-cat:checked')).map(el => el.value);
        const priceMin = Number(document.getElementById('priceMin').value || 0);
        const priceMax = Number(document.getElementById('priceMax').value || 0);
        const promoOnly = document.getElementById('filterPromo').checked;
        const stockOnly = document.getElementById('filterStock').checked;
        const ratingMin = Number(document.getElementById('ratingMin').value || 0);
        const activeVariantGroups = Object.entries(selectedVariantFilters).filter(([, values]) => values.size > 0);

        let items = allProducts.filter(product => {
            const categoryMatch = selectedCats.length === 0 || selectedCats.includes(product.parentCategorySlug);
            const priceMatch = (!priceMin || Number(product.price) >= priceMin) && (!priceMax || Number(product.price) <= priceMax);
            const promoMatch = !promoOnly || !!product.isFlashSale;
            const stockMatch = !stockOnly || Number(product.stock || 0) > 0;
            const ratingMatch = Number(product.rating || 0) >= ratingMin;
            const variantMatch = activeVariantGroups.length === 0 || activeVariantGroups.every(([name, values]) =>
                Array.isArray(product.variants) && product.variants.some(variant => normalizeFilterValue(variant.name) === name && values.has(normalizeFilterValue(variant.value)))
            );
            return categoryMatch && priceMatch && promoMatch && stockMatch && ratingMatch && variantMatch;
        });

        const sort = document.getElementById('sortSel').value;
        if (sort === 'cheap') items.sort((a,b) => a.price - b.price);
        else if (sort === 'expensive') items.sort((a,b) => b.price - a.price);
        else if (sort === 'rating') items.sort((a,b) => b.rating - a.rating || b.sold - a.sold);
        else if (sort === 'sold') items.sort((a,b) => b.sold - a.sold || b.rating - a.rating);
        else if (sort === 'newest') items.sort((a,b) => b.id - a.id);
        else items.sort((a,b) => {
            const aq = String(a.name || '').toLowerCase().includes(String(query || '').toLowerCase()) ? 2 : 0;
            const bq = String(b.name || '').toLowerCase().includes(String(query || '').toLowerCase()) ? 2 : 0;
            const ar = Number(a.rating || 0) * 10 + Number(a.sold || 0);
            const br = Number(b.rating || 0) * 10 + Number(b.sold || 0);
            return (bq + br) - (aq + ar);
        });

        return items;
    }

    function renderActiveChips() {
        const wrap = document.getElementById('activeFilters');
        const chips = [];
        document.querySelectorAll('.filter-cat:checked').forEach(el => {
            const text = el.parentElement.querySelector('span')?.textContent || el.value;
            chips.push(text);
        });
        const priceMin = document.getElementById('priceMin').value;
        const priceMax = document.getElementById('priceMax').value;
        if (priceMin) chips.push(`Min Rp ${Number(priceMin).toLocaleString('id-ID')}`);
        if (priceMax) chips.push(`Max Rp ${Number(priceMax).toLocaleString('id-ID')}`);
        if (document.getElementById('filterPromo').checked) chips.push('Promo');
        if (document.getElementById('filterStock').checked) chips.push('Stok tersedia');
        const ratingMin = document.getElementById('ratingMin').value;
        if (Number(ratingMin) > 0) chips.push(`Rating ${ratingMin}+`);
        Object.entries(selectedVariantFilters).forEach(([name, values]) => values.forEach(v => chips.push(`${name}: ${v}`)));

        wrap.innerHTML = chips.map(chip => `<span class="filter-chip">${escapeHtml(chip)}</span>`).join('');
        wrap.classList.toggle('hidden', chips.length === 0);
    }

    function renderProducts(products) {
        const grid = document.getElementById('searchResultGrid');
        const empty = document.getElementById('emptyState');
        document.getElementById('resultCount').textContent = `Menampilkan ${products.length} produk`;
        document.getElementById('searchMeta').textContent = query ? `Menampilkan ${products.length} hasil untuk "${query}"` : `Menampilkan ${products.length} produk`;

        if (!products.length) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');

        grid.innerHTML = products.map((p) => `
            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 card-hover group flex flex-col">
                <a href="{{ url('/detail-produk') }}/${p.slug}" class="relative block overflow-hidden aspect-square">
                    <img src="${p.image}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="${escapeHtml(p.name)}" />
                    ${p.originalPrice > p.price ? `<span class="absolute top-1.5 left-1.5 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">-${Math.round((1 - p.price / p.originalPrice) * 100)}%</span>` : ''}
                    ${p.isFlashSale ? `<span class="absolute top-1.5 right-1.5 bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">PROMO</span>` : ''}
                </a>
                <div class="p-2 flex-1 flex flex-col">
                    <a href="{{ url('/detail-produk') }}/${p.slug}" class="text-[11px] sm:text-xs font-semibold text-slate-800 hover:text-blue-600 line-clamp-2 leading-snug transition-colors">${escapeHtml(p.name)}</a>
                    <div class="flex items-center gap-0.5 mt-1">
                        <span class="text-yellow-400 text-[10px]">★</span>
                        <span class="text-[10px] font-medium text-slate-600">${Number(p.rating || 0).toFixed(1)}</span>
                        <span class="text-[10px] text-slate-400 ml-0.5">· ${Number(p.sold || 0).toLocaleString('id-ID')} terjual</span>
                    </div>
                    <div class="mt-1 text-[10px] text-slate-400">${Number(p.stock || 0) > 0 ? `Stok ${Number(p.stock).toLocaleString('id-ID')}` : 'Stok habis'}</div>
                    ${p.storeName ? `<div class="text-[10px] text-slate-400 truncate">${escapeHtml(p.storeName)}</div>` : ''}
                    <div class="mt-auto pt-1">
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Rp ${Number(p.price).toLocaleString('id-ID')}</p>
                        ${p.originalPrice > p.price ? `<p class="text-[10px] text-slate-400 line-through">Rp ${Number(p.originalPrice).toLocaleString('id-ID')}</p>` : ''}
                        <a href="{{ url('/detail-produk') }}/${p.slug}" class="mt-2 inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-[11px] sm:text-xs font-semibold text-blue-600 transition-colors hover:border-blue-500 hover:bg-blue-500 hover:text-white">Detail</a>
                    </div>
                </div>
            </div>`).join('');
    }

    function applyFilters() {
        const products = getFilteredProducts();
        renderActiveChips();
        renderProducts(products);
    }

    function resetFilters() {
        document.querySelectorAll('.filter-cat, .filter-variant').forEach(el => el.checked = false);
        document.getElementById('priceMin').value = '';
        document.getElementById('priceMax').value = '';
        document.getElementById('filterPromo').checked = false;
        document.getElementById('filterStock').checked = false;
        document.getElementById('ratingMin').value = '0';
        document.getElementById('sortSel').value = 'relevant';
        selectedVariantFilters = {};
        document.querySelectorAll('.filter-variant-toggle').forEach((el) => {
            el.dataset.expanded = 'false';
        });
        document.querySelectorAll('.filter-variant-options').forEach((group) => updateVariantOptionVisibility(group.dataset.variantGroup || ''));
        document.querySelectorAll('.filter-variant-group-toggle').forEach((el) => setVariantGroupExpanded(el.dataset.variantGroup || '', false));
        applyFilters();
    }

    const mobileFilterDrawer = {
        closeTimer: null,
        isDragging: false,
        startY: 0,
        currentY: 0,
        initialized: false,
    };

    function getMobileFilterElements() {
        const sidebar = document.getElementById('filterSidebar');
        const panel = document.getElementById('filterPanel');
        const handle = document.getElementById('filterDrawerHandle');
        return { sidebar, panel, handle };
    }

    function syncMobileFilterDrawerMode() {
        const { sidebar, panel } = getMobileFilterElements();
        if (!sidebar || !panel) return;

        if (window.innerWidth < 1024) {
            sidebar.classList.add('mobile-filter-drawer');
            panel.classList.add('mobile-filter-panel');
            panel.classList.remove('rounded-2xl', 'sticky', 'top-20');
            if (!sidebar.classList.contains('mobile-filter-open')) {
                sidebar.classList.add('hidden');
            }
        } else {
            clearTimeout(mobileFilterDrawer.closeTimer);
            sidebar.classList.remove('hidden', 'mobile-filter-drawer', 'mobile-filter-open');
            panel.classList.remove('mobile-filter-panel');
            panel.style.transform = '';
            panel.style.transition = '';
            panel.classList.add('rounded-2xl', 'sticky', 'top-20');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function openMobileFilter() {
        const { sidebar, panel } = getMobileFilterElements();
        if (!sidebar || !panel || window.innerWidth >= 1024) return;

        syncMobileFilterDrawerMode();
        clearTimeout(mobileFilterDrawer.closeTimer);
        sidebar.classList.remove('hidden');
        panel.style.transform = '';
        panel.style.transition = '';
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            sidebar.classList.add('mobile-filter-open');
        });
    }

    function closeMobileFilter(immediate = false) {
        const sidebar = document.getElementById('filterSidebar');
        const panel = document.getElementById('filterPanel');
        if (!sidebar || !panel || window.innerWidth >= 1024) return;

        clearTimeout(mobileFilterDrawer.closeTimer);
        mobileFilterDrawer.isDragging = false;
        panel.style.transform = '';
        panel.style.transition = '';
        sidebar.classList.remove('mobile-filter-open');
        document.body.classList.remove('overflow-hidden');

        if (immediate) {
            sidebar.classList.add('hidden');
            return;
        }

        mobileFilterDrawer.closeTimer = setTimeout(() => {
            if (!sidebar.classList.contains('mobile-filter-open')) {
                sidebar.classList.add('hidden');
            }
        }, 320);
    }

    function initMobileFilterDrawer() {
        if (mobileFilterDrawer.initialized) return;

        const { panel, handle } = getMobileFilterElements();
        if (!panel || !handle) return;

        const getPointY = (event) => event.touches ? event.touches[0].clientY : event.clientY;

        const startDrag = (event) => {
            if (window.innerWidth >= 1024) return;
            if (!document.getElementById('filterSidebar')?.classList.contains('mobile-filter-open')) return;

            mobileFilterDrawer.isDragging = true;
            mobileFilterDrawer.startY = getPointY(event);
            mobileFilterDrawer.currentY = mobileFilterDrawer.startY;
            clearTimeout(mobileFilterDrawer.closeTimer);
            panel.style.transition = 'none';
        };

        const onDrag = (event) => {
            if (!mobileFilterDrawer.isDragging) return;

            mobileFilterDrawer.currentY = getPointY(event);
            const deltaY = Math.max(0, mobileFilterDrawer.currentY - mobileFilterDrawer.startY);

            if (deltaY > 0) {
                panel.style.transform = `translateY(${deltaY}px)`;
                if (event.cancelable) event.preventDefault();
            }
        };

        const endDrag = () => {
            if (!mobileFilterDrawer.isDragging) return;

            mobileFilterDrawer.isDragging = false;
            panel.style.transition = '';
            const deltaY = Math.max(0, mobileFilterDrawer.currentY - mobileFilterDrawer.startY);

            if (deltaY > 110) {
                closeMobileFilter();
                return;
            }

            panel.style.transform = '';
        };

        handle.addEventListener('touchstart', startDrag, { passive: true });
        window.addEventListener('touchmove', onDrag, { passive: false });
        window.addEventListener('touchend', endDrag);
        handle.addEventListener('mousedown', startDrag);
        window.addEventListener('mousemove', onDrag);
        window.addEventListener('mouseup', endDrag);
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeMobileFilter(true);
                syncMobileFilterDrawerMode();
            }
        });

        mobileFilterDrawer.initialized = true;
    }

    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('filterSidebar');
        if (sidebar && sidebar.classList.contains('mobile-filter-open') && e.target === sidebar) closeMobileFilter();
    });

    initMobileFilterDrawer();
    syncMobileFilterDrawerMode();
    renderCategoryFilters();
    renderVariantFilters();
    applyFilters();
</script>
@endsection
