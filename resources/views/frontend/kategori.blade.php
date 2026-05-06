@extends('layouts.user')

@section('title', 'Kategori - Ecommerce Citra')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .badge-new {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .badge-promo {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .sidebar-item {
            transition: all 0.2s;
        }

        .sidebar-item.active {
            background: #eff6ff;
            color: #1d4ed8;
            border-right: 3px solid #2563eb;
        }

        .toast {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #2563eb;
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .search-dropdown {
            display: none;
        }

        .search-wrapper:focus-within .search-dropdown {
            display: block;
        }
    </style>
@endsection

@section('content')
    <!-- Toast -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
        <div class="flex items-center gap-3 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <span id="toast-msg">Berhasil!</span>
        </div>
    </div>

    <!-- NAVBAR -->
    @include('partials.navbar-user')

    <!-- BREADCRUMB -->
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span id="breadcrumb-cat" class="text-slate-800 font-medium">{{ $selectedLabel ?? 'Semua Kategori' }}</span>
            </nav>
        </div>
    </div>

    <!-- HERO KATEGORI -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h1 class="text-2xl md:text-3xl font-bold mb-2" id="pageTitle">{{ $selectedLabel ?? 'Semua Kategori' }}</h1>
            <p class="text-blue-100 text-sm">Temukan produk terbaik dari berbagai kategori pilihan</p>
            <!-- Search Mobile -->
            <form action="{{ route('frontend.search') }}" method="GET"
                class="mt-4 md:hidden flex items-center bg-white/15 border border-white/30 rounded-xl overflow-hidden backdrop-blur-sm">
                <input type="text" id="mobileSearchInput" name="q" placeholder="Cari produk..."
                    class="flex-1 px-4 py-2.5 text-sm outline-none bg-white/95 text-slate-800" />
                <button type="submit" class="w-14 h-full text-white flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- CATEGORY GRID (All Categories) -->
    <div id="allCategoriesSection" class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 pb-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-1 h-6 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></div>
                <h2 class="text-base font-bold text-slate-800">Semua Kategori</h2>
            </div>
            <span class="text-xs text-slate-400">{{ collect($categoryTree ?? [])->count() }} kategori tersedia</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
            @foreach (($categoryTree ?? collect()) as $mainCategory)
                @php
                    $mainCategoryImage = trim((string) ($mainCategory->image ?? ''));
                    $mainCategoryImageUrl =
                        $mainCategoryImage !== '' &&
                        (str_starts_with($mainCategoryImage, 'http://') ||
                            str_starts_with($mainCategoryImage, 'https://') ||
                            str_starts_with($mainCategoryImage, '//') ||
                            str_starts_with($mainCategoryImage, 'data:'))
                            ? $mainCategoryImage
                            : ($mainCategoryImage !== '' ? asset('storage/' . ltrim($mainCategoryImage, '/')) : '');
                    $subCount = $mainCategory->categoryDetails->count();
                @endphp
                <button onclick="selectCategory('{{ $mainCategory->slug }}', '{{ $mainCategory->name }}')"
                    class="cat-card group relative flex items-center gap-3 bg-white rounded-2xl px-4 py-3.5 border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 hover:bg-gradient-to-r hover:from-blue-50/60 hover:to-indigo-50/40 transition-all duration-300 text-left overflow-hidden">
                    <!-- Decorative corner -->
                    <div class="absolute top-0 right-0 w-12 h-12 bg-blue-100/50 rounded-bl-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

                    <!-- Image -->
                    <div class="relative shrink-0 w-14 h-14 rounded-xl overflow-hidden shadow-sm group-hover:shadow-md group-hover:scale-[1.06] transition-all duration-300">
                        @if ($mainCategoryImageUrl !== '')
                            <img src="{{ $mainCategoryImageUrl }}" alt="{{ $mainCategory->name }}"
                                class="w-full h-full object-cover" />
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                <i class="ri-folder-2-line text-2xl text-blue-500"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Text -->
                    <div class="flex-1 min-w-0 relative z-10">
                        <p class="text-sm font-bold text-slate-800 group-hover:text-blue-700 transition-colors truncate leading-tight">{{ $mainCategory->name }}</p>
                        <div class="flex items-center gap-1 mt-1">
                            <i class="ri-stack-line text-[11px] text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                            <span class="text-xs text-slate-400 group-hover:text-blue-400 transition-colors">
                                {{ $subCount }} sub kategori
                            </span>
                        </div>
                    </div>

                    <!-- Arrow -->
                    <i class="ri-arrow-right-s-line text-lg text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all duration-300 relative z-10 shrink-0"></i>
                </button>
            @endforeach
        </div>
    </div>

    <!-- MAIN CONTENT: SIDEBAR + PRODUCTS -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-10">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- SIDEBAR -->
            <aside id="filterSidebar" class="hidden lg:block lg:w-64 flex-shrink-0">
                <div id="filterPanel" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sticky top-20">
                    <div class="lg:hidden w-14 h-1 bg-slate-300 rounded-full mx-auto mb-3"></div>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-bold text-slate-800">Filter Produk</h3>
                        <div class="flex items-center gap-3">
                            <button onclick="resetFilter()"
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium">Reset</button>
                            <button onclick="closeMobileFilter()"
                                class="lg:hidden text-xs text-slate-500 hover:text-slate-700 font-medium">Tutup</button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Kategori</h4>
                        <div class="space-y-2" id="filterCategoryList"></div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Rentang Harga</h4>
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <label class="text-xs text-slate-500 mb-1 block">Min</label>
                                    <input type="number" id="priceMin" placeholder="0"
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400"
                                        onchange="applyFilter()" />
                                </div>
                                <div class="flex-1">
                                    <label class="text-xs text-slate-500 mb-1 block">Max</label>
                                    <input type="number" id="priceMax" placeholder="8"
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400"
                                        onchange="applyFilter()" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 cursor-pointer"><input type="radio"
                                        name="priceRange" class="accent-blue-500" onchange="setPriceRange(0, 100000)" />
                                    <span class="text-sm text-slate-600">Di bawah Rp 100.000</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="radio"
                                        name="priceRange" class="accent-blue-500"
                                        onchange="setPriceRange(100000, 500000)" /> <span
                                        class="text-sm text-slate-600">Rp 100.000 - Rp 500.000</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="radio"
                                        name="priceRange" class="accent-blue-500"
                                        onchange="setPriceRange(500000, 1000000)" /> <span
                                        class="text-sm text-slate-600">Rp 500.000 - Rp 1 Juta</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="radio"
                                        name="priceRange" class="accent-blue-500"
                                        onchange="setPriceRange(1000000, 9999999)" /> <span
                                        class="text-sm text-slate-600">Di atas Rp 1 Juta</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Warna</h4>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="toggleColor(this, 'hitam')"
                                class="color-btn w-8 h-8 rounded-full bg-slate-900 border-2 border-transparent hover:border-slate-400 transition-all"
                                title="Hitam"></button>
                            <button onclick="toggleColor(this, 'putih')"
                                class="color-btn w-8 h-8 rounded-full bg-white border-2 border-slate-200 hover:border-slate-400 transition-all"
                                title="Putih"></button>
                            <button onclick="toggleColor(this, 'merah')"
                                class="color-btn w-8 h-8 rounded-full bg-red-500 border-2 border-transparent hover:border-red-400 transition-all"
                                title="Merah"></button>
                            <button onclick="toggleColor(this, 'biru')"
                                class="color-btn w-8 h-8 rounded-full bg-blue-500 border-2 border-transparent hover:border-blue-400 transition-all"
                                title="Biru"></button>
                            <button onclick="toggleColor(this, 'hijau')"
                                class="color-btn w-8 h-8 rounded-full bg-blue-500 border-2 border-transparent hover:border-blue-400 transition-all"
                                title="Hijau"></button>
                            <button onclick="toggleColor(this, 'kuning')"
                                class="color-btn w-8 h-8 rounded-full bg-yellow-400 border-2 border-transparent hover:border-yellow-400 transition-all"
                                title="Kuning"></button>
                            <button onclick="toggleColor(this, 'ungu')"
                                class="color-btn w-8 h-8 rounded-full bg-purple-500 border-2 border-transparent hover:border-purple-400 transition-all"
                                title="Ungu"></button>
                            <button onclick="toggleColor(this, 'pink')"
                                class="color-btn w-8 h-8 rounded-full bg-pink-400 border-2 border-transparent hover:border-pink-400 transition-all"
                                title="Pink"></button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Rating</h4>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="rating"
                                    class="accent-blue-500" /><span class="flex gap-0.5">★★★★★</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="rating"
                                    class="accent-blue-500" /><span class="flex gap-0.5">★★★★ ke atas</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="rating"
                                    class="accent-blue-500" /><span class="flex gap-0.5">★★★ ke atas</span></label>
                        </div>
                    </div>

                    <button onclick="applyFilter()"
                        class="w-full mt-6 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 rounded-xl transition-colors text-sm">Terapkan
                        Filter</button>
                </div>
            </aside>
            <!-- PRODUCT AREA -->
            <main class="flex-1">
                <!-- Toolbar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-sm text-slate-500" id="resultCount">Menampilkan 16 produk</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex items-center gap-2 sm:hidden">
                            <button type="button" onclick="openMobileFilter()"
                                class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 flex items-center justify-center">
                                <i class="ri-filter-3-line text-base"></i>
                            </button>
                            <button type="button" onclick="cycleSortMobile()"
                                class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 flex items-center justify-center">
                                <i class="ri-arrow-up-down-line text-base"></i>
                            </button>
                        </div>
                        <select id="sortSel" onchange="sortProds()"
                            class="hidden sm:block border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-blue-400 bg-white">
                            <option value="newest">Terbaru</option>
                            <option value="cheap">Harga Termurah</option>
                            <option value="expensive">Harga Termahal</option>
                            <option value="rating">Rating Tertinggi</option>
                            <option value="sold">Terlaris</option>
                        </select>
                        <button id="gridViewBtn" onclick="toggleView('grid')"
                            class="hidden sm:block p-2 rounded-lg bg-blue-500 text-white">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button id="listViewBtn" onclick="toggleView('list')"
                            class="hidden sm:block p-2 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Products -->
                <div id="prodGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="hidden text-center py-20">
                    <div class="text-6xl mb-4">😕</div>
                    <h3 class="text-xl font-bold text-slate-700 mb-2">Produk tidak ditemukan</h3>
                    <p class="text-slate-500 mb-6">Coba ubah kata kunci atau filter pencarian</p>
                    <button onclick="resetAll()"
                        class="bg-blue-500 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-blue-600 transition-colors">Reset
                        Filter</button>
                </div>

                <!-- Pagination -->
                <div id="pagination" class="flex items-center justify-center gap-2 mt-10">
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:border-blue-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="w-9 h-9 rounded-lg bg-blue-500 text-white font-semibold text-sm">1</button>
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 text-sm hover:border-blue-300">2</button>
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 text-sm hover:border-blue-300">3</button>
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:border-blue-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </main>
        </div>
    </div>
@endsection

@section('script')
    @php
        $filterMainCategories = collect($categoryTree ?? [])
            ->map(function ($mainCategory) use ($productsJson) {
                $count = collect($productsJson ?? [])->where('parentCategorySlug', $mainCategory->slug)->count();
                return [
                    'slug' => $mainCategory->slug,
                    'name' => $mainCategory->name,
                    'count' => $count,
                ];
            })
            ->all();
    @endphp
    <script>
        const allProducts = @json($productsJson);
        const filterMainCategories = @json($filterMainCategories);
        const initialParentSlug = @json($selectedParentSlug ?? '');
        const kategoriBaseUrl = @json(route('frontend.kategori'));
        const isAuthenticated = @json(auth()->check());
        const loginUrl = @json(route('login'));
        const cartStoreUrl = @json(route('frontend.cart.store'));
        const wishlistToggleUrl = @json(route('frontend.wishlist.toggle'));
        const wishlistStatusUrl = @json(route('frontend.wishlist.status'));
        const csrfToken = @json(csrf_token());
        const wishedProductIds = new Set();

        let selectedColors = [];
        let filteredProducts = [...allProducts];
        let viewMode = 'grid';
        let searchQuery = '';

        function getLoginRedirectUrl() {
            return `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
        }

        function getFiltered() {
            const cats = Array.from(document.querySelectorAll('.filter-cat:checked')).map(c => c.value);
            const min = parseInt(document.getElementById('priceMin')?.value || '0', 10) || 0;
            const max = parseInt(document.getElementById('priceMax')?.value || '9999999', 10) || 9999999;
            return allProducts.filter((p) => {
                const catMatch = cats.length === 0 || cats.includes(p.parentCategorySlug);
                const colorMatch = selectedColors.length === 0 || selectedColors.some((c) =>
                    Array.isArray(p.colors) && p.colors.includes(c));
                const priceMatch = p.price >= min && p.price <= max;
                const searchMatch = !searchQuery || p.name.toLowerCase().includes(searchQuery.toLowerCase());
                return catMatch && colorMatch && priceMatch && searchMatch;
            });
        }

        function render(prods) {
            const grid = document.getElementById('prodGrid');
            const empty = document.getElementById('emptyState');
            const pagination = document.getElementById('pagination');
            document.getElementById('resultCount').textContent = `Menampilkan ${prods.length} produk`;

            if (prods.length === 0) {
                grid.innerHTML = '';
                empty.classList.remove('hidden');
                pagination.classList.add('hidden');
                return;
            }
            empty.classList.add('hidden');
            pagination.classList.remove('hidden');

            const gridCols = viewMode === 'grid' ? 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4' :
                'grid grid-cols-1 gap-4';
            grid.className = gridCols;

            grid.innerHTML = prods.map(p => {
                const disc = p.origPrice > p.price ? Math.round((1 - p.price / p.origPrice) * 100) : 0;
                const badge = p.isFlashSale ?
                    `<span class="badge-promo text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-${disc}%</span>` :
                    p.badge === 'new' ?
                    `<span class="badge-new text-white text-[10px] font-bold px-2 py-0.5 rounded-full">BARU</span>` :
                    p.badge === 'best' ?
                    `<span class="bg-blue-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">TERLARIS</span>` :
                    '';

                if (viewMode === 'list') {
                    return `<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex gap-4 p-4 card-hover">
            <a href="{{ url('/detail-produk') }}/${p.slug}" class="flex-shrink-0">
              <img src="${p.image}" alt="${p.name}" class="w-28 h-28 object-cover rounded-xl" />
            </a>
            <div class="flex-1 flex flex-col justify-between">
              <div>
                <div class="flex gap-2 mb-1">${badge}</div>
                <a href="{{ url('/detail-produk') }}/${p.slug}" class="font-semibold text-slate-800 hover:text-blue-600 transition-colors">${p.name}</a>
                <div class="flex items-center gap-1 mt-1">
                  <span class="text-yellow-400 text-xs">?</span>
                  <span class="text-xs font-medium text-slate-700">${p.rating}</span>
                  <span class="text-xs text-slate-400">(${p.reviews}) • ${p.sold.toLocaleString()} terjual</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <span class="font-bold text-slate-900 text-lg">Rp ${p.price.toLocaleString('id-ID')}</span>
                  ${p.origPrice > p.price ? `<span class="text-slate-400 text-sm line-through ml-2">Rp ${p.origPrice.toLocaleString('id-ID')}</span>` : ''}
                </div>
                <div class="flex items-center gap-2">
                  <button onclick="toggleWishlist(${p.id})" data-wishlist-btn data-product-id="${p.id}" class="w-9 h-9 rounded-full border border-slate-200 text-pink-500 flex items-center justify-center hover:bg-pink-50">
                    <svg class="w-4 h-4" fill="${p.isWishlisted ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                  </button>
                  <button onclick="addCart(${p.id})" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">+ Keranjang</button>
                </div>
              </div>
            </div>
          </div>`;
                }
                return `<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover group">
          <div class="relative overflow-hidden">
            <a href="{{ url('/detail-produk') }}/${p.slug}">
              <img src="${p.image}" alt="${p.name}" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
            </a>
            <div class="absolute top-2 left-2 flex gap-1">${badge}</div>
            <button onclick="addCart(${p.id})" class="absolute bottom-2 right-2 w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-blue-500 hover:text-white text-slate-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </button>
            <button onclick="toggleWishlist(${p.id})" data-wishlist-btn data-product-id="${p.id}" class="absolute top-2 right-2 w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-pink-50 text-pink-500">
              <svg class="w-4 h-4" fill="${p.isWishlisted ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </button>
          </div>
          <div class="p-3">
            <a href="{{ url('/detail-produk') }}/${p.slug}" class="block text-sm font-semibold text-slate-800 hover:text-blue-600 transition-colors line-clamp-2 mb-1">${p.name}</a>
            <div class="flex items-center gap-1 mb-2">
              <span class="text-yellow-400 text-xs">?</span>
              <span class="text-xs font-medium text-slate-700">${p.rating}</span>
              <span class="text-xs text-slate-400">(${p.reviews})</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-bold text-slate-900">Rp ${p.price.toLocaleString('id-ID')}</span>
              ${p.origPrice > p.price ? `<span class="text-slate-400 text-xs line-through">Rp ${p.origPrice.toLocaleString('id-ID')}</span>` : ''}
            </div>
          </div>
        </div>`;
            }).join('');
        }

        function renderFilterCategories() {
            const container = document.getElementById('filterCategoryList');
            if (!container) return;
            const allChecked = initialParentSlug === '';
            const items = filterMainCategories.map((cat) => {
                const checked = initialParentSlug !== '' ? cat.slug === initialParentSlug : true;
                return `<label class="flex items-center gap-2 cursor-pointer group"><input type="checkbox"
                                    class="filter-cat w-4 h-4 rounded accent-blue-500" value="${cat.slug}" ${checked ? 'checked' : ''}
                                    onchange="applyFilter()" /><span
                                    class="text-sm text-slate-600 group-hover:text-slate-800">${cat.name} (${cat.count})</span></label>`;
            }).join('');
            container.innerHTML = items;
        }

        function selectCategory(cat, label) {
            const target = cat === 'semua' ? kategoriBaseUrl : `${kategoriBaseUrl}?parent=${encodeURIComponent(cat)}`;
            window.location.href = target;
        }

        function setPriceRange(min, max) {
            document.getElementById('priceMin').value = min;
            document.getElementById('priceMax').value = max;
            applyFilter();
        }

        function toggleColor(btn, color) {
            const idx = selectedColors.indexOf(color);
            if (idx === -1) {
                selectedColors.push(color);
                btn.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
            } else {
                selectedColors.splice(idx, 1);
                btn.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
            }
            applyFilter();
        }

        function applyFilter() {
            filteredProducts = getFiltered();
            sortProds();
        }

        function resetFilter() {
            document.querySelectorAll('.filter-cat').forEach((el) => {
                el.checked = true;
            });
            document.getElementById('priceMin').value = '';
            document.getElementById('priceMax').value = '';
            document.querySelectorAll('input[name="priceRange"]').forEach((r) => r.checked = false);
            document.querySelectorAll('input[name="rating"]').forEach((r) => r.checked = false);
            selectedColors = [];
            document.querySelectorAll('.color-btn').forEach((b) => b.classList.remove('ring-2', 'ring-blue-500',
                'ring-offset-2'));
            filteredProducts = [...allProducts];
            sortProds();
        }

        function sortProds() {
            const val = document.getElementById('sortSel').value;
            let prods = [...filteredProducts];
            if (val === 'cheap') prods.sort((a, b) => a.price - b.price);
            else if (val === 'expensive') prods.sort((a, b) => b.price - a.price);
            else if (val === 'rating') prods.sort((a, b) => b.rating - a.rating);
            else if (val === 'sold') prods.sort((a, b) => b.sold - a.sold);
            else prods.sort((a, b) => b.id - a.id);
            render(prods);
        }

        function cycleSortMobile() {
            const select = document.getElementById('sortSel');
            if (!select) return;
            const order = ['newest', 'cheap', 'expensive', 'rating', 'sold'];
            const current = order.indexOf(select.value);
            select.value = order[(current + 1) % order.length];
            sortProds();
            const labels = {
                newest: 'Urut: Terbaru',
                cheap: 'Urut: Termurah',
                expensive: 'Urut: Termahal',
                rating: 'Urut: Rating Tertinggi',
                sold: 'Urut: Terlaris'
            };
            showToast(labels[select.value] || 'Urutan diubah');
        }

        function toggleView(mode) {
            viewMode = mode;
            if (mode === 'grid') {
                document.getElementById('gridViewBtn').className = 'hidden sm:block p-2 rounded-lg bg-blue-500 text-white';
                document.getElementById('listViewBtn').className =
                    'hidden sm:block p-2 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-600';
            } else {
                document.getElementById('listViewBtn').className = 'hidden sm:block p-2 rounded-lg bg-blue-500 text-white';
                document.getElementById('gridViewBtn').className =
                    'hidden sm:block p-2 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-600';
            }
            render(getFiltered());
        }

        function searchProducts() {
            const val = document.getElementById('mainSearch')?.value || document.getElementById('mobileSearchInput')
                ?.value || '';
            searchQuery = val;
            applyFilter();
        }

        function resetAll() {
            searchQuery = '';
            resetFilter();
        }

        function openMobileFilter() {
            const sidebar = document.getElementById('filterSidebar');
            const panel = document.getElementById('filterPanel');
            if (!sidebar || !panel || window.innerWidth >= 1024) return;
            sidebar.classList.remove('hidden');
            sidebar.classList.add('fixed', 'inset-0', 'z-[60]', 'bg-black/40', 'flex', 'items-end', 'p-0');
            panel.classList.remove('rounded-2xl', 'sticky', 'top-20');
            panel.classList.add('w-full', 'rounded-t-3xl', 'rounded-b-none', 'max-h-[85vh]', 'overflow-y-auto', 'border-0');
        }

        function closeMobileFilter() {
            const sidebar = document.getElementById('filterSidebar');
            const panel = document.getElementById('filterPanel');
            if (!sidebar || !panel || window.innerWidth >= 1024) return;
            sidebar.classList.add('hidden');
            sidebar.classList.remove('fixed', 'inset-0', 'z-[60]', 'bg-black/40', 'flex', 'items-end', 'p-0');
            panel.classList.add('rounded-2xl', 'sticky', 'top-20');
            panel.classList.remove('w-full', 'rounded-t-3xl', 'rounded-b-none', 'max-h-[85vh]', 'overflow-y-auto',
                'border-0');
        }

        async function addCart(id) {
            const p = allProducts.find(x => x.id === id);
            if (!p) return;
            if (!isAuthenticated) {
                window.location.href = getLoginRedirectUrl();
                return;
            }
            const res = await fetch(cartStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_variant_id: p.productVariantId,
                    quantity: 1,
                }),
            });
            if (!res.ok) return;
            showToast(`"${p.name}" ditambahkan ke keranjang!`);
            window.dispatchEvent(new Event('cart:updated'));
        }

        async function toggleWishlist(id) {
            if (!isAuthenticated) {
                window.location.href = getLoginRedirectUrl();
                return;
            }
            const p = allProducts.find((x) => x.id === id);
            if (!p) return;
            const res = await fetch(wishlistToggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_id: Number(id),
                }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                showToast('Gagal memproses wishlist');
                return;
            }
            if (json.wished) wishedProductIds.add(Number(id));
            else wishedProductIds.delete(Number(id));
            syncWishlistButtons();
            showToast(json.wished ? `"${p.name}" ditambahkan ke wishlist!` : `"${p.name}" dihapus dari wishlist!`);
            window.dispatchEvent(new Event('wishlist:updated'));
        }

        function syncWishlistButtons() {
            document.querySelectorAll('[data-wishlist-btn]').forEach((btn) => {
                const id = Number(btn.getAttribute('data-product-id') || 0);
                const icon = btn.querySelector('svg');
                if (!icon) return;
                icon.setAttribute('fill', wishedProductIds.has(id) ? 'currentColor' : 'none');
            });
        }

        async function initWishlistStatus() {
            if (!isAuthenticated) return;
            const ids = allProducts.map((p) => Number(p.id)).filter(Boolean);
            if (!ids.length) return;
            const res = await fetch(wishlistStatusUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_ids: ids,
                }),
            });
            const json = await res.json().catch(() => ({}));
            const wishedIds = Array.isArray(json.wished_product_ids) ? json.wished_product_ids : [];
            wishedIds.forEach((w) => wishedProductIds.add(Number(w)));
            syncWishlistButtons();
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        renderFilterCategories();
        applyFilter();
        initWishlistStatus();

        // Navbar mega dropdown
        function toggleCategoryMenu(event) {
            if (event) event.stopPropagation();
            const menu = document.getElementById('category-dropdown');
            if (!menu) return;
            menu.classList.toggle('hidden');
        }
        const megaCategoryData = {
            'rumah-tangga': [{
                    title: 'Dekorasi',
                    items: ['Hiasan Dinding', 'Jam Dinding', 'Lilin Aroma', 'Karpet Ruang']
                },
                {
                    title: 'Kamar Mandi',
                    items: ['Cermin Kamar Mandi', 'Dispenser Sabun', 'Rak Toilet', 'Handuk Mandi']
                },
                {
                    title: 'Kebutuhan Rumah',
                    items: ['Baterai', 'Humidifier', 'Payung', 'Termometer']
                },
                {
                    title: 'Tempat Penyimpanan',
                    items: ['Box Plastik', 'Keranjang', 'Rak Serbaguna', 'Lemari Kecil']
                }
            ],
            'fashion-pria': [{
                    title: 'Atasan',
                    items: ['Kemeja', 'Kaos', 'Polo Shirt', 'Hoodie']
                },
                {
                    title: 'Bawahan',
                    items: ['Celana Chino', 'Jeans', 'Celana Pendek', 'Jogger']
                },
                {
                    title: 'Aksesoris',
                    items: ['Topi', 'Ikat Pinggang', 'Dompet', 'Jam Tangan']
                },
                {
                    title: 'Sepatu Pria',
                    items: ['Sneakers', 'Pantofel', 'Boots', 'Sandal']
                }
            ],
            'fashion-wanita': [{
                    title: 'Atasan Wanita',
                    items: ['Blouse', 'Kemeja Wanita', 'Tunik', 'Crop Top']
                },
                {
                    title: 'Bawahan Wanita',
                    items: ['Rok', 'Jeans Wanita', 'Celana Kulot', 'Legging']
                },
                {
                    title: 'Dress',
                    items: ['Dress Kasual', 'Dress Formal', 'Maxi Dress', 'Midi Dress']
                },
                {
                    title: 'Aksesoris',
                    items: ['Tas Wanita', 'Perhiasan', 'Hijab', 'Sepatu Wanita']
                }
            ],
            'elektronik': [{
                    title: 'Komputer',
                    items: ['Laptop', 'PC Desktop', 'Monitor', 'Keyboard']
                },
                {
                    title: 'Gadget',
                    items: ['Smartphone', 'Tablet', 'Smartwatch', 'Earbuds']
                },
                {
                    title: 'Gaming',
                    items: ['Konsol', 'Gamepad', 'Mouse Gaming', 'Headset Gaming']
                },
                {
                    title: 'Aksesoris',
                    items: ['Power Bank', 'Charger', 'Kabel Data', 'Storage']
                }
            ],
            'kecantikan': [{
                    title: 'Perawatan Wajah',
                    items: ['Facial Wash', 'Toner', 'Serum', 'Moisturizer']
                },
                {
                    title: 'Makeup',
                    items: ['Lipstik', 'Foundation', 'Compact Powder', 'Maskara']
                },
                {
                    title: 'Perawatan Tubuh',
                    items: ['Body Lotion', 'Body Scrub', 'Sabun', 'Hand Cream']
                },
                {
                    title: 'Perawatan Rambut',
                    items: ['Shampoo', 'Conditioner', 'Hair Mask', 'Hair Tonic']
                }
            ],
            'olahraga': [{
                    title: 'Fitness',
                    items: ['Dumbbell', 'Resistance Band', 'Yoga Mat', 'Kettlebell']
                },
                {
                    title: 'Lari',
                    items: ['Sepatu Lari', 'Jaket Lari', 'Celana Lari', 'Botol Minum']
                },
                {
                    title: 'Sepak Bola',
                    items: ['Jersey', 'Sepatu Bola', 'Bola', 'Shin Guard']
                },
                {
                    title: 'Outdoor',
                    items: ['Tenda', 'Carrier', 'Sleeping Bag', 'Jaket Gunung']
                }
            ],
            'ibu-bayi': [{
                    title: 'Makanan Bayi',
                    items: ['Sereal Bayi', 'Puree', 'Snack Bayi', 'Susu Formula']
                },
                {
                    title: 'Perlengkapan Bayi',
                    items: ['Popok', 'Botol Susu', 'Stroller', 'Baby Carrier']
                },
                {
                    title: 'Perawatan Bayi',
                    items: ['Sabun Bayi', 'Minyak Telon', 'Lotion Bayi', 'Tisu Basah']
                },
                {
                    title: 'Ibu Menyusui',
                    items: ['Pompa ASI', 'Breast Pad', 'Cooler Bag', 'Bantal Menyusui']
                }
            ],
            'makanan-minuman': [{
                    title: 'Makanan Ringan',
                    items: ['Keripik', 'Biskuit', 'Cokelat', 'Kacang']
                },
                {
                    title: 'Minuman',
                    items: ['Kopi', 'Teh', 'Susu UHT', 'Minuman Isotonik']
                },
                {
                    title: 'Bahan Pokok',
                    items: ['Beras', 'Minyak Goreng', 'Gula', 'Tepung']
                },
                {
                    title: 'Makanan Instan',
                    items: ['Mie Instan', 'Sarden', 'Kornet', 'Frozen Food']
                }
            ]
        };

        function renderMegaCategoryContent(key) {
            const container = document.getElementById('category-mega-content');
            if (!container) return;
            const sections = megaCategoryData[key] || megaCategoryData['rumah-tangga'];
            container.innerHTML =
                `<div class="grid grid-cols-4 gap-6">${sections.map(s => `<div><h5 class="text-sm font-semibold text-slate-800 mb-3">${s.title}</h5><ul class="space-y-2">${s.items.map(i => `<li><a href="#" class="text-sm text-slate-600 hover:text-blue-600">${i}</a></li>`).join('')}</ul></div>`).join('')}</div>`;
        }

        function setMegaCategory(key) {
            document.querySelectorAll('.mega-cat-btn').forEach(b => {
                b.classList.remove('bg-blue-50', 'text-blue-700', 'font-semibold');
                b.classList.add('text-slate-700');
            });
            const a = document.querySelector(`.mega-cat-btn[data-cat-key="${key}"]`);
            if (a) {
                a.classList.add('bg-blue-50', 'text-blue-700', 'font-semibold');
                a.classList.remove('text-slate-700');
            }
            renderMegaCategoryContent(key);
        }
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('category-dropdown');
            const trigger = document.getElementById('category-trigger');
            if (menu && trigger && !menu.contains(e.target) && !trigger.contains(e.target)) menu.classList.add(
                'hidden');

            const sidebar = document.getElementById('filterSidebar');
            const panel = document.getElementById('filterPanel');
            if (sidebar && panel && window.innerWidth < 1024 && sidebar.classList.contains('fixed')) {
                if (e.target === sidebar) closeMobileFilter();
            }
        });

        function toggleMobileSearch() {
            document.getElementById('mobileSearch').classList.toggle('hidden');
        }
        setMegaCategory('rumah-tangga');
    </script>
@endsection




