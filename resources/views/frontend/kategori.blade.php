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
    <div id="allCategoriesSection" class="max-w-7xl mx-auto px-4 sm:px-6 pt-5 pb-4">
        {{-- <h2 class="text-xl font-bold text-slate-800 mb-6">Jelajahi Kategori</h2> --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4 mb-6">
            @foreach (($categoryTree ?? collect()) as $mainCategory)
                <button onclick="selectCategory('{{ $mainCategory->slug }}', '{{ $mainCategory->name }}')"
                    class="cat-card bg-white rounded-2xl p-6 flex flex-col items-center gap-3 shadow-sm border border-slate-100 hover:border-blue-300 hover:shadow-md transition-all group card-hover">
                    <div
                        class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center group-hover:bg-slate-100 group-hover:scale-110 transition-all shadow-sm border border-slate-200">
                        <i class="ri-folder-2-line text-2xl text-blue-600"></i>
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-blue-600 text-sm">{{ $mainCategory->name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $mainCategory->categoryDetails->count() }} kategori detail
                        </p>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- MAIN CONTENT: SIDEBAR + PRODUCTS -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-10">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- SIDEBAR -->
            <aside id="filterSidebar" class="hidden lg:block lg:w-64 flex-shrink-0">
                <div id="filterPanel"
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-20">
                    <div class="lg:hidden w-14 h-1 bg-slate-300 rounded-full mx-auto mt-3"></div>
                    <div class="p-4 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-slate-800">Filter</h3>
                            <button onclick="closeMobileFilter()"
                                class="lg:hidden text-xs text-slate-500 hover:text-slate-700 font-medium">Tutup</button>
                        </div>
                    </div>

                    <!-- Sub Kategori -->
                    <div class="p-4 border-b border-slate-100">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Sub Kategori</h4>
                        <ul class="space-y-1" id="subCatList">
                            <li><button
                                    class="sidebar-item active w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all"
                                    onclick="filterSubcat(this, 'semua')">Semua Produk</button></li>
                            <li><button
                                    class="sidebar-item w-full text-left px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all"
                                    onclick="filterSubcat(this, 'new')">Produk Baru</button></li>
                            <li><button
                                    class="sidebar-item w-full text-left px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all"
                                    onclick="filterSubcat(this, 'promo')">Sedang Promo</button></li>
                            <li><button
                                    class="sidebar-item w-full text-left px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all"
                                    onclick="filterSubcat(this, 'best')">Best Seller</button></li>
                        </ul>
                    </div>

                    <!-- Harga -->
                    <div class="p-4 border-b border-slate-100">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Harga</h4>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="price"
                                    class="accent-blue-500" onchange="setPriceRange(0,100000)" /> <span
                                    class="text-sm text-slate-600">
                                    < Rp 100.000</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="price"
                                    class="accent-blue-500" onchange="setPriceRange(100000,500000)" /> <span
                                    class="text-sm text-slate-600">Rp 100.000 – 500.000</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="price"
                                    class="accent-blue-500" onchange="setPriceRange(500000,1000000)" /> <span
                                    class="text-sm text-slate-600">Rp 500.000 – 1 Juta</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="price"
                                    class="accent-blue-500" onchange="setPriceRange(1000000,9999999)" /> <span
                                    class="text-sm text-slate-600">> Rp 1 Juta</span></label>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="p-4 border-b border-slate-100">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Rating</h4>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="rating"
                                    class="accent-blue-500" /> <span class="text-yellow-400">★★★★★</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="rating"
                                    class="accent-blue-500" /> <span class="text-yellow-400">★★★★</span><span
                                    class="text-slate-400 text-xs"> ke atas</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="rating"
                                    class="accent-blue-500" /> <span class="text-yellow-400">★★★</span><span
                                    class="text-slate-400 text-xs"> ke atas</span></label>
                        </div>
                    </div>

                    <!-- Warna -->
                    <div class="p-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Warna</h4>
                        <div class="flex flex-wrap gap-2">
                            <button
                                class="w-7 h-7 rounded-full bg-slate-900 hover:ring-2 hover:ring-slate-400 hover:ring-offset-1 transition-all"
                                title="Hitam"></button>
                            <button
                                class="w-7 h-7 rounded-full bg-white border border-slate-200 hover:ring-2 hover:ring-slate-400 hover:ring-offset-1 transition-all"
                                title="Putih"></button>
                            <button
                                class="w-7 h-7 rounded-full bg-red-500 hover:ring-2 hover:ring-red-400 hover:ring-offset-1 transition-all"
                                title="Merah"></button>
                            <button
                                class="w-7 h-7 rounded-full bg-blue-500 hover:ring-2 hover:ring-blue-400 hover:ring-offset-1 transition-all"
                                title="Biru"></button>
                            <button
                                class="w-7 h-7 rounded-full bg-blue-500 hover:ring-2 hover:ring-blue-400 hover:ring-offset-1 transition-all"
                                title="Hijau"></button>
                            <button
                                class="w-7 h-7 rounded-full bg-yellow-400 hover:ring-2 hover:ring-yellow-400 hover:ring-offset-1 transition-all"
                                title="Kuning"></button>
                            <button
                                class="w-7 h-7 rounded-full bg-pink-400 hover:ring-2 hover:ring-pink-400 hover:ring-offset-1 transition-all"
                                title="Pink"></button>
                            <button
                                class="w-7 h-7 rounded-full bg-purple-500 hover:ring-2 hover:ring-purple-400 hover:ring-offset-1 transition-all"
                                title="Ungu"></button>
                        </div>
                    </div>
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

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 py-8 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                    </svg>
                </div>
                <span class="text-white font-bold">Ecommerce Citra</span>
            </div>
            <p class="text-sm">© 2025 Ecommerce Citra. All rights reserved.</p>
            <div class="flex gap-4 text-sm">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-400">Beranda</a>
                <a href="{{ route('frontend.profil') }}" class="hover:text-blue-400">Profil</a>
                <a href="{{ route('frontend.checkout') }}" class="hover:text-blue-400">Checkout</a>
            </div>
        </div>
    </footer>
@endsection

@section('script')
    <script>
        const allProducts = @json($productsJson);
        const isAuthenticated = @json(auth()->check());
        const loginUrl = @json(route('login'));
        const cartStoreUrl = @json(route('frontend.cart.store'));
        const csrfToken = @json(csrf_token());

        let currentCat = 'semua';
        let currentSubcat = 'semua';
        let priceMin = 0,
            priceMax = 9999999;
        let viewMode = 'grid';
        let searchQuery = '';

        function getFiltered() {
            return allProducts.filter(p => {
                const catMatch = currentCat === 'semua' || p.parentCategorySlug === currentCat || p.categorySlug === currentCat;
                const subcatMatch = currentSubcat === 'semua' ||
                    (currentSubcat === 'new' && p.isNew) ||
                    (currentSubcat === 'promo' && p.isFlashSale) ||
                    (currentSubcat === 'best' && p.badge === 'best');
                const priceMatch = p.price >= priceMin && p.price <= priceMax;
                const searchMatch = !searchQuery || p.name.toLowerCase().includes(searchQuery.toLowerCase());
                return catMatch && subcatMatch && priceMatch && searchMatch;
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
                  <span class="text-yellow-400 text-xs">★</span>
                  <span class="text-xs font-medium text-slate-700">${p.rating}</span>
                  <span class="text-xs text-slate-400">(${p.reviews}) • ${p.sold.toLocaleString()} terjual</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <span class="font-bold text-slate-900 text-lg">Rp ${p.price.toLocaleString('id-ID')}</span>
                  ${p.origPrice > p.price ? `<span class="text-slate-400 text-sm line-through ml-2">Rp ${p.origPrice.toLocaleString('id-ID')}</span>` : ''}
                </div>
                <button onclick="addCart(${p.id})" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">+ Keranjang</button>
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
          </div>
          <div class="p-3">
            <a href="{{ url('/detail-produk') }}/${p.slug}" class="block text-sm font-semibold text-slate-800 hover:text-blue-600 transition-colors line-clamp-2 mb-1">${p.name}</a>
            <div class="flex items-center gap-1 mb-2">
              <span class="text-yellow-400 text-xs">★</span>
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

        function selectCategory(cat, label) {
            currentCat = cat;
            document.getElementById('breadcrumb-cat').textContent = label;
            document.getElementById('pageTitle').textContent = label;
            render(getFiltered());
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function filterSubcat(btn, sub) {
            currentSubcat = sub;
            document.querySelectorAll('.sidebar-item').forEach(b => {
                b.classList.remove('active');
                b.classList.add('text-slate-600');
            });
            btn.classList.add('active');
            btn.classList.remove('text-slate-600');
            render(getFiltered());
        }

        function setPriceRange(min, max) {
            priceMin = min;
            priceMax = max;
            render(getFiltered());
        }

        function sortProds() {
            const val = document.getElementById('sortSel').value;
            let prods = getFiltered();
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
            render(getFiltered());
        }

        function resetAll() {
            currentCat = 'semua';
            currentSubcat = 'semua';
            priceMin = 0;
            priceMax = 9999999;
            searchQuery = '';
            render(getFiltered());
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
                window.location.href = loginUrl;
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

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        render(allProducts);

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

