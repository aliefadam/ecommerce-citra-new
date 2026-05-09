@extends('layouts.user')

@section('title', ($appStoreName ?? 'Ecommerce Citra') . ' - Belanja Online Terpercaya')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #065f46 100%);
        }

        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .filter-panel {
            transition: all 0.3s ease;
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

        #flashSaleTrack::-webkit-scrollbar {
            height: 4px;
        }

        #flashSaleTrack::-webkit-scrollbar-track {
            background: #e2e8f0;
        }

        #flashSaleTrack::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 9999px;
        }

        #categoryTrack {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #categoryTrack::-webkit-scrollbar {
            display: none;
        }

        .badge-new {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .badge-promo {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .search-dropdown {
            display: none;
        }

        .search-wrapper:focus-within .search-dropdown {
            display: block;
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

        .swiper-container {
            scroll-snap-type: x mandatory;
        }

        .swiper-slide {
            scroll-snap-align: start;
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

        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
        <div class="flex items-center gap-3 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <span id="toast-msg">Produk ditambahkan ke keranjang!</span>
        </div>
    </div>

    <!-- NAVBAR -->
    @include('partials.navbar-user')

    {{-- HERO SECTION (dinonaktifkan, diganti carousel)
    <section class="hero-gradient text-white overflow-hidden relative">...</section>
    --}}
    <!-- HERO CAROUSEL BANNER -->
    @php
        $heroBanners = collect($bannersJson ?? [])
            ->filter(fn($b) => filled($b['image'] ?? null))
            ->values();
        if ($heroBanners->isEmpty()) {
            $heroBanners = collect([
                [
                    'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1600&h=700&fit=crop&crop=center',
                    'target_url' => '',
                ],
            ]);
        }

        // Side banners from DB (type=side), fallback to placeholders if none configured
        $sideBanners = collect($sideBannersJson ?? [])
            ->filter(fn($b) => filled($b['image'] ?? null))
            ->values();
        if ($sideBanners->isEmpty()) {
            $sideBanners = collect([
                [
                    'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&h=300&fit=crop&crop=center',
                    'target_url' => '',
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=300&fit=crop&crop=center',
                    'target_url' => '',
                ],
            ]);
        }
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-3 pb-0">
        {{-- Wrapper with explicit height so side banners align perfectly --}}
        <div class="flex gap-2 h-[160px] sm:h-[200px] md:h-[220px]">
            {{-- Main slider (left, full width on mobile, ~68% on desktop) --}}
            <div class="relative rounded-xl overflow-hidden shadow-sm flex-1 min-w-0 h-full" id="heroCarousel">
                <div id="carouselTrack" class="flex transition-transform duration-600 ease-in-out h-full">
                    @foreach ($heroBanners as $banner)
                        <div class="min-w-full h-full relative overflow-hidden flex-shrink-0">
                            @if (!empty($banner['target_url']))
                                <a href="{{ $banner['target_url'] }}" class="block w-full h-full">
                                    <img src="{{ $banner['image'] }}" alt="Banner {{ $loop->iteration }}" class="w-full h-full object-cover" />
                                </a>
                            @else
                                <img src="{{ $banner['image'] }}" alt="Banner {{ $loop->iteration }}" class="w-full h-full object-cover" />
                            @endif
                        </div>
                    @endforeach
                </div>

                <button onclick="carouselPrev()"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all z-10">
                    <i class="ri-arrow-left-s-line text-lg"></i>
                </button>
                <button onclick="carouselNext()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all z-10">
                    <i class="ri-arrow-right-s-line text-lg"></i>
                </button>

                @if ($heroBanners->count() > 1)
                    <div class="absolute bottom-3 left-4 flex gap-1.5 z-10" id="carouselDots">
                        @foreach ($heroBanners as $banner)
                            <button onclick="carouselGoTo({{ $loop->index }})"
                                class="carousel-dot h-1.5 rounded-full bg-white transition-all duration-300 {{ $loop->first ? '' : 'w-1.5' }}"
                                style="{{ $loop->first ? 'width:16px' : '' }}" data-index="{{ $loop->index }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Side banners: 2 stacked, exact same total height as main slider, hidden on mobile --}}
            <div class="hidden md:flex flex-col gap-2 w-[32%] shrink-0 h-full">
                @foreach ($sideBanners as $side)
                    <div class="rounded-xl overflow-hidden shadow-sm flex-1 min-h-0">
                        @if (!empty($side['target_url']))
                            <a href="{{ $side['target_url'] }}" class="block w-full h-full">
                                <img src="{{ $side['image'] }}" alt="Promo Banner" class="w-full h-full object-cover" />
                            </a>
                        @else
                            <img src="{{ $side['image'] }}" alt="Promo Banner" class="w-full h-full object-cover" />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <!-- KATEGORI SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-xs font-medium text-blue-500 tracking-widest uppercase mb-1">Browse</p>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight">Popular Categories</h2>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="categoryPrev()"
                    class="w-8 h-8 border border-slate-200 hover:border-slate-400 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 transition-all duration-200">
                    <i class="ri-arrow-left-s-line text-lg"></i>
                </button>
                <button type="button" onclick="categoryNext()"
                    class="w-8 h-8 border border-slate-200 hover:border-slate-400 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 transition-all duration-200">
                    <i class="ri-arrow-right-s-line text-lg"></i>
                </button>
            </div>
        </div>
        <div id="categoryTrack" class="flex flex-nowrap items-start gap-5 overflow-x-auto py-3 px-2 scrollbar-hide">
            @foreach (collect($homeMainCategories ?? []) as $cat)
                <a href="{{ route('frontend.kategori', ['parent' => $cat['slug']]) }}"
                    class="flex flex-col items-center gap-3 group shrink-0 w-[100px]">
                    <div class="relative w-[88px] h-[88px] rounded-full overflow-hidden bg-slate-50 ring-1 ring-slate-100 transition-all duration-300 group-hover:ring-2 group-hover:ring-blue-400 group-hover:shadow-md group-hover:-translate-y-1">
                        @if (!empty($cat['image']))
                            <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="{{ $cat['icon'] }} text-3xl text-blue-500"></i>
                            </div>
                        @endif
                    </div>
                    <p class="text-[11px] font-medium text-slate-500 group-hover:text-slate-800 tracking-wide uppercase text-center leading-tight transition-colors duration-200">{{ $cat['name'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <!-- PRODUK SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
        <div class="flex flex-col lg:flex-row gap-8"> <!-- SIDEBAR FILTER -->
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
                        <div class="space-y-2">
                            @foreach ($homeFilterCategories ?? [] as $cat)
                                <label class="flex items-center gap-2 cursor-pointer group"><input type="checkbox"
                                        class="filter-cat w-4 h-4 rounded accent-blue-500" value="{{ $cat['slug'] }}"
                                        checked onchange="applyFilter()" /><span
                                        class="text-sm text-slate-600 group-hover:text-slate-800">{{ $cat['name'] }}
                                        ({{ $cat['count'] }})
                                    </span></label>
                            @endforeach
                        </div>
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

            <!-- PRODUK GRID -->
            <main class="flex-1">
                <!-- Sort & View -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-800">Produk Terbaru</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5" id="productCount">Menampilkan 12 produk</p>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center gap-2 sm:hidden">
                            <button type="button" onclick="openMobileFilter()"
                                class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 flex items-center justify-center hover:bg-slate-50 transition-colors">
                                <i class="ri-filter-3-line text-base"></i>
                            </button>
                            <button type="button" onclick="cycleSortMobile()"
                                class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 flex items-center justify-center hover:bg-slate-50 transition-colors">
                                <i class="ri-arrow-up-down-line text-base"></i>
                            </button>
                        </div>
                        <select id="sortSelect" onchange="sortProducts()"
                            class="hidden sm:block border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-400 bg-white cursor-pointer">
                            <option value="newest">Terbaru</option>
                            <option value="price-low">Harga Terendah</option>
                            <option value="price-high">Harga Tertinggi</option>
                            <option value="popular">Terpopuler</option>
                        </select>
                        <div class="flex bg-slate-100 rounded-xl p-1 gap-1">
                            <button onclick="setView('grid')" id="gridBtn" class="p-1.5 rounded-lg bg-blue-500 text-white transition-all">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                            <button onclick="setView('list')" id="listBtn" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 transition-all">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-center gap-2 mt-10">
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:border-blue-300 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="w-9 h-9 rounded-lg bg-blue-500 text-white font-semibold text-sm">1</button>
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 hover:border-blue-300 hover:text-blue-600 transition-colors text-sm">2</button>
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 hover:border-blue-300 hover:text-blue-600 transition-colors text-sm">3</button>
                    <span class="text-slate-400">...</span>
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 hover:border-blue-300 hover:text-blue-600 transition-colors text-sm">10</button>
                    <button
                        class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:border-blue-300 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </main>
        </div>
    </section>

    <!-- PRODUK REKOMENDASI SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-1 h-7 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Produk Rekomendasi</h2>
            </div>
            <a href="{{ route('frontend.kategori') }}"
                class="text-blue-600 hover:text-blue-700 font-semibold text-sm flex items-center gap-1 transition-colors">
                Lihat Semua <i class="ri-arrow-right-s-line text-base"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="rekomendasiGrid">
            @php
                $rekProducts = collect($productsJson ?? [])
                    ->sortByDesc('rating')
                    ->take(10)
                    ->values();
            @endphp
            @forelse ($rekProducts as $rp)
                <div class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                    <a href="{{ url('/detail-produk/' . $rp['slug']) }}" class="relative overflow-hidden aspect-[4/3] block">
                        <img src="{{ $rp['image'] }}" alt="{{ $rp['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
                        @if (($rp['originalPrice'] ?? 0) > ($rp['price'] ?? 0))
                            @php $disc = round((1 - $rp['price'] / $rp['originalPrice']) * 100); @endphp
                            <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">-{{ $disc }}%</span>
                        @elseif (($rp['badge'] ?? '') === 'new')
                            <span class="absolute top-2 left-2 bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">BARU</span>
                        @elseif (($rp['badge'] ?? '') === 'best')
                            <span class="absolute top-2 left-2 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">TERLARIS</span>
                        @endif
                    </a>
                    <div class="p-3 flex-1 flex flex-col gap-1">
                        <a href="{{ url('/detail-produk/' . $rp['slug']) }}" class="text-sm font-semibold text-slate-800 hover:text-blue-600 line-clamp-2 leading-snug transition-colors">{{ $rp['name'] }}</a>
                        <div class="flex items-center gap-1">
                            <span class="text-yellow-400 text-xs">★</span>
                            <span class="text-xs font-medium text-slate-700">{{ number_format($rp['rating'], 1) }}</span>
                            @if (!empty($rp['reviews']))
                                <span class="text-xs text-slate-400">({{ number_format($rp['reviews']) }})</span>
                            @endif
                            @if (!empty($rp['sold']))
                                <span class="text-xs text-slate-300 mx-0.5">•</span>
                                <span class="text-xs text-slate-400">{{ number_format($rp['sold']) }} terjual</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap mt-auto pt-1">
                            @php
                                $rpPrice = (int) ($rp['price'] ?? 0);
                                $rpPriceMax = (int) ($rp['priceMax'] ?? $rpPrice);
                            @endphp
                            <span class="font-bold text-slate-900 text-sm">
                                @if ($rpPriceMax > $rpPrice)
                                    Rp {{ number_format($rpPrice, 0, ',', '.') }} - Rp {{ number_format($rpPriceMax, 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($rpPrice, 0, ',', '.') }}
                                @endif
                            </span>
                            @if ($rpPriceMax <= $rpPrice && ($rp['originalPrice'] ?? 0) > ($rp['price'] ?? 0))
                                <span class="text-slate-400 text-xs line-through">Rp {{ number_format($rp['originalPrice'], 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <button onclick="addToCart({{ $rp['id'] }})" class="w-full bg-blue-50 hover:bg-blue-500 text-blue-600 hover:text-white text-xs font-semibold py-2 rounded-full transition-all border border-blue-200 hover:border-blue-500 flex items-center justify-center gap-1.5 mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Keranjang
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-10 text-slate-400 text-sm">Belum ada produk rekomendasi.</div>
            @endforelse
        </div>
    </section>

    <!-- FLASH SALE SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-5">
        <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-3xl p-6 border border-red-100">
            <!-- Header -->
            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-11 h-11 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-red-200">
                            <i class="ri-flashlight-fill text-white text-xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-800">Flash Sale</h2>
                            </div>
                            <p class="text-slate-500 text-[11px] sm:text-xs">Penawaran terbatas, jangan sampai habis!</p>
                        </div>
                    </div>
                    <!-- Countdown -->
                    <div class="hidden sm:flex items-center gap-2 pl-4 border-l border-red-200">
                        <span class="text-slate-500 text-sm">Berakhir:</span>
                        <div class="flex gap-1.5 items-center">
                            <div class="bg-red-500 text-white rounded-lg px-2.5 py-1.5 text-center min-w-[42px] shadow-sm">
                                <div id="fs-hours" class="text-base font-bold leading-none">05</div>
                                <div class="text-[10px] text-red-200 mt-0.5">Jam</div>
                            </div>
                            <span class="text-red-400 font-bold text-lg">:</span>
                            <div class="bg-red-500 text-white rounded-lg px-2.5 py-1.5 text-center min-w-[42px] shadow-sm">
                                <div id="fs-minutes" class="text-base font-bold leading-none">23</div>
                                <div class="text-[10px] text-red-200 mt-0.5">Mnt</div>
                            </div>
                            <span class="text-red-400 font-bold text-lg">:</span>
                            <div class="bg-red-500 text-white rounded-lg px-2.5 py-1.5 text-center min-w-[42px] shadow-sm">
                                <div id="fs-seconds" class="text-base font-bold leading-none">47</div>
                                <div class="text-[10px] text-red-200 mt-0.5">Dtk</div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('frontend.flash-sale') }}"
                    class="text-red-500 hover:text-red-600 font-semibold text-xs sm:text-sm flex items-center gap-1 bg-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl border border-red-200 hover:border-red-300 transition-colors whitespace-nowrap self-start sm:self-auto">
                    Lihat Semua <i class="ri-arrow-right-line"></i>
                </a>
            </div>
            <div class="sm:hidden flex items-center gap-1.5 mb-3">
                <span class="text-slate-500 text-[11px]">Berakhir:</span>
                <div class="bg-red-500 text-white rounded-md px-2 py-1 text-xs font-bold" id="fs-hours-mobile">05</div>
                <span class="text-red-400 font-bold text-xs">:</span>
                <div class="bg-red-500 text-white rounded-md px-2 py-1 text-xs font-bold" id="fs-minutes-mobile">23</div>
                <span class="text-red-400 font-bold text-xs">:</span>
                <div class="bg-red-500 text-white rounded-md px-2 py-1 text-xs font-bold" id="fs-seconds-mobile">47</div>
            </div>
            <div class="sm:hidden flex items-center justify-end gap-2 mb-3">
                <button type="button" onclick="flashSalePrev()"
                    class="w-8 h-8 rounded-xl border border-red-200 bg-white text-red-500 hover:bg-red-50 transition-colors flex items-center justify-center">
                    <i class="ri-arrow-left-s-line text-lg"></i>
                </button>
                <button type="button" onclick="flashSaleNext()"
                    class="w-8 h-8 rounded-xl border border-red-200 bg-white text-red-500 hover:bg-red-50 transition-colors flex items-center justify-center">
                    <i class="ri-arrow-right-s-line text-lg"></i>
                </button>
            </div>

            <!-- Flash Sale Products -->
            <div id="flashSaleTrack"
                class="flex sm:grid sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 overflow-x-auto sm:overflow-visible scroll-smooth pb-1">
                @forelse (collect($flashSale['items'] ?? [])->take(10) as $fs)
                    <a href="{{ url('/detail-produk/' . $fs['slug']) }}"
                        class="min-w-[220px] w-[220px] sm:min-w-0 sm:w-auto bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow card-hover group border border-red-50">
                        <div class="relative">
                            <img src="{{ $fs['image'] }}"
                                class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-300" />
                            <span
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-{{ $fs['discountPercent'] }}%</span>
                        </div>
                        <div class="p-3">
                            <p class="text-[11px] sm:text-xs font-semibold text-slate-800 line-clamp-2 mb-1">
                                {{ $fs['name'] }}</p>
                            <p class="text-sm sm:text-base font-bold text-red-500">Rp
                                {{ number_format($fs['price'], 0, ',', '.') }}</p>
                            <p class="text-[11px] sm:text-xs text-slate-400 line-through">Rp
                                {{ number_format($fs['originalPrice'], 0, ',', '.') }}</p>
                            <div class="mt-2 w-full bg-red-100 rounded-full h-1.5">
                                <div class="bg-red-500 h-1.5 rounded-full"
                                    style="width:{{ 100 - $fs['remainingPercent'] }}%"></div>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-0.5">Tersisa {{ $fs['remainingPercent'] }}%</p>
                        </div>
                    </a>
                @empty
                    <div
                        class="col-span-full min-w-[220px] w-full bg-white rounded-2xl border border-red-100 p-6 text-center text-sm text-slate-500">
                        Belum ada flash sale aktif saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        // PRODUCT DATA
        const products = @json($productsJson);
        const flashSaleEndAt = @json($flashSale['end_at'] ?? null);
        const isAuthenticated = @json(auth()->check());
        const loginUrl = @json(route('login'));
        const cartStoreUrl = @json(route('frontend.cart.store'));
        const wishlistToggleUrl = @json(route('frontend.wishlist.toggle'));
        const wishlistStatusUrl = @json(route('frontend.wishlist.status'));
        const csrfToken = @json(csrf_token());
        const wishedProductIds = new Set();
        const carouselTotal = document.querySelectorAll('#carouselTrack > div').length || 1;

        let filteredProducts = [...products];
        let selectedColors = [];
        let currentView = 'grid';

        function getLoginRedirectUrl() {
            return `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
        }

        function renderProducts(prods) {
            const grid = document.getElementById('productGrid');
            document.getElementById('productCount').textContent = `Menampilkan ${prods.length} produk`;
            grid.innerHTML = prods.map(p => {
                const discount = p.originalPrice > p.price ? Math.round((1 - p.price / p.originalPrice) * 100) : 0;
                const priceMax = Number(p.priceMax ?? p.price);
                const isPriceRange = priceMax > Number(p.price);
                const priceLabel = isPriceRange ?
                    `Rp ${Number(p.price).toLocaleString('id-ID')} - Rp ${priceMax.toLocaleString('id-ID')}` :
                    `Rp ${Number(p.price).toLocaleString('id-ID')}`;
                const badgeHtml = p.isFlashSale ?
                    `<span class="badge-promo text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-${discount}%</span>` :
                    p.badge === 'new' ?
                    `<span class="badge-new text-white text-[10px] font-bold px-2 py-0.5 rounded-full">BARU</span>` :
                    p.badge === 'best' ?
                    `<span class="bg-blue-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">TERLARIS</span>` :
                    '';
                const stars = '?'.repeat(Math.floor(p.rating)) + (p.rating % 1 >= 0.5 ? '½' : '');
                return `
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover group h-full flex flex-col" data-id="${p.id}">
            <div class="relative overflow-hidden">
              <a href="{{ url('/detail-produk') }}/${p.slug}">
                <img src="${p.image}" alt="${p.name}" class="w-full h-44 sm:h-52 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
              </a>
              <div class="absolute top-2 left-2 flex gap-1 flex-wrap">${badgeHtml}</div>
              <button onclick="addToWishlist(${p.id})" data-wishlist-btn data-product-id="${p.id}" class="absolute top-2 right-2 w-8 h-8 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-pink-50">
                <svg class="w-4 h-4 text-pink-500" fill="${p.isWishlisted ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
              </button>
            </div>
            <div class="p-3 flex-1 flex flex-col">
              <a href="{{ url('/detail-produk') }}/${p.slug}" class="block">
                <h3 class="text-sm font-semibold text-slate-800 hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px] mb-1">${p.name}</h3>
              </a>
              <div class="flex items-center gap-1 mb-2">
                <span class="text-yellow-400 text-xs">⭐</span>
                <span class="text-xs font-medium text-slate-700">${p.rating}</span>
                <span class="text-xs text-slate-400">(${p.reviews.toLocaleString()})</span>
                <span class="text-xs text-slate-300 mx-1">•</span>
                <span class="text-xs text-slate-400">${p.sold.toLocaleString()} terjual</span>
              </div>
              <div class="flex items-center gap-2 flex-wrap min-h-[28px] mb-3">
                <span class="font-bold text-slate-900 text-base">${priceLabel}</span>
                ${!isPriceRange && p.originalPrice > p.price ? `<span class="text-slate-400 text-xs line-through">Rp ${p.originalPrice.toLocaleString('id-ID')}</span>` : ''}
              </div>
              <button onclick="addToCart(${p.id})" class="mt-auto w-full bg-blue-50 hover:bg-blue-500 text-blue-600 hover:text-white text-xs font-semibold py-2 rounded-full transition-all border border-blue-200 hover:border-blue-500 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Keranjang
              </button>
            </div>
          </div>`;
            }).join('');
        }

        async function addToCart(id) {
            const p = products.find(x => x.id === id);
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

        function addToWishlist(id) {
            if (!isAuthenticated) {
                window.location.href = getLoginRedirectUrl();
                return;
            }
            const p = products.find(x => x.id === id);
            if (!p) return;
            toggleWishlistByProductId(id, p.name);
        }

        async function toggleWishlistByProductId(productId, productName = 'Produk') {
            const res = await fetch(wishlistToggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_id: Number(productId),
                }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                showToast('Gagal memproses wishlist');
                return;
            }
            if (json.wished) wishedProductIds.add(Number(productId));
            else wishedProductIds.delete(Number(productId));
            syncWishlistButtons();
            showToast(json.wished ? `"${productName}" ditambahkan ke wishlist!` :
                `"${productName}" dihapus dari wishlist!`);
            window.dispatchEvent(new Event('wishlist:updated'));
        }

        function syncWishlistButtons() {
            document.querySelectorAll('[data-wishlist-btn]').forEach((btn) => {
                const productId = Number(btn.getAttribute('data-product-id') || 0);
                const icon = btn.querySelector('svg');
                if (!icon) return;
                icon.setAttribute('fill', wishedProductIds.has(productId) ? 'currentColor' : 'none');
            });
        }

        async function initWishlistStatus() {
            if (!isAuthenticated) return;
            const ids = products.map((p) => Number(p.id)).filter(Boolean);
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
            wishedIds.forEach((id) => wishedProductIds.add(Number(id)));
            syncWishlistButtons();
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
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

        function setPriceRange(min, max) {
            document.getElementById('priceMin').value = min;
            document.getElementById('priceMax').value = max;
            applyFilter();
        }

        function applyFilter() {
            const cats = Array.from(document.querySelectorAll('.filter-cat:checked')).map(c => c.value);
            const min = parseInt(document.getElementById('priceMin').value) || 0;
            const max = parseInt(document.getElementById('priceMax').value) || 9999999;
            filteredProducts = products.filter(p => {
                const catMatch = cats.length === 0 || cats.includes(p.parentCategorySlug);
                const priceMatch = p.price >= min && p.price <= max;
                const colorMatch = selectedColors.length === 0 || selectedColors.some(c => p.colors.includes(c));
                return catMatch && priceMatch && colorMatch;
            });
            renderProducts(filteredProducts);
        }

        function resetFilter() {
            document.querySelectorAll('.filter-cat').forEach(c => c.checked = true);
            document.getElementById('priceMin').value = '';
            document.getElementById('priceMax').value = '';
            document.querySelectorAll('input[name="priceRange"]').forEach(r => r.checked = false);
            document.querySelectorAll('input[name="rating"]').forEach(r => r.checked = false);
            selectedColors = [];
            document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('ring-2', 'ring-blue-500',
                'ring-offset-2'));
            filteredProducts = [...products];
            renderProducts(filteredProducts);
        }

        function sortProducts() {
            const val = document.getElementById('sortSelect').value;
            const sorted = [...filteredProducts];
            if (val === 'price-low') sorted.sort((a, b) => a.price - b.price);
            else if (val === 'price-high') sorted.sort((a, b) => b.price - a.price);
            else if (val === 'popular') sorted.sort((a, b) => b.sold - a.sold);
            else sorted.sort((a, b) => b.id - a.id);
            renderProducts(sorted);
        }

        function cycleSortMobile() {
            const select = document.getElementById('sortSelect');
            if (!select) return;
            const order = ['newest', 'price-low', 'price-high', 'popular'];
            const current = order.indexOf(select.value);
            select.value = order[(current + 1) % order.length];
            sortProducts();
            const labels = {
                'newest': 'Urut: Terbaru',
                'price-low': 'Urut: Termurah',
                'price-high': 'Urut: Termahal',
                'popular': 'Urut: Terpopuler'
            };
            showToast(labels[select.value] || 'Urutan diubah');
        }

        function setView(v) {
            currentView = v;
            const grid = document.getElementById('productGrid');
            if (v === 'grid') {
                grid.className = 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4';
                document.getElementById('gridBtn').className = 'p-2 bg-blue-500 text-white';
                document.getElementById('listBtn').className = 'p-2 text-slate-400 hover:text-slate-600 bg-white';
            } else {
                grid.className = 'grid grid-cols-1 gap-4';
                document.getElementById('listBtn').className = 'p-2 bg-blue-500 text-white';
                document.getElementById('gridBtn').className = 'p-2 text-slate-400 hover:text-slate-600 bg-white';
            }
            applyFilter();
        }

        function toggleMobileSearch() {
            const el = document.getElementById('mobileSearch');
            el.classList.toggle('hidden');
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
            container.innerHTML = `
        <div class="grid grid-cols-4 gap-6">
          ${sections.map(section => `
                                                                                                    <div>
                                                                                                      <h5 class="text-sm font-semibold text-slate-800 mb-3">${section.title}</h5>
                                                                                                      <ul class="space-y-2">
                                                                                                        ${section.items.map(item => `<li><a href="{{ route('frontend.kategori') }}" class="text-sm text-slate-600 hover:text-blue-600">${item}</a></li>`).join('')}
                                                                                                      </ul>
                                                                                                    </div>
                                                                                                  `).join('')}
        </div>
      `;
        }

        function setMegaCategory(key) {
            document.querySelectorAll('.mega-cat-btn').forEach((btn) => {
                btn.classList.remove('bg-blue-50', 'text-blue-700', 'font-semibold');
                btn.classList.add('text-slate-700');
            });

            const active = document.querySelector(`.mega-cat-btn[data-cat-key="${key}"]`);
            if (active) {
                active.classList.add('bg-blue-50', 'text-blue-700', 'font-semibold');
                active.classList.remove('text-slate-700');
            }

            renderMegaCategoryContent(key);
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('category-dropdown');
            const trigger = document.getElementById('category-trigger');
            if (!menu || !trigger) return;
            if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                menu.classList.add('hidden');
            }

            const sidebar = document.getElementById('filterSidebar');
            const panel = document.getElementById('filterPanel');
            if (sidebar && panel && window.innerWidth < 1024 && sidebar.classList.contains('fixed')) {
                if (e.target === sidebar) {
                    closeMobileFilter();
                }
            }
        });

        // Flash Sale Countdown Timer
        function updateTimer() {
            const now = new Date();
            const end = flashSaleEndAt ? new Date(flashSaleEndAt) : null;
            const diff = end ? Math.max(end - now, 0) : 0;
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            const hh = String(h).padStart(2, '0');
            const mm = String(m).padStart(2, '0');
            const ss = String(s).padStart(2, '0');
            const fsHours = document.getElementById('fs-hours');
            const fsMinutes = document.getElementById('fs-minutes');
            const fsSeconds = document.getElementById('fs-seconds');
            const fsHoursMobile = document.getElementById('fs-hours-mobile');
            const fsMinutesMobile = document.getElementById('fs-minutes-mobile');
            const fsSecondsMobile = document.getElementById('fs-seconds-mobile');
            if (fsHours) fsHours.textContent = hh;
            if (fsMinutes) fsMinutes.textContent = mm;
            if (fsSeconds) fsSeconds.textContent = ss;
            if (fsHoursMobile) fsHoursMobile.textContent = hh;
            if (fsMinutesMobile) fsMinutesMobile.textContent = mm;
            if (fsSecondsMobile) fsSecondsMobile.textContent = ss;
        }
        setInterval(updateTimer, 1000);
        updateTimer();

        // Hero Carousel
        let carouselIndex = 0;
        let carouselTimer;

        function carouselGoTo(idx) {
            if (carouselTotal <= 0) return;
            carouselIndex = idx;
            const track = document.getElementById('carouselTrack');
            if (!track) return;
            track.style.transform = `translateX(-${idx * 100}%)`;
            document.querySelectorAll('.carousel-dot').forEach((dot, i) => {
                if (i === idx) {
                    dot.style.width = '20px';
                    dot.style.backgroundColor = 'white';
                    dot.style.opacity = '1';
                } else {
                    dot.style.width = '8px';
                    dot.style.backgroundColor = 'white';
                    dot.style.opacity = '0.5';
                }
            });
            resetCarouselTimer();
        }

        function carouselNext() {
            if (carouselTotal <= 1) return;
            carouselGoTo((carouselIndex + 1) % carouselTotal);
        }

        function carouselPrev() {
            if (carouselTotal <= 1) return;
            carouselGoTo((carouselIndex - 1 + carouselTotal) % carouselTotal);
        }

        function resetCarouselTimer() {
            clearInterval(carouselTimer);
            if (carouselTotal > 1) {
                carouselTimer = setInterval(carouselNext, 5000);
            }
        }

        function flashSalePrev() {
            const track = document.getElementById('flashSaleTrack');
            if (!track) return;
            track.scrollBy({
                left: -(track.clientWidth * 0.85),
                behavior: 'smooth'
            });
        }

        function flashSaleNext() {
            const track = document.getElementById('flashSaleTrack');
            if (!track) return;
            track.scrollBy({
                left: track.clientWidth * 0.85,
                behavior: 'smooth'
            });
        }

        function categoryPrev() {
            const track = document.getElementById('categoryTrack');
            if (!track) return;
            track.scrollBy({
                left: -(track.clientWidth * 0.8),
                behavior: 'smooth'
            });
        }

        function categoryNext() {
            const track = document.getElementById('categoryTrack');
            if (!track) return;
            track.scrollBy({
                left: track.clientWidth * 0.8,
                behavior: 'smooth'
            });
        }

        if (carouselTotal > 0) {
            carouselGoTo(0);
        }

        // Init
        setMegaCategory('rumah-tangga');
        renderProducts(products);
        initWishlistStatus();
    </script>
@endsection
