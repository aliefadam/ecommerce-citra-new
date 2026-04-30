@extends('layouts.user')

@section('title', 'Detail Produk - Ecommerce Citra')

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
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .thumb-active {
            border-color: #2563eb;
        }

        .variant-btn {
            transition: all 0.2s;
        }

        .variant-btn.active {
            border-color: #2563eb;
            color: #1d4ed8;
            background: #eff6ff;
            font-weight: 600;
        }

        .color-swatch.active {
            ring: 2px;
            outline: 2px solid #2563eb;
            outline-offset: 2px;
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

        .tab-btn.active {
            border-bottom: 2px solid #2563eb;
            color: #1d4ed8;
        }

        .review-bar {
            background: #e2e8f0;
            border-radius: 9999px;
            height: 6px;
            overflow: hidden;
        }

        .review-fill {
            background: linear-gradient(to right, #f59e0b, #fbbf24);
            height: 100%;
            border-radius: 9999px;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        .main-img {
            transition: opacity 0.2s ease;
        }

        .sticky-bottom {
            position: fixed;
            bottom: 64px;
            left: 0;
            right: 0;
            z-index: 40;
        }

        @media (min-width: 768px) {
            .sticky-bottom {
                bottom: 0;
            }
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
    <div id="toast" class="fixed top-4 right-4 z-[9999] hidden">
        <div class="toast bg-blue-500 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span id="toast-msg">Berhasil!</span>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="bg-white sticky top-0 z-50 shadow-sm border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('frontend.index') }}" class="flex items-center gap-2 flex-shrink-0">
                    <div
                        class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                            <path d="M16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                        </svg>
                    </div>
                    <span
                        class="text-lg sm:text-xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Ecommerce
                        Citra</span>
                </a>
                <div class="hidden md:flex flex-1 max-w-xl mx-6 relative items-center gap-2">
                    <button id="category-trigger" type="button" onclick="toggleCategoryMenu(event)"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap">
                        Kategori
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="search-wrapper relative flex-1">
                        <div
                            class="flex border border-slate-200 rounded-xl overflow-hidden focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                            <input type="text" placeholder="Cari produk, merek, kategori..."
                                class="flex-1 px-4 py-2.5 text-sm outline-none bg-white" />
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                        <div
                            class="search-dropdown absolute top-full left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2">
                            <p class="text-xs text-slate-400 px-3 py-1">Pencarian populer</p>
                            <a href="{{ route('frontend.detail-produk') }}"
                                class="block px-3 py-2 rounded-lg hover:bg-slate-50 text-sm text-slate-700">Sepatu
                                Sneakers</a>
                            <a href="{{ route('frontend.detail-produk') }}"
                                class="block px-3 py-2 rounded-lg hover:bg-slate-50 text-sm text-slate-700">Kemeja Pria</a>
                            <a href="{{ route('frontend.detail-produk') }}"
                                class="block px-3 py-2 rounded-lg hover:bg-slate-50 text-sm text-slate-700">Laptop
                                Gaming</a>
                        </div>
                    </div>
                    <div id="category-dropdown"
                        class="hidden absolute top-full left-0 mt-2 w-[820px] max-w-[88vw] bg-white rounded-2xl shadow-xl border border-slate-100 z-50">
                        <div class="grid grid-cols-5 min-h-[360px]">
                            <div class="col-span-1 border-r border-slate-100 p-4 overflow-y-auto">
                                <h4 class="text-sm font-semibold text-slate-800 mb-3">Semua Kategori</h4>
                                <div class="space-y-1">
                                    <button type="button" data-cat-key="rumah-tangga"
                                        onclick="setMegaCategory('rumah-tangga')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm bg-blue-50 text-blue-700 font-semibold">Rumah
                                        Tangga</button>
                                    <button type="button" data-cat-key="fashion-pria"
                                        onclick="setMegaCategory('fashion-pria')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Fashion
                                        Pria</button>
                                    <button type="button" data-cat-key="fashion-wanita"
                                        onclick="setMegaCategory('fashion-wanita')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Fashion
                                        Wanita</button>
                                    <button type="button" data-cat-key="elektronik" onclick="setMegaCategory('elektronik')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Elektronik</button>
                                    <button type="button" data-cat-key="kecantikan" onclick="setMegaCategory('kecantikan')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Kecantikan</button>
                                    <button type="button" data-cat-key="olahraga" onclick="setMegaCategory('olahraga')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Olahraga</button>
                                    <button type="button" data-cat-key="ibu-bayi" onclick="setMegaCategory('ibu-bayi')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Ibu
                                        & Bayi</button>
                                    <button type="button" data-cat-key="makanan-minuman"
                                        onclick="setMegaCategory('makanan-minuman')"
                                        class="mega-cat-btn w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Makanan
                                        & Minuman</button>
                                </div>
                                <a href="{{ route('frontend.kategori') }}"
                                    class="mt-4 inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium px-3 py-2">
                                    Lihat Semua <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                            <div id="category-mega-content" class="col-span-4 p-6 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                    <button class="md:hidden p-2 rounded-lg hover:bg-slate-100" onclick="toggleMobileSearch()">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    <button class="hidden sm:flex p-2 rounded-lg hover:bg-slate-100">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                    <a href="{{ route('frontend.checkout') }}" class="p-2 rounded-lg hover:bg-slate-100 relative">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span id="cartCount"
                            class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">3</span>
                    </a>
                    <a href="{{ route('frontend.profil') }}"
                        class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100">
                        <div
                            class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold">
                            A</div>
                        <span class="hidden sm:block text-sm font-medium text-slate-700">Andi</span>
                    </a>
                </div>
            </div>
        </div>
        <div id="mobileSearch" class="hidden md:hidden px-4 pb-3 border-t border-slate-100 pt-3">
            <div class="flex border border-slate-200 rounded-xl overflow-hidden focus-within:border-blue-400">
                <input type="text" placeholder="Cari produk..." class="flex-1 px-4 py-2.5 text-sm outline-none" />
                <button class="bg-blue-500 text-white px-4"><svg class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg></button>
            </div>
        </div>
    </nav>

    <!-- BREADCRUMB -->
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500 flex-wrap">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="{{ route('frontend.kategori') }}" class="hover:text-blue-600">Fashion Pria</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-800 font-medium">Kemeja Oxford Slim Fit Premium</span>
            </nav>
        </div>
    </div>

    <!-- MAIN PRODUCT SECTION -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 pb-28 md:pb-8">
        <div class="grid md:grid-cols-2 gap-10 lg:gap-16">

            <!-- LEFT: Gallery -->
            <div>
                <!-- Main Image -->
                <div class="relative rounded-2xl overflow-hidden bg-white shadow-sm border border-slate-100 mb-4">
                    <img id="mainImg"
                        src="https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=700&h=700&fit=crop"
                        alt="Produk" class="w-full h-80 md:h-[450px] object-cover main-img" />
                    <div class="absolute top-3 left-3 flex gap-2">
                        <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">-30%</span>
                        <span class="bg-blue-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">BEST SELLER</span>
                    </div>
                    <button onclick="toggleWishlist()" id="wishBtn"
                        class="absolute top-3 right-3 w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-pink-50 transition-colors">
                        <svg id="wishIcon" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                    <button
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/80 rounded-full shadow flex items-center justify-center hover:bg-white"
                        onclick="prevImg()">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/80 rounded-full shadow flex items-center justify-center hover:bg-white"
                        onclick="nextImg()">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <!-- Thumbnails -->
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <button onclick="setImg(0)"
                        class="thumb-btn flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 thumb-active">
                        <img src="https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=150&h=150&fit=crop"
                            class="w-full h-full object-cover" />
                    </button>
                    <button onclick="setImg(1)"
                        class="thumb-btn flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-slate-200">
                        <img src="https://images.unsplash.com/photo-1603251579431-8041402bdeda?w=150&h=150&fit=crop"
                            class="w-full h-full object-cover" />
                    </button>
                    <button onclick="setImg(2)"
                        class="thumb-btn flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-slate-200">
                        <img src="https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=150&h=150&fit=crop"
                            class="w-full h-full object-cover" />
                    </button>
                    <button onclick="setImg(3)"
                        class="thumb-btn flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-slate-200">
                        <img src="https://images.unsplash.com/photo-1571945153237-4929e783af4a?w=150&h=150&fit=crop"
                            class="w-full h-full object-cover" />
                    </button>
                    <button onclick="setImg(4)"
                        class="thumb-btn flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-slate-200">
                        <img src="https://images.unsplash.com/photo-1598522325074-042db73aa4e6?w=150&h=150&fit=crop"
                            class="w-full h-full object-cover" />
                    </button>
                </div>
            </div>

            <!-- RIGHT: Product Info -->
            <div>
                <!-- Brand & Status -->
                <div class="flex items-center justify-between mb-2">
                    <span class="text-blue-600 font-semibold text-xs sm:text-sm bg-blue-50 px-2.5 py-1 rounded-full">Kemeja &
                        Atasan</span>
                    <div class="flex items-center gap-2">
                        <span class="text-blue-600 text-xs sm:text-sm font-medium flex items-center gap-1">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span> Stok Tersedia
                        </span>
                        <button onclick="shareProduct()" title="Bagikan produk"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 mb-3 leading-tight">Kemeja Oxford Slim Fit Premium</h1>

                <!-- Rating & Sales -->
                <div class="flex items-center gap-2 sm:gap-4 mb-4 flex-wrap">
                    <div class="flex items-center gap-1">
                        <span class="text-yellow-400 text-sm sm:text-base">★★★★★</span>
                        <span class="font-bold text-slate-800 text-sm">4.8</span>
                        <span class="text-slate-500 text-xs sm:text-sm">(234 ulasan)</span>
                    </div>
                    <span class="text-slate-300 hidden xs:inline">|</span>
                    <span class="text-slate-600 text-xs sm:text-sm">1.245 terjual</span>
                    <span class="text-slate-300 hidden xs:inline">|</span>
                    <span class="text-slate-600 text-xs sm:text-sm">Wishlist: 456</span>
                </div>

                <!-- Price -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-3 sm:p-4 mb-5">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-2xl sm:text-3xl font-extrabold text-blue-600">Rp 189.000</span>
                        <span class="text-sm sm:text-base text-slate-400 line-through">Rp 270.000</span>
                        <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-md">Hemat 30%</span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-0.5 rounded">Flash Sale</span>
                        <span class="text-xs text-slate-600">Berakhir dalam:</span>
                        <span class="font-mono font-bold text-red-600 text-sm" id="saleTimer">04:23:17</span>
                    </div>
                </div>

                <!-- Color Variants -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">Warna: <span id="selectedColor"
                                class="text-blue-600 font-bold">Biru Navy</span></span>
                    </div>
                    <div class="flex gap-3 flex-wrap">
                        <button onclick="selectColor(this, 'Biru Navy')"
                            class="color-swatch w-10 h-10 rounded-full bg-blue-800 outline outline-2 outline-blue-500 outline-offset-2 hover:scale-110 transition-transform"
                            title="Biru Navy"></button>
                        <button onclick="selectColor(this, 'Putih Bersih')"
                            class="color-swatch w-10 h-10 rounded-full bg-white border-2 border-slate-200 hover:scale-110 transition-transform"
                            title="Putih"></button>
                        <button onclick="selectColor(this, 'Abu-abu')"
                            class="color-swatch w-10 h-10 rounded-full bg-slate-400 hover:scale-110 transition-transform"
                            title="Abu"></button>
                        <button onclick="selectColor(this, 'Hitam')"
                            class="color-swatch w-10 h-10 rounded-full bg-slate-900 hover:scale-110 transition-transform"
                            title="Hitam"></button>
                        <button onclick="selectColor(this, 'Hijau Sage')"
                            class="color-swatch w-10 h-10 rounded-full bg-blue-700 hover:scale-110 transition-transform"
                            title="Hijau"></button>
                    </div>
                </div>

                <!-- Size Variants -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-700">Ukuran: <span id="selectedSize"
                                class="text-blue-600 font-bold">M</span></span>
                        <button class="text-blue-600 text-xs hover:underline" onclick="showSizeGuide()">Panduan Ukuran
                            📏</button>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button onclick="selectSize(this, 'XS')"
                            class="variant-btn border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:border-blue-300 transition-all">XS</button>
                        <button onclick="selectSize(this, 'S')"
                            class="variant-btn border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:border-blue-300 transition-all">S</button>
                        <button onclick="selectSize(this, 'M')"
                            class="variant-btn active border-2 border-blue-400 rounded-xl px-4 py-2 text-sm">M</button>
                        <button onclick="selectSize(this, 'L')"
                            class="variant-btn border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:border-blue-300 transition-all">L</button>
                        <button onclick="selectSize(this, 'XL')"
                            class="variant-btn border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:border-blue-300 transition-all">XL</button>
                        <button onclick="selectSize(this, 'XXL')"
                            class="variant-btn border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:border-blue-300 transition-all">XXL</button>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-6">
                    <span class="text-sm font-semibold text-slate-700 block mb-2">Jumlah</span>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden">
                            <button onclick="changeQty(-1)"
                                class="px-4 py-2.5 text-slate-600 hover:bg-slate-50 font-bold text-lg transition-colors">−</button>
                            <span id="qtyDisplay"
                                class="px-5 py-2.5 font-bold text-slate-800 min-w-[50px] text-center border-x-2 border-slate-200">1</span>
                            <button onclick="changeQty(1)"
                                class="px-4 py-2.5 text-slate-600 hover:bg-slate-50 font-bold text-lg transition-colors">+</button>
                        </div>
                        <span class="text-sm text-slate-500">Stok: <span class="text-slate-700 font-semibold">87
                                item</span></span>
                    </div>
                </div>

                <!-- Action Buttons (Desktop) -->
                <div class="hidden md:flex gap-3 mb-6">
                    <button onclick="addToCart()"
                        class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-3.5 rounded-2xl border-2 border-blue-200 hover:border-blue-400 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Tambah ke Keranjang
                    </button>
                    <a href="{{ route('frontend.checkout') }}" onclick="buyNow()"
                        class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-3.5 rounded-2xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-200 hover:shadow-blue-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Beli Sekarang
                    </a>
                </div>
            </div>
        </div>

        <!-- TABS: Deskripsi, Ulasan, Info Pengiriman -->
        <div class="mt-10">
            <div class="flex border-b border-slate-200 mb-6 gap-4 sm:gap-8 overflow-x-auto">
                <button onclick="switchTab('desc')" id="tab-desc"
                    class="tab-btn active pb-3 text-sm font-semibold text-blue-600 whitespace-nowrap border-b-2 border-blue-500">Deskripsi</button>
                <button onclick="switchTab('review')" id="tab-review"
                    class="tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap">Ulasan
                    (234)</button>
                <button onclick="switchTab('info')" id="tab-info"
                    class="tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap">Info
                    Pengiriman</button>
                <button onclick="switchTab('size')" id="tab-size"
                    class="tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap">Tabel
                    Ukuran</button>
            </div>

            <!-- Deskripsi -->
            <div id="content-desc" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-4 text-lg">Tentang Produk</h3>
                <div class="prose text-slate-600 text-sm leading-relaxed space-y-3">
                    <p>Kemeja Oxford Slim Fit Premium adalah kemeja pria berkualitas tinggi yang dirancang dengan potongan
                        slim fit modern. Cocok untuk tampilan kasual maupun semi-formal.</p>
                    <p><strong class="text-slate-800">Bahan:</strong> 100% Cotton Oxford 120s yang lembut dan breathable,
                        nyaman dipakai sepanjang hari bahkan di cuaca panas sekalipun.</p>
                    <p><strong class="text-slate-800">Detail Produk:</strong></p>
                    <ul class="space-y-1 list-none">
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Kerah button-down klasik</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Kancing mother of pearl berkualitas tinggi</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Jahitan presisi double-stitched</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Saku dada kiri fungsional</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                            Label bahan anti-gatal</li>
                    </ul>
                    <p><strong class="text-slate-800">Cara Perawatan:</strong> Cuci dengan mesin pada suhu 30°C. Jangan
                        diputar. Setrika pada suhu sedang.</p>
                </div>
                <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">Bahan</p>
                        <p class="font-semibold text-slate-800 text-sm">100% Cotton</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">Fit</p>
                        <p class="font-semibold text-slate-800 text-sm">Slim Fit</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">Panjang Lengan</p>
                        <p class="font-semibold text-slate-800 text-sm">Panjang</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">Motif</p>
                        <p class="font-semibold text-slate-800 text-sm">Polos</p>
                    </div>
                </div>
            </div>

            <!-- Review -->
            <div id="content-review" class="hidden bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    <div class="text-center">
                        <div class="text-6xl font-extrabold text-slate-800 mb-1">4.8</div>
                        <div class="text-yellow-400 text-2xl mb-2">★★★★★</div>
                        <p class="text-slate-500 text-sm">dari 234 ulasan</p>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-600 w-8">5 ★</span>
                            <div class="flex-1 review-bar">
                                <div class="review-fill" style="width:82%"></div>
                            </div>
                            <span class="text-xs text-slate-500 w-8">82%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-600 w-8">4 ★</span>
                            <div class="flex-1 review-bar">
                                <div class="review-fill" style="width:12%"></div>
                            </div>
                            <span class="text-xs text-slate-500 w-8">12%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-600 w-8">3 ★</span>
                            <div class="flex-1 review-bar">
                                <div class="review-fill" style="width:4%"></div>
                            </div>
                            <span class="text-xs text-slate-500 w-8">4%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-600 w-8">2 ★</span>
                            <div class="flex-1 review-bar">
                                <div class="review-fill" style="width:1%"></div>
                            </div>
                            <span class="text-xs text-slate-500 w-8">1%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-600 w-8">1 ★</span>
                            <div class="flex-1 review-bar">
                                <div class="review-fill" style="width:1%"></div>
                            </div>
                            <span class="text-xs text-slate-500 w-8">1%</span>
                        </div>
                    </div>
                </div>
                <div class="space-y-5">
                    <div class="border-b border-slate-100 pb-5" id="reviews-container">
                    </div>
                </div>
            </div>

            <!-- Info Pengiriman -->
            <div id="content-info" class="hidden bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-6">Informasi Pengiriman</h3>
                <div class="space-y-4">
                    <div class="flex gap-4 p-4 bg-slate-50 rounded-xl">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">🚚
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 mb-0.5">Gratis Ongkir</p>
                            <p class="text-sm text-slate-600">Gratis ongkos kirim ke seluruh Indonesia untuk pembelian min.
                                Rp 100.000</p>
                        </div>
                    </div>
                    <div class="flex gap-4 p-4 bg-slate-50 rounded-xl">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">⚡
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 mb-0.5">Pengiriman Same Day</p>
                            <p class="text-sm text-slate-600">Tersedia untuk area Jabodetabek, pesan sebelum jam 12.00 WIB
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-4 p-4 bg-slate-50 rounded-xl">
                        <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">📦
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 mb-0.5">Estimasi Tiba</p>
                            <p class="text-sm text-slate-600">2-3 hari kerja (Reguler) | 1 hari kerja (Ekspres) | Same Day
                                (Instan)</p>
                        </div>
                    </div>
                    <div class="flex gap-4 p-4 bg-slate-50 rounded-xl">
                        <div class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center flex-shrink-0">🔄
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 mb-0.5">Kebijakan Retur</p>
                            <p class="text-sm text-slate-600">Retur mudah dalam 30 hari. Produk harus dalam kondisi asli
                                dan belum dipakai.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Size Guide -->
            <div id="content-size" class="hidden bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-4">Tabel Ukuran</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700 rounded-tl-xl">Ukuran</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Lingkar Dada (cm)</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Lebar Bahu (cm)</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 rounded-tr-xl">Panjang (cm)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">XS</td>
                                <td class="px-4 py-3 text-slate-600">84-88</td>
                                <td class="px-4 py-3 text-slate-600">40</td>
                                <td class="px-4 py-3 text-slate-600">68</td>
                            </tr>
                            <tr class="bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">S</td>
                                <td class="px-4 py-3 text-slate-600">88-92</td>
                                <td class="px-4 py-3 text-slate-600">42</td>
                                <td class="px-4 py-3 text-slate-600">70</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-blue-600 bg-blue-50">M ✓</td>
                                <td class="px-4 py-3 text-slate-600 bg-blue-50">92-96</td>
                                <td class="px-4 py-3 text-slate-600 bg-blue-50">44</td>
                                <td class="px-4 py-3 text-slate-600 bg-blue-50">72</td>
                            </tr>
                            <tr class="bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">L</td>
                                <td class="px-4 py-3 text-slate-600">96-100</td>
                                <td class="px-4 py-3 text-slate-600">46</td>
                                <td class="px-4 py-3 text-slate-600">74</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">XL</td>
                                <td class="px-4 py-3 text-slate-600">100-108</td>
                                <td class="px-4 py-3 text-slate-600">48</td>
                                <td class="px-4 py-3 text-slate-600">76</td>
                            </tr>
                            <tr class="bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">XXL</td>
                                <td class="px-4 py-3 text-slate-600">108-116</td>
                                <td class="px-4 py-3 text-slate-600">50</td>
                                <td class="px-4 py-3 text-slate-600">78</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-slate-500 mt-3">* Ukuran dapat bervariasi ±1-2 cm</p>
            </div>
        </div>

        <!-- PRODUK REKOMENDASI -->
        <div class="mt-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-800">Produk Rekomendasi</h2>
                <a href="{{ route('frontend.kategori') }}"
                    class="text-blue-600 text-sm font-medium hover:text-blue-700 flex items-center gap-1">
                    Lihat Semua <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="recGrid"></div>
        </div>
    </div>

    <!-- STICKY BOTTOM BAR (Mobile) -->
    <div class="sticky bottom-[55px] md:hidden bg-white border-t border-slate-200 px-3 py-2.5 flex gap-2">
        <button onclick="shareProduct()"
            class="w-10 h-10 shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
        </button>
        <button onclick="addToCart()"
            class="flex-1 bg-blue-50 border-2 border-blue-400 text-blue-700 font-bold py-2.5 rounded-xl text-sm flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Keranjang
        </button>
        <a href="{{ route('frontend.checkout') }}"
            class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold py-2.5 rounded-xl text-sm flex items-center justify-center gap-1.5 shadow-md shadow-blue-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Beli Sekarang
        </a>
    </div>

    <!-- Size Guide Modal -->
    <div id="sizeModal" class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-lg">Panduan Ukuran</h3>
                <button onclick="closeSizeGuide()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <p class="text-sm text-slate-600 mb-4">Ukur lingkar dada Anda pada bagian terlebar, lalu pilih ukuran yang
                sesuai.</p>
            <div class="bg-blue-50 rounded-xl p-4 text-sm text-blue-700 font-medium">💡 Tips: Jika ragu antara dua ukuran,
                pilih ukuran yang lebih besar untuk kenyamanan maksimal.</div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const images = [
            "https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=700&h=700&fit=crop",
            "https://images.unsplash.com/photo-1603251579431-8041402bdeda?w=700&h=700&fit=crop",
            "https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=700&h=700&fit=crop",
            "https://images.unsplash.com/photo-1571945153237-4929e783af4a?w=700&h=700&fit=crop",
            "https://images.unsplash.com/photo-1598522325074-042db73aa4e6?w=700&h=700&fit=crop"
        ];
        let currentImg = 0;
        let qty = 1;
        let isWishlisted = false;

        function setImg(i) {
            currentImg = i;
            const img = document.getElementById('mainImg');
            img.style.opacity = 0;
            setTimeout(() => {
                img.src = images[i];
                img.style.opacity = 1;
            }, 150);
            document.querySelectorAll('.thumb-btn').forEach((b, idx) => {
                b.className = b.className.replace('thumb-active border-blue-400', '').replace('border-slate-200',
                    '');
                b.classList.add(idx === i ? 'thumb-active border-blue-400' : 'border-slate-200');
                b.className = b.className.replace('border-2  border-2', 'border-2');
            });
        }

        function prevImg() {
            setImg((currentImg - 1 + images.length) % images.length);
        }

        function nextImg() {
            setImg((currentImg + 1) % images.length);
        }

        function changeQty(d) {
            qty = Math.max(1, Math.min(87, qty + d));
            document.getElementById('qtyDisplay').textContent = qty;
        }

        function selectColor(btn, color) {
            document.querySelectorAll('.color-swatch').forEach(b => b.style.outline = 'none');
            btn.style.outline = '2px solid #2563eb';
            btn.style.outlineOffset = '2px';
            document.getElementById('selectedColor').textContent = color;
        }

        function selectSize(btn, size) {
            document.querySelectorAll('.variant-btn').forEach(b => {
                b.classList.remove('active', 'border-blue-400');
                b.classList.add('border-slate-200', 'text-slate-600');
            });
            btn.classList.add('active', 'border-blue-400');
            btn.classList.remove('border-slate-200', 'text-slate-600');
            document.getElementById('selectedSize').textContent = size;
        }

        function toggleWishlist() {
            isWishlisted = !isWishlisted;
            const icon = document.getElementById('wishIcon');
            if (isWishlisted) {
                icon.setAttribute('fill', '#ec4899');
                icon.setAttribute('stroke', '#ec4899');
                showToast('Ditambahkan ke wishlist! ❤️');
            } else {
                icon.setAttribute('fill', 'none');
                icon.setAttribute('stroke', 'currentColor');
            }
        }

        function addToCart() {
            const color = document.getElementById('selectedColor').textContent;
            const size = document.getElementById('selectedSize').textContent;
            showToast(`Kemeja ${color} ukuran ${size} (${qty} item) ditambahkan ke keranjang!`);
            const badge = document.getElementById('cartCount');
            badge.textContent = parseInt(badge.textContent) + qty;
        }

        function buyNow() {
            const color = document.getElementById('selectedColor').textContent;
            const size = document.getElementById('selectedSize').textContent;
            localStorage.setItem('lastProduct', JSON.stringify({
                name: 'Kemeja Oxford Slim Fit Premium',
                color,
                size,
                qty,
                price: 189000
            }));
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function switchTab(tab) {
            ['desc', 'review', 'info', 'size'].forEach(t => {
                document.getElementById('tab-' + t).className =
                    'tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap';
                document.getElementById('content-' + t).classList.add('hidden');
            });
            document.getElementById('tab-' + tab).className =
                'tab-btn active pb-3 text-sm font-semibold text-blue-600 whitespace-nowrap border-b-2 border-blue-500';
            document.getElementById('content-' + tab).classList.remove('hidden');
        }

        function showSizeGuide() {
            const modal = document.getElementById('sizeModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeSizeGuide() {
            const modal = document.getElementById('sizeModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Reviews
        const reviews = [{
                name: "Budi Santoso",
                rating: 5,
                date: "15 Jan 2025",
                color: "Biru Navy",
                size: "L",
                text: "Kemeja ini kualitasnya luar biasa! Bahan terasa adem dan nyaman sepanjang hari. Jahitannya rapi dan ukurannya pas sesuai chart. Highly recommended!",
                avatar: "B",
                verified: true,
                helpful: 24
            },
            {
                name: "Ahmad Rizki",
                rating: 5,
                date: "10 Jan 2025",
                color: "Putih",
                size: "M",
                text: "Sudah beli ke-3 kalinya nih. Emang gak kecewa. Bahan adem, jahitan kuat, dan warnanya gak cepet pudar meski sering dicuci.",
                avatar: "A",
                verified: true,
                helpful: 18
            },
            {
                name: "Denny Wijaya",
                rating: 4,
                date: "5 Jan 2025",
                color: "Hitam",
                size: "XL",
                text: "Barang sesuai deskripsi. Pengiriman cepat dan packaging aman. Hanya saja kancingnya agak keras di awal, tapi sudah mulai longgar setelah beberapa kali pakai.",
                avatar: "D",
                verified: true,
                helpful: 12
            },
        ];

        const reviewColors = ['bg-blue-400', 'bg-blue-400', 'bg-orange-400', 'bg-purple-400'];
        document.getElementById('reviews-container').innerHTML = reviews.map((r, i) => `
      <div class="${i > 0 ? 'pt-5 border-t border-slate-100 mt-5' : ''}">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-full ${reviewColors[i % reviewColors.length]} flex items-center justify-center text-white font-bold flex-shrink-0">${r.avatar}</div>
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1 flex-wrap">
              <p class="font-semibold text-slate-800 text-sm">${r.name}</p>
              ${r.verified ? '<span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">✓ Terverifikasi</span>' : ''}
              <span class="text-xs text-slate-400 ml-auto">${r.date}</span>
            </div>
            <div class="text-yellow-400 text-sm mb-1">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
            <div class="flex gap-2 mb-2">
              <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">Warna: ${r.color}</span>
              <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">Ukuran: ${r.size}</span>
            </div>
            <p class="text-sm text-slate-600 leading-relaxed">${r.text}</p>
            <button class="mt-2 text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
              Membantu (${r.helpful})
            </button>
          </div>
        </div>
      </div>`).join('');

        // Rekomendasi
        const recs = [{
                name: "Polo Shirt Premium",
                price: 159000,
                image: "https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=300&h=300&fit=crop",
                rating: 4.7
            },
            {
                name: "Celana Chino Modern",
                price: 229000,
                image: "https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=300&h=300&fit=crop",
                rating: 4.6
            },
            {
                name: "Hoodie Oversized",
                price: 299000,
                image: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=300&h=300&fit=crop",
                rating: 4.8
            },
            {
                name: "T-Shirt Graphic Art",
                price: 129000,
                image: "https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=300&h=300&fit=crop",
                rating: 4.5
            },
            {
                name: "Jaket Bomber Casual",
                price: 459000,
                image: "https://images.unsplash.com/photo-1551028719-00167b16eac5?w=300&h=300&fit=crop",
                rating: 4.9
            },
        ];
        document.getElementById('recGrid').innerHTML = recs.map(r => `
      <a href="{{ route('frontend.detail-produk') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover group block">
        <div class="overflow-hidden">
          <img src="${r.image}" alt="${r.name}" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
        </div>
        <div class="p-3">
          <p class="text-xs font-semibold text-slate-800 line-clamp-2 mb-1">${r.name}</p>
          <div class="flex items-center gap-1 mb-1.5">
            <span class="text-yellow-400 text-xs">★</span>
            <span class="text-xs text-slate-600">${r.rating}</span>
          </div>
          <p class="font-bold text-slate-900 text-sm">Rp ${r.price.toLocaleString('id-ID')}</p>
        </div>
      </a>`).join('');

        // Sale Timer
        function updateSaleTimer() {
            const now = new Date();
            const end = new Date();
            end.setHours(23, 59, 59, 0);
            const diff = end - now;
            const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
            const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
            const el = document.getElementById('saleTimer');
            if (el) el.textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateSaleTimer, 1000);
        updateSaleTimer();

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
            if (!menu || !trigger) return;
            if (!menu.contains(e.target) && !trigger.contains(e.target)) menu.classList.add('hidden');
        });

        function toggleMobileSearch() {
            document.getElementById('mobileSearch').classList.toggle('hidden');
        }

        function shareProduct() {
            const url = window.location.href;
            if (navigator.share) {
                navigator.share({
                    title: 'Kemeja Oxford Slim Fit Premium',
                    text: 'Cek produk ini di Ecommerce Citra!',
                    url: url
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Link produk berhasil disalin!');
                }).catch(() => {
                    showToast('Gagal menyalin link.');
                });
            }
        }

        setMegaCategory('rumah-tangga');
    </script>
@endsection
