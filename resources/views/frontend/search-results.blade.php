@extends('layouts.user')

@section('title', 'Hasil Pencarian - ' . ($appStoreName ?? config('app.name')))

@section('style')
    <style>
        * { font-family: 'Inter Variable', Inter, sans-serif; }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12); }
        .filter-chip { display:inline-flex; align-items:center; gap:8px; border-radius:999px; padding:8px 12px; font-size:12px; font-weight:600; background:var(--ec-primary-100); color:var(--ec-primary-700); }
        .filter-drawer-handle { width:40px; height:5px; background:#cbd5e1; border-radius:9999px; margin:0 auto 16px; cursor:grab; touch-action:none; }
        .filter-drawer-handle:active { cursor:grabbing; }
        .flat-filter-panel { background:transparent; border:0; border-radius:0; box-shadow:none; padding:0; }
        .flat-filter-title { padding-bottom:0.75rem; border-bottom:1px solid #e2e8f0; }
        .flat-filter-section { padding:1.125rem 0; border-bottom:1px solid #e2e8f0; }
        .flat-filter-panel input[type="checkbox"] { width:16px; height:16px; border-radius:2px; }
        @media (max-width: 1023px) {
            #filterSidebar.mobile-filter-drawer { position:fixed; inset:0; z-index:60; display:flex; align-items:flex-end; background:rgba(15, 23, 42, 0); opacity:0; transition:background 0.28s ease, opacity 0.28s ease; }
            #filterSidebar.mobile-filter-drawer:not(.mobile-filter-open) { pointer-events:none; }
            #filterSidebar.mobile-filter-drawer.mobile-filter-open { background:rgba(15, 23, 42, 0.4); opacity:1; pointer-events:auto; }
            #filterPanel.mobile-filter-panel { width:100%; max-height:85vh; overflow-y:auto; overscroll-behavior:contain; border:0; border-radius:24px 24px 0 0; background:#fff; padding:1.25rem; box-shadow:0 -16px 40px rgb(15 23 42 / 0.14); position:relative; top:auto; transform:translateY(calc(100% + 24px)); transition:transform 0.32s cubic-bezier(0.22, 1, 0.36, 1); will-change:transform; }
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

    <section class="ec-page-hero py-8">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6">
            <h1 class="text-2xl md:text-3xl font-bold mb-2">Hasil Pencarian</h1>
            <p id="searchMeta" class="ec-page-hero-copy text-sm"></p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div id="activeFilters" class="hidden flex flex-wrap gap-2 mb-4"></div>

        <div class="flex flex-col lg:flex-row gap-8">
            <aside id="filterSidebar" class="hidden lg:block lg:w-64 flex-shrink-0">
                <div id="filterPanel" class="flat-filter-panel sticky top-20 flex max-h-[calc(100vh-6rem)] flex-col">
                    <div id="filterDrawerHandle" class="filter-drawer-handle lg:hidden"></div>
                    <div class="flat-filter-title flex flex-shrink-0 items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-950">Filter</h3>
                        <div class="flex items-center gap-3">
                            <button onclick="resetFilters()" class="text-xs text-blue-600 font-medium">Reset</button>
                            <button onclick="closeMobileFilter()" class="lg:hidden text-xs text-slate-500 font-medium">Tutup</button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto pr-2">
                        <div class="flat-filter-section">
                        <button type="button" class="flex w-full items-center justify-between gap-3 text-left"
                            aria-expanded="true" aria-controls="searchCategoryPanel" onclick="toggleFilterSection(this, 'searchCategoryPanel')">
                            <span class="text-sm font-medium text-slate-950">Kategori</span>
                            <i class="ri-arrow-down-s-line rotate-180 text-lg text-slate-400 transition-transform"></i>
                        </button>
                        <div id="searchCategoryPanel" class="pt-3">
                            <div class="relative mb-3">
                                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="searchCategorySearch" type="search" placeholder="Cari kategori..."
                                    class="w-full rounded border border-slate-300 bg-transparent py-2 pl-8 pr-3 text-sm outline-none transition focus:border-blue-500"
                                    oninput="searchCategoryOptions(this, 'categoryFilterList', 'searchCategoryEmpty')">
                            </div>
                            <div id="categoryFilterList" class="space-y-3"></div>
                            <p id="searchCategoryEmpty" class="hidden py-2 text-xs text-slate-400">Kategori tidak ditemukan.</p>
                        </div>
                    </div>

                    <div class="flat-filter-section">
                        <button type="button" class="flex w-full items-center justify-between gap-3 text-left"
                            aria-expanded="true" aria-controls="searchPricePanel" onclick="toggleFilterSection(this, 'searchPricePanel')">
                            <span class="text-sm font-medium text-slate-950">Harga</span>
                            <i class="ri-arrow-down-s-line rotate-180 text-lg text-slate-400 transition-transform"></i>
                        </button>
                        <div id="searchPricePanel" class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 pt-3">
                            <input id="priceMin" type="number" min="0" placeholder="Min" oninput="applyFilters()" class="min-w-0 w-full rounded border border-slate-300 bg-transparent px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <span class="text-slate-400">-</span>
                            <input id="priceMax" type="number" min="0" placeholder="Max" oninput="applyFilters()" class="min-w-0 w-full rounded border border-slate-300 bg-transparent px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flat-filter-section">
                        <h4 class="mb-3 text-sm font-medium text-slate-950">Status Produk</h4>
                        <label class="mb-3 flex items-center gap-2 text-sm text-slate-700"><input id="filterPromo" type="checkbox" class="accent-blue-500" onchange="applyFilters()"> Hanya promo / flash sale</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input id="filterStock" type="checkbox" class="accent-blue-500" onchange="applyFilters()"> Hanya stok tersedia</label>
                    </div>

                    <div class="flat-filter-section">
                        <h4 class="mb-3 text-sm font-medium text-slate-950">Rating</h4>
                        <select id="ratingMin" onchange="applyFilters()" class="w-full rounded border border-slate-300 bg-transparent px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <option value="0">Semua rating</option>
                            <option value="4">4 ke atas</option>
                            <option value="4.5">4.5 ke atas</option>
                            <option value="5">5 saja</option>
                        </select>
                    </div>

                        <div id="variantFilterList"></div>
                    </div>
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

    function toggleFilterSection(button, panelId) {
        const panel = document.getElementById(panelId);
        if (!panel) return;

        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        panel.classList.toggle('hidden', expanded);
        button.querySelector('i')?.classList.toggle('rotate-180', !expanded);
    }

    function searchCategoryOptions(input, optionsId, emptyId) {
        const options = Array.from(document.querySelectorAll(`#${optionsId} .filter-category-option`));
        const searchValue = normalizeFilterValue(input.value);
        let visibleCount = 0;

        options.forEach((option) => {
            const visible = !searchValue || normalizeFilterValue(option.textContent).includes(searchValue);
            option.classList.toggle('hidden', !visible);
            if (visible) visibleCount++;
        });

        document.getElementById(emptyId)?.classList.toggle('hidden', visibleCount > 0);
    }

    function renderCategoryFilters() {
        const container = document.getElementById('categoryFilterList');
        container.innerHTML = searchMainCategories.map(cat => `
            <label class="filter-category-option flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" class="filter-cat accent-blue-500" value="${cat.slug}" onchange="applyFilters()">
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
            return `<div class="filter-variant-group flat-filter-section" data-variant-group="${encodeURIComponent(groupKey)}">
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
                            <input type="checkbox" class="filter-variant accent-blue-500" data-variant-name="${encodeURIComponent(groupKey)}" data-variant-value="${encodeURIComponent(key)}" onchange="updateVariantOptionVisibility(this.dataset.variantName || ''); openCheckedVariantGroups(); applyFilters();">
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

        grid.innerHTML = products.map((p) => {
            const productUrl = `{{ url('/detail-produk') }}/${encodeURIComponent(p.slug)}`;
            const variants = Array.isArray(p.variants) ? p.variants.map(v => v.value).filter(Boolean) : [];
            const variantLabel = p.variant || variants.slice(0, 2).join(' · ') || p.category || 'Produk industri';
            const discount = Number(p.originalPrice) > Number(p.price) ? Math.round((1 - Number(p.price) / Number(p.originalPrice)) * 100) : 0;
            return `
            <article class="store-product-card group">
                <div class="store-product-media">
                    <a href="${productUrl}" class="block h-full" aria-label="Lihat ${escapeHtml(p.name)}">
                        <img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name)}" loading="lazy" />
                    </a>
                    ${discount > 0 ? `<span class="absolute left-2.5 top-2.5 rounded-md bg-rose-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">-${discount}%</span>` : ''}
                </div>
                <div class="store-product-body">
                    <a href="${productUrl}" class="store-product-name line-clamp-2 hover:text-blue-700">${escapeHtml(p.name)}</a>
                    <p class="store-product-variant truncate">${escapeHtml(variantLabel)}</p>
                    <p class="store-product-price">Rp ${Number(p.price || 0).toLocaleString('id-ID')}</p>
                    <p class="store-product-seller"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h16v-9.5M3 4h18l-1.5 6a2.5 2.5 0 0 1-4.5 1.1 2.5 2.5 0 0 1-4.5 0A2.5 2.5 0 0 1 6 10L4.5 4M9 20v-5h6v5"/></svg><span>${escapeHtml(p.storeName || 'Mitra industri')}</span></p>
                    <div class="store-product-meta">
                        <span class="store-product-rating"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.7 2.83 5.73 6.32.92-4.58 4.46 1.08 6.3L12 17.14l-5.65 2.97 1.08-6.3-4.58-4.46 6.32-.92L12 2.7Z"/></svg>${Number(p.rating || 0).toFixed(1)} <span class="font-normal text-slate-400">(${Number(p.reviews || 0).toLocaleString('id-ID')})</span></span>
                        <span>Terjual ${Number(p.sold || 0).toLocaleString('id-ID')}</span>
                    </div>
                </div>
            </article>`;
        }).join('');
    }

    function applyFilters() {
        const products = getFilteredProducts();
        renderActiveChips();
        renderProducts(products);
    }

    function resetFilters() {
        document.querySelectorAll('.filter-cat, .filter-variant').forEach(el => el.checked = false);
        const categorySearch = document.getElementById('searchCategorySearch');
        if (categorySearch) {
            categorySearch.value = '';
            searchCategoryOptions(categorySearch, 'categoryFilterList', 'searchCategoryEmpty');
        }
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
