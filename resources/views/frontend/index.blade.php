@extends('layouts.user')

@section('title', $appStoreSettings['seo_home_title'] ?: (($appStoreName ?? 'Ecommerce Citra') . ' - Belanja Online Terpercaya'))
@section('meta_description', $appStoreSettings['seo_home_description'] ?? 'Belanja online dengan mudah dan aman.')

@push('structured_data')
    @php
        $sameAs = collect(['social_instagram', 'social_tiktok', 'social_facebook', 'social_twitter', 'social_youtube'])
            ->map(fn ($key) => $appStoreSettings[$key] ?? null)->filter()->values()->all();
        $homeSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                ['@type' => 'WebSite', '@id' => route('frontend.index').'#website', 'url' => route('frontend.index'), 'name' => $appStoreName ?? 'Ecommerce Citra', 'inLanguage' => 'id-ID'],
                array_filter(['@type' => 'Organization', '@id' => route('frontend.index').'#organization', 'name' => $appStoreName ?? 'Ecommerce Citra', 'url' => route('frontend.index'), 'logo' => $appStoreLogoUrl ?? null, 'sameAs' => $sameAs]),
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($homeSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('style')
    <style>
        * {
            font-family: 'Inter Variable', Inter, sans-serif;
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

        .filter-drawer-handle {
            width: 40px;
            height: 5px;
            background: #cbd5e1;
            border-radius: 9999px;
            margin: 0 auto 16px;
            cursor: grab;
            touch-action: none;
        }

        .filter-drawer-handle:active {
            cursor: grabbing;
        }

        .flat-filter-panel {
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }

        .flat-filter-title {
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .flat-filter-section {
            padding: 1.125rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .flat-filter-panel input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 2px;
        }

        @media (max-width: 1023px) {
            #filterSidebar.mobile-filter-drawer {
                position: fixed;
                inset: 0;
                z-index: 60;
                display: flex;
                align-items: flex-end;
                background: rgba(15, 23, 42, 0);
                opacity: 0;
                transition: background 0.28s ease, opacity 0.28s ease;
            }

            #filterSidebar.mobile-filter-drawer:not(.mobile-filter-open) {
                pointer-events: none;
            }

            #filterSidebar.mobile-filter-drawer.mobile-filter-open {
                background: rgba(15, 23, 42, 0.4);
                opacity: 1;
                pointer-events: auto;
            }

            #filterPanel.mobile-filter-panel {
                width: 100%;
                max-height: 85vh;
                overflow-y: auto;
                overscroll-behavior: contain;
                border: 0;
                border-radius: 24px 24px 0 0;
                background: #fff;
                padding: 1.25rem;
                box-shadow: 0 -16px 40px rgb(15 23 42 / 0.14);
                position: relative;
                top: auto;
                transform: translateY(calc(100% + 24px));
                transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
                will-change: transform;
            }

            #filterSidebar.mobile-filter-open #filterPanel.mobile-filter-panel {
                transform: translateY(0);
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
            display: flex;
            flex-wrap: nowrap;
            gap: 0.625rem;
            overflow-x: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #categoryTrack::-webkit-scrollbar {
            display: none;
        }

        .featured-category-card {
            display: flex;
            flex: 0 0 132px;
            width: 132px;
            min-width: 132px;
            max-width: 132px;
            flex-direction: column;
            align-items: center;
        }

        .featured-category-image {
            display: grid;
            flex: 0 0 64px;
            width: 64px;
            height: 64px;
            max-width: 64px;
            max-height: 64px;
            place-items: center;
            overflow: hidden;
            border-radius: 9999px;
        }

        .featured-category-image img {
            display: block;
            width: 100%;
            height: 100%;
            max-width: 64px;
            max-height: 64px;
            object-fit: contain;
            padding: 0.375rem;
        }

        @media (min-width: 640px) {
            .featured-category-card {
                flex-basis: 140px;
                width: 140px;
                min-width: 140px;
                max-width: 140px;
            }
        }

        .badge-new {
            background: linear-gradient(135deg, var(--ec-primary-600), var(--ec-primary-800));
        }

        .badge-promo {
            background: linear-gradient(135deg, var(--ec-secondary-500), var(--ec-secondary-700));
        }

        .store-product-card {
            display: flex;
            min-width: 0;
            height: 100%;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #dbe3ed;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px rgb(15 23 42 / .025);
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .store-product-card:hover {
            transform: translateY(-2px);
            border-color: #b9c9de;
            box-shadow: 0 12px 24px rgb(15 45 86 / .09);
        }

        .store-product-media {
            position: relative;
            display: block;
            aspect-ratio: 1.18 / 1;
            overflow: hidden;
            background: linear-gradient(145deg, #fff 55%, #f8fafc);
        }

        .store-product-media img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .3s ease;
        }

        .store-product-card:hover .store-product-media img { transform: scale(1.04); }

        .store-wishlist-button {
            position: absolute;
            z-index: 2;
            top: .65rem;
            right: .65rem;
            display: grid;
            width: 2rem;
            height: 2rem;
            place-items: center;
            border-radius: 999px;
            background: rgb(255 255 255 / .9);
            color: #315783;
            transition: color .2s ease, background-color .2s ease, transform .2s ease;
        }

        .store-wishlist-button:hover { transform: scale(1.08); background: #eff6ff; color: #e11d48; }

        .store-product-body { display: flex; flex: 1; flex-direction: column; padding: .75rem .85rem .85rem; }
        .store-product-name { color: #082557; font-size: .8125rem; font-weight: 700; line-height: 1.35; }
        .store-product-variant { margin-top: .18rem; min-height: 1rem; color: #607594; font-size: .7rem; line-height: 1.35; }
        .store-product-price { margin-top: .45rem; color: #082557; font-size: 1rem; font-weight: 700; line-height: 1.2; }
        .store-product-seller { display: flex; min-width: 0; align-items: center; gap: .45rem; margin-top: .7rem; color: #3d587f; font-size: .67rem; }
        .store-product-seller svg { width: .85rem; height: .85rem; flex: 0 0 .85rem; color: #0f4d96; }
        .store-product-seller span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .store-product-meta { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: auto; padding-top: .75rem; color: #526a8c; font-size: .65rem; }
        .store-product-rating { display: inline-flex; align-items: center; gap: .25rem; color: #0d2d5d; font-weight: 600; }
        .store-product-rating svg { width: .75rem; height: .75rem; flex: 0 0 .75rem; color: #f59e0b; }

        .flash-sale-panel {
            border-block: 1px solid #e4eaf2;
            background: linear-gradient(110deg, #fff 0%, #f7fbff 56%, #fff 100%);
        }

        .flash-timer-unit {
            min-width: 4rem;
            border-radius: 9px;
            background: linear-gradient(145deg, #103a69, #061d3b);
            padding: .55rem .65rem;
            color: #fff;
            text-align: center;
            box-shadow: 0 7px 14px rgb(8 38 76 / .18);
        }

        .flash-sale-card .store-product-price { color: #f02046; }
        .flash-sale-progress { height: .45rem; overflow: hidden; border-radius: 999px; background: #e4eaf2; }
        .flash-sale-progress > span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #fb3658, #f02046); }

        #productGrid[data-view="list"] .store-product-card { display: grid; grid-template-columns: 12rem minmax(0, 1fr); }
        #productGrid[data-view="list"] .store-product-media { height: 100%; min-height: 12rem; aspect-ratio: auto; }

        @media (max-width: 639px) {
            .store-product-media { aspect-ratio: 1 / 1; }
            .store-product-body { padding: .65rem; }
            .store-product-seller { margin-top: .55rem; }
            .flash-timer-unit { min-width: 3.35rem; padding: .45rem; }
            #productGrid[data-view="list"] .store-product-card { grid-template-columns: 8rem minmax(0, 1fr); }
            #productGrid[data-view="list"] .store-product-media { min-height: 9rem; }
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
            background: var(--ec-secondary-500);
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

        /*
         * At md and above the main banner and both side banners are all 16:7.
         * This width accounts for the two side banners plus the 8px gap between
         * them, so the bottoms stay aligned without stretching the artwork.
         */
        .hero-side-banners {
            width: calc((100% - 26.285714px) / 3);
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
                    'image' => asset('imgs/banners/hero-industrial-default.png'),
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
                    'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&h=350&fit=crop&crop=center',
                    'target_url' => '',
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&h=350&fit=crop&crop=center',
                    'target_url' => '',
                ],
            ]);
        }
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-3 pb-0">
        {{-- Every banner uses the same 16:7 artwork ratio. The main banner defines the row height. --}}
        <div class="flex items-stretch gap-2">
            {{-- Main slider (full width on mobile, ~68% on desktop) --}}
            <div class="relative aspect-[16/7] rounded-xl overflow-hidden shadow-sm flex-1 min-w-0" id="heroCarousel">
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

            {{-- Two stacked banners share the main banner height and use the same artwork ratio. --}}
            <div class="hero-side-banners hidden md:flex flex-col gap-2 shrink-0">
                @foreach ($sideBanners->take(2) as $side)
                    <div class="aspect-[16/7] rounded-xl overflow-hidden shadow-sm shrink-0">
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
    <!-- KATEGORI PILIHAN -->
    <section class="border-y border-slate-100 bg-slate-50/80 py-6 sm:py-7" aria-labelledby="featuredCategoriesTitle">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="mb-4 sm:mb-5">
                <h2 id="featuredCategoriesTitle" class="text-xl sm:text-2xl font-bold text-slate-950 leading-tight">Kategori Pilihan</h2>
                <p class="mt-1 text-xs sm:text-sm font-normal text-slate-500">Temukan kebutuhan fastener dan tools sesuai kategori</p>
            </div>

            <div id="categoryTrack" class="flex snap-x snap-mandatory flex-nowrap gap-2.5 overflow-x-auto pb-1 scrollbar-hide">
                @forelse (collect($homeMainCategories ?? []) as $cat)
                    <a href="{{ route('frontend.kategori', ['parent' => $cat['slug']]) }}"
                        class="featured-category-card group shrink-0 snap-start rounded-xl border border-slate-200 bg-white px-3 py-3 text-center shadow-[0_1px_2px_rgb(15_23_42/.02)] transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
                        <span class="featured-category-image bg-slate-50 transition-colors duration-200 group-hover:bg-blue-50" aria-hidden="true">
                            @if (!empty($cat['image']))
                                <img src="{{ $cat['image'] }}" alt="" class="transition-transform duration-300 group-hover:scale-105" loading="lazy" />
                            @else
                                <i class="{{ $cat['icon'] }} text-3xl text-blue-600 transition-transform duration-300 group-hover:scale-110"></i>
                            @endif
                        </span>
                        <strong class="mt-2 block w-full truncate text-xs font-bold text-slate-950 sm:text-[13px]">{{ $cat['name'] }}</strong>
                        <span class="mt-1 text-[10px] font-normal text-slate-400 sm:text-[11px]">{{ number_format((int) ($cat['count'] ?? 0), 0, ',', '.') }} produk</span>
                    </a>
                @empty
                    <div class="w-full rounded-xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center text-sm text-slate-500">Kategori sedang disiapkan.</div>
                @endforelse
            </div>
        </div>
    </section>

    @php
        $flashSaleItems = collect($flashSale['items'] ?? [])->take(6);
    @endphp

    @if ($flashSaleItems->isNotEmpty())
        <!-- FLASH SALE SECTION -->
        <section class="flash-sale-panel py-7 sm:py-9">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-flashlight-fill text-4xl text-rose-500 sm:text-5xl" aria-hidden="true"></i>
                        <div>
                            <h2 class="text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Flash <span class="text-blue-700">Sale</span></h2>
                            <p class="mt-0.5 text-xs text-slate-600 sm:text-sm">Penawaran terbatas untuk kebutuhan proyek Anda</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                        <span class="text-xs font-medium text-slate-700 sm:text-sm">Berakhir dalam</span>
                        <div class="flex items-center gap-2" aria-label="Hitung mundur flash sale">
                            <div class="flash-timer-unit"><strong id="fs-hours" class="block text-lg leading-none sm:text-xl">00</strong><small class="mt-1 block text-[9px] text-blue-100">Jam</small></div>
                            <div class="flash-timer-unit"><strong id="fs-minutes" class="block text-lg leading-none sm:text-xl">00</strong><small class="mt-1 block text-[9px] text-blue-100">Menit</small></div>
                            <div class="flash-timer-unit"><strong id="fs-seconds" class="block text-lg leading-none sm:text-xl">00</strong><small class="mt-1 block text-[9px] text-blue-100">Detik</small></div>
                        </div>
                        <a href="{{ route('frontend.flash-sale') }}" class="ml-auto inline-flex min-h-10 items-center gap-2 border-l border-slate-200 pl-4 text-xs font-semibold text-blue-700 transition-colors hover:text-blue-900 sm:text-sm">Lihat Semua Promo <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
                    </div>
                </div>

                <div id="flashSaleTrack" class="flex gap-2.5 overflow-x-auto scroll-smooth pb-2 sm:grid sm:grid-cols-3 sm:overflow-visible lg:grid-cols-6">
                    @foreach ($flashSaleItems as $fs)
                        @php $soldPercent = 100 - (int) $fs['remainingPercent']; @endphp
                        <article class="store-product-card flash-sale-card w-[190px] min-w-[190px] sm:w-auto sm:min-w-0">
                            <div class="store-product-media">
                                <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="block h-full" aria-label="Lihat {{ $fs['name'] }}">
                                    <img src="{{ $fs['image'] }}" alt="{{ $fs['name'] }}" loading="lazy" />
                                </a>
                                <span class="absolute left-2.5 top-2.5 rounded-md bg-rose-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">-{{ $fs['discountPercent'] }}%</span>
                                <button type="button" onclick="addToWishlist({{ $fs['productId'] }})" data-wishlist-btn data-product-id="{{ $fs['productId'] }}" class="store-wishlist-button" aria-label="Tambahkan {{ $fs['name'] }} ke wishlist">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </button>
                            </div>
                            <div class="store-product-body">
                                <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="store-product-name line-clamp-2 hover:text-blue-700">{{ $fs['name'] }}</a>
                                <p class="store-product-variant truncate">{{ $fs['variantName'] ?: 'Pilihan produk industri' }}</p>
                                <div class="mt-1 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                    <strong class="store-product-price">Rp {{ number_format($fs['price'], 0, ',', '.') }}</strong>
                                    <span class="text-[10px] text-slate-400 line-through">Rp {{ number_format($fs['originalPrice'], 0, ',', '.') }}</span>
                                </div>
                                @if (!empty($fs['storeName']))
                                    <p class="store-product-seller"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h16v-9.5M3 4h18l-1.5 6a2.5 2.5 0 0 1-4.5 1.1 2.5 2.5 0 0 1-4.5 0A2.5 2.5 0 0 1 6 10L4.5 4M9 20v-5h6v5"/></svg><span>{{ $fs['storeName'] }}</span></p>
                                @endif
                                <div class="mt-auto pt-3">
                                    <div class="flash-sale-progress"><span style="width: {{ $soldPercent }}%"></span></div>
                                    <div class="mt-1.5 flex justify-between gap-2 text-[10px] font-medium text-slate-700"><span>{{ $soldPercent }}% terjual</span><span>Sisa {{ $fs['remaining'] }}</span></div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- PRODUK SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
        <div class="flex flex-col lg:flex-row gap-8"> <!-- SIDEBAR FILTER -->
            <aside id="filterSidebar" class="hidden lg:block lg:w-64 flex-shrink-0">
                <div id="filterPanel" class="flat-filter-panel sticky top-20 flex max-h-[calc(100vh-6rem)] flex-col">
                    <div id="filterDrawerHandle" class="filter-drawer-handle lg:hidden"></div>
                    <div class="flat-filter-title flex flex-shrink-0 items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-950">Filter</h3>
                        <div class="flex items-center gap-3">
                            <button onclick="resetFilter()"
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium">Reset</button>
                            <button onclick="closeMobileFilter()"
                                class="lg:hidden text-xs text-slate-500 hover:text-slate-700 font-medium">Tutup</button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto pr-2">
                        <div class="flat-filter-section">
                            <button type="button" class="flex w-full items-center justify-between gap-3 text-left"
                                aria-expanded="true" aria-controls="homeCategoryPanel" onclick="toggleFilterSection(this, 'homeCategoryPanel')">
                                <span class="text-sm font-medium text-slate-950">Kategori</span>
                                <i class="ri-arrow-down-s-line rotate-180 text-lg text-slate-400 transition-transform"></i>
                            </button>
                            <div id="homeCategoryPanel" class="pt-3">
                                <div class="relative mb-3">
                                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                    <input id="homeCategorySearch" type="search" placeholder="Cari kategori..."
                                        class="w-full rounded border border-slate-300 bg-transparent py-2 pl-8 pr-3 text-sm outline-none transition focus:border-blue-500"
                                        oninput="searchCategoryOptions(this, 'homeCategoryOptions', 'homeCategoryEmpty')" />
                                </div>
                                <div id="homeCategoryOptions" class="space-y-3">
                                    @foreach ($homeFilterCategories ?? [] as $cat)
                                        <label class="filter-category-option flex items-center gap-2 cursor-pointer group"><input type="checkbox"
                                                class="filter-cat w-4 h-4 rounded accent-blue-500" value="{{ $cat['slug'] }}"
                                                onchange="applyFilter()" /><span
                                                class="text-sm text-slate-700 group-hover:text-slate-950">{{ $cat['name'] }}
                                                ({{ $cat['count'] }})
                                            </span></label>
                                    @endforeach
                                </div>
                                <p id="homeCategoryEmpty" class="hidden py-2 text-xs text-slate-400">Kategori tidak ditemukan.</p>
                            </div>
                        </div>

                        <div class="flat-filter-section">
                            <button type="button" class="flex w-full items-center justify-between gap-3 text-left"
                                aria-expanded="true" aria-controls="homePricePanel" onclick="toggleFilterSection(this, 'homePricePanel')">
                                <span class="text-sm font-medium text-slate-950">Harga</span>
                                <i class="ri-arrow-down-s-line rotate-180 text-lg text-slate-400 transition-transform"></i>
                            </button>
                            <div id="homePricePanel" class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 pt-3">
                                <input id="homePriceMin" type="number" min="0" placeholder="Min" oninput="applyFilter()"
                                    class="min-w-0 w-full rounded border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500" />
                                <span class="text-slate-400">-</span>
                                <input id="homePriceMax" type="number" min="0" placeholder="Max" oninput="applyFilter()"
                                    class="min-w-0 w-full rounded border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500" />
                            </div>
                        </div>

                        <div id="homeFilterVariantList"></div>
                    </div>
                </div>
            </aside>

            <!-- PRODUK GRID -->
            <main class="flex-1">
                <!-- Sort & View -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Produk <span class="text-blue-700">Terlaris</span></h2>
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
                            <option value="popular" selected>Terpopuler</option>
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
                <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-4">
                </div>

                <!-- Load More -->
                <div id="homeLoadMoreWrapper" class="flex flex-col items-center gap-3 mt-10">
                    <p id="homeLoadMoreInfo" class="text-xs text-slate-500"></p>
                    <button type="button" id="homeLoadMoreBtn" onclick="loadMoreProducts()"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-5 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-colors">
                        Muat Lagi
                        <i class="ri-arrow-down-s-line text-lg"></i>
                    </button>
                </div>
            </main>
        </div>
    </section>

    @if (session('newsletter_success') || session('newsletter_error') || $errors->has('email'))
        <div class="fixed top-4 right-4 z-[9999] max-w-sm rounded-xl shadow-2xl px-5 py-3 text-sm font-medium {{ session('newsletter_success') ? 'bg-blue-500 text-white' : 'bg-red-500 text-white' }}">
            {{ session('newsletter_success') ?? session('newsletter_error') ?? $errors->first('email') }}
        </div>
    @endif

    <!-- PRODUK REKOMENDASI SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="ec-section-marker"></div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Produk Rekomendasi</h2>
            </div>
            <a href="{{ route('frontend.kategori') }}"
                class="text-blue-600 hover:text-blue-700 font-semibold text-sm flex items-center gap-1 transition-colors">
                Lihat Semua <i class="ri-arrow-right-s-line text-base"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-4" id="rekomendasiGrid">
            @php
                $rekProducts = collect($productsJson ?? [])
                    ->sortByDesc('rating')
                    ->take(10)
                    ->values();
            @endphp
            @forelse ($rekProducts as $rp)
                @php $rpVariant = collect($rp['variants'] ?? [])->pluck('value')->filter()->take(2)->implode(' · '); @endphp
                <article class="store-product-card">
                    <div class="store-product-media">
                        <a href="{{ url('/detail-produk/' . $rp['slug']) }}" class="block h-full" aria-label="Lihat {{ $rp['name'] }}">
                            <img src="{{ $rp['image'] }}" alt="{{ $rp['name'] }}" loading="lazy" />
                        </a>
                        @if (($rp['originalPrice'] ?? 0) > ($rp['price'] ?? 0))
                            @php $disc = round((1 - $rp['price'] / $rp['originalPrice']) * 100); @endphp
                            <span class="absolute left-2.5 top-2.5 rounded-md bg-rose-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">-{{ $disc }}%</span>
                        @elseif (($rp['badge'] ?? '') === 'new')
                            <span class="absolute left-2.5 top-2.5 rounded-md bg-blue-700 px-2 py-1 text-[10px] font-bold text-white shadow-sm">BARU</span>
                        @elseif (($rp['badge'] ?? '') === 'best')
                            <span class="absolute left-2.5 top-2.5 rounded-md bg-amber-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">TERLARIS</span>
                        @endif
                        <button type="button" onclick="addToWishlist({{ $rp['id'] }})" data-wishlist-btn data-product-id="{{ $rp['id'] }}" class="store-wishlist-button" aria-label="Tambahkan {{ $rp['name'] }} ke wishlist">
                            <svg class="h-4 w-4" fill="{{ !empty($rp['isWishlisted']) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </button>
                    </div>
                    <div class="store-product-body">
                        <a href="{{ url('/detail-produk/' . $rp['slug']) }}" class="store-product-name line-clamp-2 hover:text-blue-700">{{ $rp['name'] }}</a>
                        <p class="store-product-variant truncate">{{ $rpVariant ?: ($rp['category'] ?? 'Produk industri') }}</p>
                        <p class="store-product-price">Rp {{ number_format((int) ($rp['price'] ?? 0), 0, ',', '.') }}</p>
                        <p class="store-product-seller"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h16v-9.5M3 4h18l-1.5 6a2.5 2.5 0 0 1-4.5 1.1 2.5 2.5 0 0 1-4.5 0A2.5 2.5 0 0 1 6 10L4.5 4M9 20v-5h6v5"/></svg><span>{{ $rp['storeName'] ?: 'Mitra industri' }}</span></p>
                        <div class="store-product-meta">
                            <span class="store-product-rating"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.7 2.83 5.73 6.32.92-4.58 4.46 1.08 6.3L12 17.14l-5.65 2.97 1.08-6.3-4.58-4.46 6.32-.92L12 2.7Z"/></svg>{{ number_format((float) ($rp['rating'] ?? 0), 1) }} <span class="font-normal text-slate-400">({{ number_format((int) ($rp['reviews'] ?? 0)) }})</span></span>
                            <span>Terjual {{ number_format((int) ($rp['sold'] ?? 0)) }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-10 text-slate-400 text-sm">Belum ada produk rekomendasi.</div>
            @endforelse
        </div>
    </section>

    @if (!empty($latestPosts) && $latestPosts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-5 sm:mb-6">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] uppercase text-blue-600 mb-2">Insight</p>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Blog Terbaru</h2>
                    <p class="mt-1 text-sm text-slate-500">Informasi, tips, dan update untuk kebutuhan teknik kamu.</p>
                </div>
                <a href="{{ route('frontend.blog.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    Lihat Semua
                    <i class="ri-arrow-right-line text-base"></i>
                </a>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                @foreach ($latestPosts as $post)
                    <a href="{{ route('frontend.blog.show', $post->slug) }}"
                        class="group overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                            @if ($post->hero_image)
                                <img src="{{ $post->hero_image }}" alt="{{ $post->title }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                            @else
                                <div class="h-full w-full bg-gradient-to-br from-slate-900 via-blue-900 to-blue-600"></div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-slate-950/45 to-transparent"></div>
                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600 shadow-sm">Blog</span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                                <span>{{ optional($post->published_at)->format('d M Y') ?: 'Artikel' }}</span>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span>{{ max(1, ceil(str_word_count(strip_tags((string) $post->content)) / 180)) }} min read</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-extrabold leading-snug text-slate-900 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $post->title }}</h3>
                            @if ($post->excerpt)
                                <p class="mt-2 text-sm leading-6 text-slate-500 line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-blue-600">
                                Baca Artikel
                                <i class="ri-arrow-right-up-line"></i>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-4">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-blue-800 px-6 py-8 sm:px-10 sm:py-10 text-white shadow-xl overflow-hidden relative">
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at top right, white 0, transparent 35%), radial-gradient(circle at bottom left, white 0, transparent 30%);"></div>
            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold tracking-[0.3em] uppercase text-blue-200 mb-2">Newsletter</p>
                    <h3 class="text-2xl sm:text-3xl font-bold leading-tight mb-2">Dapatkan update promo dan produk terbaru</h3>
                    <p class="text-sm sm:text-base text-slate-200">Masukkan email kamu untuk menerima info diskon, restock, dan penawaran khusus dari {{ $appStoreName ?? 'Ecommerce Citra' }}.</p>
                </div>
                <form action="{{ route('frontend.newsletter.subscribe') }}" method="POST" class="w-full lg:max-w-xl">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Masukkan email kamu"
                                class="w-full rounded-2xl border {{ $errors->has('email') || session('newsletter_error') ? 'border-red-300 focus:border-red-400' : 'border-white/20 focus:border-blue-300' }} bg-white px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:outline-none"
                                required>
                            @error('email')
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                            @if (session('newsletter_error'))
                                <p class="mt-2 text-sm text-red-200">{{ session('newsletter_error') }}</p>
                            @endif
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-500 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-400 transition-colors whitespace-nowrap">
                            Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script>
        // PRODUCT DATA
        const products = @json(collect($productsJson ?? [])->sortByDesc('sold')->values());
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
        let selectedVariantFilters = {};
        let currentView = 'grid';
        const productPageSize = 12;
        const filterOptionPreviewLimit = 4;
        let visibleProductCount = productPageSize;
        let currentRenderedProducts = [...products];

        function getLoginRedirectUrl() {
            return `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
        }

        function renderProducts(prods, resetVisible = true) {
            const grid = document.getElementById('productGrid');
            const loadMoreWrapper = document.getElementById('homeLoadMoreWrapper');
            const loadMoreBtn = document.getElementById('homeLoadMoreBtn');
            const loadMoreInfo = document.getElementById('homeLoadMoreInfo');

            currentRenderedProducts = [...prods];
            if (resetVisible) visibleProductCount = productPageSize;
            const visibleProducts = currentRenderedProducts.slice(0, visibleProductCount);

            document.getElementById('productCount').textContent =
                `Menampilkan ${visibleProducts.length} dari ${currentRenderedProducts.length} produk`;
            loadMoreWrapper.classList.toggle('hidden', currentRenderedProducts.length <= productPageSize);
            loadMoreBtn.classList.toggle('hidden', visibleProducts.length >= currentRenderedProducts.length);
            loadMoreInfo.textContent = `Sudah tampil ${visibleProducts.length} dari ${currentRenderedProducts.length} produk`;

            grid.innerHTML = visibleProducts.map(p => {
                const discount = p.originalPrice > p.price ? Math.round((1 - p.price / p.originalPrice) * 100) : 0;
                const priceLabel = `Rp ${Number(p.price).toLocaleString('id-ID')}`;
                const badgeHtml = p.isFlashSale ?
                    `<span class="absolute left-2.5 top-2.5 rounded-md bg-rose-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">-${discount}%</span>` :
                    p.badge === 'new' ?
                    `<span class="absolute left-2.5 top-2.5 rounded-md bg-blue-700 px-2 py-1 text-[10px] font-bold text-white shadow-sm">BARU</span>` :
                    p.badge === 'best' ?
                    `<span class="absolute left-2.5 top-2.5 rounded-md bg-amber-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">TERLARIS</span>` :
                    '';
                const variants = Array.isArray(p.variants) ? p.variants.map(v => v.value).filter(Boolean) : [];
                const variantLabel = variants.slice(0, 2).join(' · ') || p.category || 'Produk industri';
                const productUrl = `{{ url('/detail-produk') }}/${encodeURIComponent(p.slug)}`;
                const productName = escapeHtml(p.name);
                const sellerName = escapeHtml(p.storeName || 'Mitra industri');

                return `
          <article class="store-product-card" data-id="${p.id}">
            <div class="store-product-media">
              <a href="${productUrl}" class="block h-full" aria-label="Lihat ${productName}">
                <img src="${escapeHtml(p.image)}" alt="${productName}" loading="lazy" />
              </a>
              ${badgeHtml}
              <button type="button" onclick="addToWishlist(${p.id})" data-wishlist-btn data-product-id="${p.id}" class="store-wishlist-button" aria-label="Tambahkan ${productName} ke wishlist">
                <svg class="h-4 w-4" fill="${p.isWishlisted ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
              </button>
            </div>
            <div class="store-product-body">
              <a href="${productUrl}" class="store-product-name line-clamp-2 hover:text-blue-700">${productName}</a>
              <p class="store-product-variant truncate">${escapeHtml(variantLabel)}</p>
              <p class="store-product-price">${priceLabel}</p>
              <p class="store-product-seller"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h16v-9.5M3 4h18l-1.5 6a2.5 2.5 0 0 1-4.5 1.1 2.5 2.5 0 0 1-4.5 0A2.5 2.5 0 0 1 6 10L4.5 4M9 20v-5h6v5"/></svg><span>${sellerName}</span></p>
              <div class="store-product-meta">
                <span class="store-product-rating"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.7 2.83 5.73 6.32.92-4.58 4.46 1.08 6.3L12 17.14l-5.65 2.97 1.08-6.3-4.58-4.46 6.32-.92L12 2.7Z"/></svg>${Number(p.rating || 0).toFixed(1)} <span class="font-normal text-slate-400">(${Number(p.reviews || 0).toLocaleString('id-ID')})</span></span>
                <span>Terjual ${Number(p.sold || 0).toLocaleString('id-ID')}</span>
              </div>
            </div>
          </article>`;
            }).join('');
            syncWishlistButtons();
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

        function normalizeFilterValue(value) {
            return String(value || '').trim().toLowerCase();
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            } [char]));
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
            const query = normalizeFilterValue(input.value);
            let visibleCount = 0;

            options.forEach((option) => {
                const visible = !query || normalizeFilterValue(option.textContent).includes(query);
                option.classList.toggle('hidden', !visible);
                if (visible) visibleCount++;
            });

            document.getElementById(emptyId)?.classList.toggle('hidden', visibleCount > 0);
        }

        function renderFilterVariants() {
            const container = document.getElementById('homeFilterVariantList');
            if (!container) return;

            const groups = new Map();
            products.forEach((product) => {
                (Array.isArray(product.variants) ? product.variants : []).forEach((variant) => {
                    const name = String(variant.name || '').trim();
                    const value = String(variant.value || '').trim();
                    if (!name || !value) return;
                    if (!groups.has(name)) groups.set(name, new Map());
                    groups.get(name).set(normalizeFilterValue(value), value);
                });
            });

            container.innerHTML = Array.from(groups.entries()).map(([name, values]) => {
                const groupKey = normalizeFilterValue(name);
                const valueItems = Array.from(values.entries()).map(([key, label]) => `
                    <label class="filter-variant-option flex items-center gap-2 cursor-pointer group" data-search-value="${escapeHtml(normalizeFilterValue(label))}">
                        <input type="checkbox" class="filter-variant w-4 h-4 rounded accent-blue-500"
                            data-variant-name="${encodeURIComponent(groupKey)}"
                            data-variant-value="${encodeURIComponent(key)}"
                            onchange="applyVariantFilter()" />
                        <span class="text-sm text-slate-600 group-hover:text-slate-800">${escapeHtml(label)}</span>
                    </label>
                `).join('');

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
                        <div class="relative mb-3">
                            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="search" placeholder="Cari ${escapeHtml(name)}..."
                                class="filter-variant-search w-full border border-slate-200 rounded-lg pl-8 pr-3 py-2 text-sm focus:outline-none focus:border-blue-400"
                                data-variant-group="${encodeURIComponent(groupKey)}"
                                oninput="searchVariantOptions(this)" />
                        </div>
                        <div class="filter-variant-options space-y-2" data-variant-group="${encodeURIComponent(groupKey)}">${valueItems}</div>
                        <button type="button"
                            class="filter-variant-toggle mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700 ${values.size <= filterOptionPreviewLimit ? 'hidden' : ''}"
                            data-variant-group="${encodeURIComponent(groupKey)}"
                            data-expanded="false"
                            onclick="toggleVariantOptions(this)">
                            Lihat semua
                        </button>
                        <p class="filter-variant-empty hidden text-xs text-slate-400" data-variant-group="${encodeURIComponent(groupKey)}">Tidak ada varian yang cocok.</p>
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

        function searchVariantOptions(input) {
            updateVariantOptionVisibility(input.dataset.variantGroup || '');
        }

        function toggleVariantOptions(button) {
            button.dataset.expanded = button.dataset.expanded === 'true' ? 'false' : 'true';
            updateVariantOptionVisibility(button.dataset.variantGroup || '');
        }

        function updateVariantOptionVisibility(group) {
            const searchInput = document.querySelector(`.filter-variant-search[data-variant-group="${group}"]`);
            const toggle = document.querySelector(`.filter-variant-toggle[data-variant-group="${group}"]`);
            const query = normalizeFilterValue(searchInput?.value || '');
            const options = Array.from(document.querySelectorAll(`.filter-variant-options[data-variant-group="${group}"] .filter-variant-option`));
            const matchingOptions = options.filter((option) => !query || String(option.dataset.searchValue || '').includes(query));
            const isExpanded = toggle?.dataset.expanded === 'true';
            let visibleCount = 0;

            options.forEach((option) => {
                const matchesSearch = !query || String(option.dataset.searchValue || '').includes(query);
                const isChecked = option.querySelector('.filter-variant')?.checked;
                const previewIndex = matchingOptions.indexOf(option);
                const isVisible = matchesSearch && (query || isExpanded || previewIndex < filterOptionPreviewLimit || isChecked);
                option.classList.toggle('hidden', !isVisible);
                if (isVisible) visibleCount++;
            });

            if (toggle) {
                toggle.classList.toggle('hidden', query || matchingOptions.length <= filterOptionPreviewLimit);
                toggle.textContent = isExpanded ? 'Ringkas' : `Lihat semua (${matchingOptions.length})`;
            }

            const empty = document.querySelector(`.filter-variant-empty[data-variant-group="${group}"]`);
            if (empty) empty.classList.toggle('hidden', visibleCount > 0);
        }

        function applyVariantFilter() {
            selectedVariantFilters = {};
            document.querySelectorAll('.filter-variant:checked').forEach((input) => {
                const name = decodeURIComponent(input.dataset.variantName || '');
                const value = decodeURIComponent(input.dataset.variantValue || '');
                if (!name || !value) return;
                if (!selectedVariantFilters[name]) selectedVariantFilters[name] = new Set();
                selectedVariantFilters[name].add(value);
            });
            document.querySelectorAll('.filter-variant-options').forEach((group) => updateVariantOptionVisibility(group.dataset.variantGroup || ''));
            openCheckedVariantGroups();
            applyFilter();
        }

        function applyFilter() {
            const cats = Array.from(document.querySelectorAll('.filter-cat:checked')).map(c => c.value);
            const priceMin = Number(document.getElementById('homePriceMin')?.value || 0);
            const priceMax = Number(document.getElementById('homePriceMax')?.value || 0);
            const activeVariantGroups = Object.entries(selectedVariantFilters).filter(([, values]) => values.size > 0);
            filteredProducts = products.filter(p => {
                const catMatch = cats.length === 0 || cats.includes(p.parentCategorySlug);
                const priceMatch = (!priceMin || Number(p.price) >= priceMin) && (!priceMax || Number(p.price) <= priceMax);
                const variantMatch = activeVariantGroups.length === 0 || activeVariantGroups.every(([name, values]) =>
                    Array.isArray(p.variants) && p.variants.some((variant) =>
                        normalizeFilterValue(variant.name) === name && values.has(normalizeFilterValue(variant.value))
                    )
                );
                return catMatch && priceMatch && variantMatch;
            });
            renderProducts(filteredProducts);
        }

        function resetFilter() {
            document.querySelectorAll('.filter-cat').forEach(c => c.checked = false);
            const categorySearch = document.getElementById('homeCategorySearch');
            if (categorySearch) {
                categorySearch.value = '';
                searchCategoryOptions(categorySearch, 'homeCategoryOptions', 'homeCategoryEmpty');
            }
            const priceMin = document.getElementById('homePriceMin');
            const priceMax = document.getElementById('homePriceMax');
            if (priceMin) priceMin.value = '';
            if (priceMax) priceMax.value = '';
            selectedVariantFilters = {};
            document.querySelectorAll('.filter-variant').forEach((el) => {
                el.checked = false;
            });
            document.querySelectorAll('.filter-variant-search').forEach((el) => {
                el.value = '';
            });
            document.querySelectorAll('.filter-variant-toggle').forEach((el) => {
                el.dataset.expanded = 'false';
            });
            document.querySelectorAll('.filter-variant-options').forEach((group) => updateVariantOptionVisibility(group.dataset.variantGroup || ''));
            document.querySelectorAll('.filter-variant-group-toggle').forEach((el) => setVariantGroupExpanded(el.dataset.variantGroup || '', false));
            applyFilter();
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

        function loadMoreProducts() {
            visibleProductCount += productPageSize;
            renderProducts(currentRenderedProducts, false);
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
            grid.dataset.view = v;
            if (v === 'grid') {
                grid.className = 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-4';
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
            return {
                sidebar,
                panel,
                handle,
            };
        }

        function syncMobileFilterDrawerMode() {
            const {
                sidebar,
                panel
            } = getMobileFilterElements();
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
            const {
                sidebar,
                panel
            } = getMobileFilterElements();
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

            const {
                panel,
                handle
            } = getMobileFilterElements();

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

            handle.addEventListener('touchstart', startDrag, {
                passive: true
            });
            window.addEventListener('touchmove', onDrag, {
                passive: false
            });
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

        function toggleCategoryMenu(event) {
            if (event) event.stopPropagation();
            const menu = document.getElementById('category-dropdown');
            if (!menu) return;
            menu.classList.toggle('hidden');
        }

        const megaCategoryData = {
            'rumah-tangga': [{
                    title: 'Baut',
                    items: ['Baut Hex', 'Baut L', 'Baut Roofing', 'Baut Stainless']
                },
                {
                    title: 'Mur',
                    items: ['Mur Hex', 'Mur Nyloc', 'Mur Flange', 'Mur Kuping']
                },
                {
                    title: 'Sekrup',
                    items: ['Sekrup Kayu', 'Sekrup Gypsum', 'Sekrup SDS', 'Sekrup Mesin']
                },
                {
                    title: 'Ring & Washer',
                    items: ['Ring Plat', 'Ring Per', 'Washer Stainless', 'Washer Galvanis']
                }
            ],
            'fashion-pria': [{
                    title: 'Dynabolt',
                    items: ['Dynabolt M8', 'Dynabolt M10', 'Dynabolt M12', 'Drop In Anchor']
                },
                {
                    title: 'Anchor',
                    items: ['Fischer', 'Chemical Anchor', 'Sleeve Anchor', 'Anchor Bolt']
                },
                {
                    title: 'Tools',
                    items: ['Kunci Pas', 'Kunci L', 'Obeng', 'Tang']
                },
                {
                    title: 'Mata Bor',
                    items: ['Mata Bor Besi', 'Mata Bor Beton', 'Hole Saw', 'Countersink']
                }
            ],
            'fashion-wanita': [{
                    title: 'Paku',
                    items: ['Paku Beton', 'Paku Kayu', 'Paku Rivet', 'Paku Tembak']
                },
                {
                    title: 'Klem',
                    items: ['Klem Pipa', 'U Bolt', 'Clamp Stainless', 'Klem Selang']
                },
                {
                    title: 'Bracket',
                    items: ['Bracket L', 'Bracket Rak', 'Plat Sambung', 'Engsel Besi']
                },
                {
                    title: 'Tools',
                    items: ['Lem Besi', 'Sealant', 'Anti Karat', 'Threadlocker']
                }
            ],
            'elektronik': [{
                    title: 'Baut Mesin',
                    items: ['Baut M4', 'Baut M5', 'Baut M6', 'Baut M8']
                },
                {
                    title: 'Material',
                    items: ['Baja', 'Stainless 304', 'Galvanis', 'Kuningan']
                },
                {
                    title: 'Grade',
                    items: ['Grade 4.8', 'Grade 8.8', 'Grade 10.9', 'Grade 12.9']
                },
                {
                    title: 'Tools',
                    items: ['Box Baut', 'Rak Komponen', 'Label SKU', 'Organizer']
                }
            ],
            'kecantikan': [{
                    title: 'Abrasive',
                    items: ['Mata Gerinda', 'Amplas', 'Cutting Wheel', 'Flap Disc']
                },
                {
                    title: 'Safety',
                    items: ['Sarung Tangan', 'Kacamata Safety', 'Masker Kerja', 'Ear Plug']
                },
                {
                    title: 'Lem & Sealant',
                    items: ['Lem Besi', 'Lem PVC', 'Sealant', 'Epoxy']
                },
                {
                    title: 'Anti Karat',
                    items: ['WD Spray', 'Rust Remover', 'Grease', 'Lubricant']
                }
            ],
            'olahraga': [{
                    title: 'Perkakas Tangan',
                    items: ['Palu', 'Tang', 'Obeng', 'Kunci Inggris']
                },
                {
                    title: 'Perkakas Ukur',
                    items: ['Meteran', 'Jangka Sorong', 'Waterpass', 'Siku Ukur']
                },
                {
                    title: 'Power Tool',
                    items: ['Bor Listrik', 'Gerinda', 'Impact Driver', 'Blower']
                },
                {
                    title: 'Consumable',
                    items: ['Mata Bor', 'Mata Gerinda', 'Amplas', 'Kabel Ties']
                }
            ],
            'ibu-bayi': [{
                    title: 'Klem & Clamp',
                    items: ['Klem Pipa', 'Hose Clamp', 'U Bolt', 'Clamp Stainless']
                },
                {
                    title: 'Bracket',
                    items: ['Bracket L', 'Bracket U', 'Plat Sambung', 'Dudukan Rak']
                },
                {
                    title: 'Rivet',
                    items: ['Paku Rivet', 'Rivet Nut', 'Tang Rivet', 'Blind Rivet']
                },
                {
                    title: 'Accessories',
                    items: ['Cable Tie', 'Fisher', 'Spacer', 'Insert Nut']
                }
            ],
            'makanan-minuman': [{
                    title: 'Chemical',
                    items: ['Threadlocker', 'Sealant', 'Epoxy', 'Cleaner']
                },
                {
                    title: 'Lem',
                    items: ['Lem Besi', 'Lem Kayu', 'Lem PVC', 'Lem Serbaguna']
                },
                {
                    title: 'Pelumas',
                    items: ['Grease', 'Oli Serbaguna', 'Anti Karat', 'Contact Cleaner']
                },
                {
                    title: 'Packing',
                    items: ['Lakban', 'Stretch Film', 'Bubble Wrap', 'Kardus Sparepart']
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
            if (sidebar && panel && window.innerWidth < 1024 && sidebar.classList.contains('mobile-filter-open')) {
                if (e.target === sidebar) {
                    closeMobileFilter();
                }
            }
        });

        initMobileFilterDrawer();
        syncMobileFilterDrawerMode();

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

        if (carouselTotal > 0) {
            carouselGoTo(0);
        }

        // Init
        setMegaCategory('rumah-tangga');
        renderFilterVariants();
        renderProducts(products);
        initWishlistStatus();
    </script>
@endsection
