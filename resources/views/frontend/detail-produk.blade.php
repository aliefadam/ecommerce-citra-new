@extends('layouts.user')

@section('title', ($productData['name'] ?? 'Detail Produk') . ' - ' . ($appStoreName ?? 'Ecommerce Citra'))
@section('meta_description', \Illuminate\Support\Str::limit(trim(strip_tags((string) ($productData['description'] ?? ''))) ?: 'Beli '.($productData['name'] ?? 'produk').' secara online di '.($appStoreName ?? 'Ecommerce Citra').'.', 160))
@section('canonical', route('frontend.detail-produk', ['slug' => $productData['slug']]))
@section('og_image', $productData['image'] ?? '')
@section('og_type', 'product')

@push('structured_data')
    @php
        $displayPrice = $productData['isFlashSale'] ? $productData['flashSalePrice'] : $productData['price'];
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $productData['name'],
            'description' => trim(strip_tags((string) ($productData['description'] ?? ''))),
            'image' => $productData['images'],
            'sku' => $productData['sku'],
            'category' => $productData['categoryName'],
            'brand' => ['@type' => 'Brand', 'name' => $productData['storeName'] ?: ($appStoreName ?? 'Ecommerce Citra')],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('frontend.detail-produk', ['slug' => $productData['slug']]),
                'priceCurrency' => 'IDR',
                'price' => (string) $displayPrice,
                'availability' => $productData['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
        ];
        if ($productData['reviews'] > 0) {
            $productSchema['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $productData['rating'], 'reviewCount' => $productData['reviews']];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('style')
    <link href="{{ asset('vendor/tom-select/tom-select.min.css') }}" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter Variable', Inter, sans-serif;
        }

        .thumb-active {
            border-color: var(--ec-primary-700);
            box-shadow: 0 0 0 1px var(--ec-primary-700);
        }

        .product-detail-layout { display: grid; gap: 1.25rem; }
        .product-gallery { display: flex; min-width: 0; flex-direction: column; gap: .75rem; }
        .product-main-media { aspect-ratio: 1 / 1; border: 1px solid #e5e7eb; border-radius: .5rem; background: #f7f8fa; }
        .product-main-media img { object-fit: contain; }
        .product-summary { min-width: 0; }
        .product-buy-panel { border: 1px solid #d7dee8; border-radius: .5rem; background: #fff; padding: 1rem; box-shadow: 0 4px 14px rgb(15 45 86 / .05); }
        .product-main-price { color: #0b2b5b; font-size: 1.65rem; font-weight: 800; letter-spacing: -.035em; }
        .product-buy-primary { background: #1256a0; }
        .product-buy-primary:hover { background: #0b4380; }
        .product-seller-panel { margin-top: .75rem; border: 1px solid #d7dee8; border-radius: .5rem; background: #fff; padding: 1rem; }
        .product-seller-mark { display: grid; width: 2.75rem; height: 2.75rem; flex: 0 0 2.75rem; place-items: center; overflow: hidden; border: 1px solid #dbe3ed; border-radius: .5rem; background: #f7f9fc; color: #0b4380; font-weight: 800; }
        .product-seller-mark img { width: 100%; height: 100%; object-fit: contain; }
        .product-rfq { margin-top: .75rem; border: 1px solid #cad7e8; border-left: 3px solid #1256a0; border-radius: .5rem; background: #f7faff; padding: 1rem; }
        .product-spec-table { border-top: 1px solid #e5e7eb; }
        .product-spec-row { display: grid; grid-template-columns: minmax(7.5rem, 32%) minmax(0, 1fr); border-bottom: 1px solid #edf0f4; }
        .product-spec-row:nth-child(even) { background: #fafbfc; }
        .product-spec-row dt, .product-spec-row dd { padding: .7rem .85rem; font-size: .8125rem; }
        .product-spec-row dt { color: #64748b; }
        .product-spec-row dd { color: #172033; font-weight: 600; overflow-wrap: anywhere; }
        .product-section-tabs { position: sticky; top: 7.25rem; z-index: 20; background: rgb(255 255 255 / .97); backdrop-filter: blur(8px); }
        .product-section-tabs a { display: inline-flex; min-height: 2.9rem; align-items: center; border-bottom: 2px solid transparent; color: #64748b; font-size: .8125rem; font-weight: 650; white-space: nowrap; }
        .product-section-tabs a:hover, .product-section-tabs a:focus { border-color: #1256a0; color: #0b4380; }
        .product-copy { max-width: 52rem; color: #475569; font-size: .875rem; line-height: 1.8; }
        .store-product-media img { object-fit: contain; padding: .5rem; }

        @media (min-width: 768px) and (max-width: 1199px) {
            .product-detail-layout { grid-template-columns: minmax(0, 42fr) minmax(0, 58fr); align-items: start; }
            .product-buy-column { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; align-items: start; }
            .product-seller-panel, .product-rfq { margin-top: 0; }
        }

        @media (min-width: 1200px) {
            .product-detail-layout { grid-template-columns: minmax(0, 35fr) minmax(0, 40fr) minmax(16.5rem, 25fr); align-items: start; gap: 1.25rem; }
            .product-gallery { display: grid; grid-template-columns: 3.75rem minmax(0, 1fr); grid-template-areas: 'thumbs image'; align-items: start; }
            .product-main-media { grid-area: image; height: clamp(22rem, 36vw, 31rem); aspect-ratio: auto; }
            .product-thumbnails { grid-area: thumbs; max-height: clamp(22rem, 36vw, 31rem); flex-direction: column; overflow-x: hidden; overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; }
            .product-thumbnails .thumb-btn { width: 3.75rem; height: 3.75rem; }
            .product-buy-column { position: sticky; top: 8.25rem; }
        }

        @media (max-width: 767px) {
            .product-section-tabs { top: 0; }
            .product-spec-row { grid-template-columns: 7rem minmax(0, 1fr); }
        }

        .ts-wrapper.single .ts-control {
            min-height: 38px;
            align-items: center;
            justify-content: center;
            border-radius: 0.55rem;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            box-shadow: none;
            padding: 0.4rem 0.75rem;
            font-size: 0.8125rem;
            text-align: center;
            color: #334155;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .ts-wrapper.single.focus .ts-control {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .ts-wrapper .ts-control input {
            font-size: 0.875rem;
            color: #334155;
        }

        .ts-wrapper.single .ts-control .item { width: 100%; text-align: center; }
        .ts-wrapper.is-single-option .ts-control { cursor: default; background: #f8fafc; color: #475569; }
        .product-variant-grid { grid-template-columns: minmax(0, 1fr); gap: .85rem 1rem; margin-bottom: 1.25rem; }
        .product-variant-field { min-width: 0; margin: 0; }
        @media (min-width: 1024px) {
            .product-variant-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        .ts-wrapper .ts-dropdown {
            border: 1.5px solid #e2e8f0;
            border-radius: 0.875rem;
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            margin-top: 4px;
            z-index: 80;
        }

        .ts-wrapper.dropdown-active {
            z-index: 80;
        }

        .ts-wrapper .ts-dropdown .ts-dropdown-content {
            max-height: 220px;
        }

        .ts-wrapper .ts-dropdown .option {
            padding: 0.6rem 0.875rem;
            font-size: 0.875rem;
            color: #475569;
            transition: background 0.1s;
        }

        .ts-wrapper .ts-dropdown .option:hover,
        .ts-wrapper .ts-dropdown .option.active {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .ts-wrapper .ts-dropdown .option[data-disabled] {
            opacity: 0.4;
            text-decoration: line-through;
            cursor: not-allowed;
        }

        .ts-wrapper .ts-dropdown input.ts-input-search {
            margin: 8px;
            width: calc(100% - 16px);
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.8125rem;
            outline: none;
            color: #334155;
        }

        .ts-wrapper .ts-dropdown input.ts-input-search:focus {
            border-color: #3b82f6;
        }

        .ts-no-results {
            padding: 0.75rem;
            font-size: 0.875rem;
            color: #94a3b8;
            text-align: center;
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

        .mobile-sticky-actions {
            transition: transform 0.2s ease, opacity 0.2s ease;
            z-index: 30;
        }

        @media (max-width: 767px) {
            body.variant-select-open .mobile-sticky-actions {
                opacity: 0;
                pointer-events: none;
                transform: translateY(calc(100% + 84px));
            }
        }

        /* Mobile Variant Drawer */
        .variant-drawer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100vw;
            max-width: none;
            margin: 0;
            background: white;
            border-radius: 24px 24px 0 0;
            box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.15);
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
        }

        .variant-drawer.active {
            transform: translateY(0);
        }

        .variant-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 49;
        }

        .variant-drawer-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .drawer-handle {
            width: 40px;
            height: 4px;
            background: #cbd5e1;
            border-radius: 2px;
            margin: 12px auto 8px;
            cursor: grab;
        }

        .drawer-handle:active {
            cursor: grabbing;
        }

        .variant-drawer-content {
            overflow-y: auto;
            flex: 1;
        }

        .drawer-variant-select-wrap {
            width: min(52%, 180px);
            min-width: 140px;
        }

        .drawer-variant-select-wrap .ts-wrapper,
        .drawer-variant-select-wrap .ts-control {
            width: 100%;
        }

        @media (min-width: 768px) {
            .variant-drawer,
            .variant-drawer-overlay {
                display: none !important;
            }
        }
    </style>
@endsection
@section('content')
    @php
        $displayPrice = $productData['isFlashSale'] ? $productData['flashSalePrice'] : $productData['price'];
        $savingPercent =
            $productData['origPrice'] > 0 ? round((1 - $displayPrice / $productData['origPrice']) * 100) : 0;
        $variantGroups = collect($productData['variantGroups'] ?? []);
        $otherGroups = $variantGroups->values();
        $defaultOther = $otherGroups->mapWithKeys(fn($g) => [$g['key'] => $g['values'][0] ?? null])->all();
        $reviewItems = collect($productData['reviewItems'] ?? []);
        $reviewDistribution = collect($productData['reviewDistribution'] ?? []);
    @endphp
    <!-- Toast -->
    <div id="toast" class="fixed top-4 left-4 right-4 md:top-auto md:left-auto md:bottom-6 md:right-6 z-[9999] hidden">
        <div class="flex items-center gap-3 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <span id="toast-msg">Berhasil!</span>
        </div>
    </div>

    <!-- NAVBAR -->
    @include('partials.navbar-user')

    @if ($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ $errors->first() }}
            </div>
        </div>
    @endif

    <!-- BREADCRUMB -->
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500 flex-wrap">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="{{ route('frontend.kategori') }}"
                    class="hover:text-blue-600">{{ $productData['mainCategoryName'] ?: $productData['categoryName'] }}</a>
                @if (!empty($productData['mainCategoryName']) && $productData['mainCategoryName'] !== $productData['categoryName'])
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <a href="{{ route('frontend.kategori') }}" class="hover:text-blue-600">{{ $productData['categoryName'] }}</a>
                @endif
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-800 font-medium">{{ $productData['name'] }}</span>
            </nav>
        </div>
    </div>

    <!-- MAIN PRODUCT SECTION -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-8 pb-48 md:pb-8">
        <div class="product-detail-layout">

            <!-- LEFT: Gallery -->
            <div class="product-gallery">
                <!-- Main Image -->
                <div class="product-main-media relative overflow-hidden">
                    <img id="mainImg" src="{{ $productData['image'] }}" alt="{{ $productData['name'] }}"
                        class="main-img h-full w-full cursor-zoom-in object-contain p-3" onclick="openProductImageModal(this.src)" />
                    @if ($productData['isFlashSale'])
                        <div class="absolute top-3 left-3">
                            <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-md">-{{ max(0, $savingPercent) }}%</span>
                        </div>
                    @endif
                    <button onclick="toggleWishlist()" id="wishBtn"
                        class="absolute top-3 right-3 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full shadow-md flex items-center justify-center hover:bg-pink-50 transition-colors">
                        <svg id="wishIcon" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                    @if (count($productData['images'] ?? [$productData['image']]) > 1)
                        <button onclick="prevImg()"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-white/80 backdrop-blur-sm rounded-full shadow flex items-center justify-center hover:bg-white transition-colors">
                            <svg class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button onclick="nextImg()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-white/80 backdrop-blur-sm rounded-full shadow flex items-center justify-center hover:bg-white transition-colors">
                            <svg class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </button>
                    @endif
                </div>
                <!-- Thumbnails -->
                <div class="product-thumbnails flex gap-2 overflow-x-auto pb-1">
                    @foreach ($productData['images'] ?? [$productData['image']] as $idx => $thumb)
                        <button onclick="setImg({{ $idx }})"
                            class="thumb-btn flex-shrink-0 w-16 h-16 rounded-md overflow-hidden border bg-[#f7f8fa] transition-all {{ $idx === 0 ? 'thumb-active border-blue-700' : 'border-slate-200 hover:border-slate-400' }}">
                            <img src="{{ $thumb }}" alt="Tampilan {{ $idx + 1 }} {{ $productData['name'] }}" class="w-full h-full object-contain p-1" />
                        </button>
                    @endforeach
                </div>
                <p class="text-center text-[11px] text-slate-400 lg:col-start-2">Klik gambar untuk memperbesar</p>
            </div>

            <!-- RIGHT: Product Info -->
            <div class="product-summary">
                <!-- Brand & Status -->
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-[.12em] text-blue-800">{{ $productData['categoryName'] }} <span class="text-slate-300">&bull;</span> Industrial Supply</span>
                    <div class="flex items-center gap-2">
                        <span id="stockStatusBadge" class="text-xs font-medium flex items-center gap-1 rounded-full px-2.5 py-1 {{ ($productData['stock'] ?? 0) <= 0 ? 'bg-red-50 text-red-600' : (($productData['stock'] ?? 0) <= 5 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-700') }}">
                            <span id="stockStatusDot" class="w-2 h-2 rounded-full {{ ($productData['stock'] ?? 0) <= 0 ? 'bg-red-500' : (($productData['stock'] ?? 0) <= 5 ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                            <span id="stockStatusText">{{ ($productData['stock'] ?? 0) <= 0 ? 'Stok Habis' : (($productData['stock'] ?? 0) <= 5 ? 'Stok Terbatas' : 'Stok Tersedia') }}</span>
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

                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-950 mb-1 leading-tight tracking-[-.025em]">
                    {{ $productData['name'] }}</h1>

                @if (!empty($productData['storeName']))
                    <p class="text-xs sm:text-sm text-slate-500 mb-3">Dijual oleh <span class="font-semibold text-slate-700">{{ $productData['storeName'] }}</span></p>
                @endif

                <!-- Rating & Sales -->
                <div class="flex items-center gap-2 sm:gap-4 mb-4 flex-wrap">
                    @if ((int) $productData['reviews'] > 0)
                    <div class="flex items-center gap-1">
                        @php
                            $ratingVal = (float) $productData['rating'];
                            $fullStars = (int) floor($ratingVal);
                            $halfStar = ($ratingVal - $fullStars) >= 0.5;
                        @endphp
                        <div class="flex items-center gap-0.5">
                            @for ($s = 1; $s <= 5; $s++)
                                @if ($s <= $fullStars)
                                    <span class="text-yellow-400 text-xs sm:text-sm">★</span>
                                @elseif ($s == $fullStars + 1 && $halfStar)
                                    <span class="text-yellow-400 text-xs sm:text-sm">★</span>
                                @else
                                    <span class="text-slate-300 text-xs sm:text-sm">★</span>
                                @endif
                            @endfor
                        </div>
                        <span class="font-bold text-slate-800 text-xs sm:text-sm">{{ number_format($productData['rating'], 1) }}</span>
                        <span class="text-slate-500 text-xs">({{ number_format($productData['reviews']) }} ulasan)</span>
                    </div>
                    @else
                        <span class="text-xs text-slate-500">Belum ada ulasan</span>
                    @endif
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <span class="text-slate-600 text-xs"><span class="font-semibold text-slate-700">{{ number_format($productData['sold']) }}</span> terjual</span>
                    @if (!empty($productData['isRedeemProduct']))
                        <span class="text-slate-300 hidden sm:inline">|</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 border border-amber-200">
                            Redeem {{ number_format((int) ($productData['redeemPoints'] ?? 0), 0, ',', '.') }} point
                        </span>
                    @endif
                </div>

                <div class="border-y border-slate-200 py-4 mb-5">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span id="productMainPrice" class="product-main-price">Rp {{ number_format($displayPrice, 0, ',', '.') }}</span>
                        @if ($productData['isFlashSale'])
                            <span id="productMainOrigPrice" class="text-sm text-slate-400 line-through">Rp {{ number_format($productData['origPrice'], 0, ',', '.') }}</span>
                            <span class="rounded bg-red-50 px-2 py-1 text-xs font-bold text-red-600">-{{ max(0, $savingPercent) }}%</span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                        <span>SKU: <strong id="productSku" class="font-semibold text-slate-700">{{ $productData['sku'] ?: '-' }}</strong></span>
                        <span>Satuan: <strong class="font-semibold text-slate-700">pcs</strong></span>
                    </div>
                </div>

                <div class="product-variant-grid hidden md:grid">
                @foreach ($otherGroups as $group)
                    <div class="product-variant-field" data-variant-group="{{ $group['key'] }}">
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="text-xs sm:text-sm font-semibold text-slate-700">{{ $group['label'] }}:</span>
                            <span id="selected-{{ $group['key'] }}"
                                class="text-xs sm:text-sm font-bold text-blue-600">{{ $defaultOther[$group['key']] ?? '-' }}</span>
                        </div>
                        <select onchange="selectVariantValue(this, '{{ $group['key'] }}')"
                            data-group-key="{{ $group['key'] }}"
                            class="w-full">
                            @foreach ($group['values'] as $value)
                                <option value="{{ $value }}" @selected(($defaultOther[$group['key']] ?? null) === $value)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                </div>

                @if ($otherGroups->isNotEmpty())
                    <button type="button" onclick="openVariantDrawer('buy')" class="mb-3 flex w-full items-center justify-between rounded-md border border-slate-300 px-3 py-3 text-left md:hidden">
                        <span><span class="block text-xs font-bold text-slate-900">Pilih varian</span><span id="mobileVariantSummary" class="mt-1 block max-w-[16rem] truncate text-xs text-slate-500">{{ collect($defaultOther)->filter()->implode(' · ') }}</span></span>
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                @endif

                @if ($otherGroups->isNotEmpty())
                    <div class="hidden rounded-md border border-slate-200 bg-slate-50/70 md:block">
                        <div class="border-b border-slate-200 px-3 py-2 text-xs font-bold uppercase tracking-[.08em] text-slate-600">Spesifikasi varian</div>
                        <dl class="divide-y divide-slate-200/80">
                            @foreach ($otherGroups as $group)
                                <div class="grid grid-cols-[8rem_1fr] px-3 py-2 text-xs">
                                    <dt class="text-slate-500">{{ $group['label'] }}</dt>
                                    <dd class="font-semibold text-slate-800" data-variant-summary="{{ $group['key'] }}">{{ $defaultOther[$group['key']] ?? '-' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

            </div>

            <!-- RIGHT: Purchase & seller panel -->
            <aside class="product-buy-column">
                <div class="product-buy-panel hidden md:block">
                    <h2 class="text-sm font-bold text-slate-900">Atur pembelian</h2>
                    @if ($productData['isFlashSale'])
                        <div class="mt-1.5 flex items-center gap-1.5 text-[11px]">
                            <span class="font-semibold text-rose-600">Flash Sale</span>
                            <span class="text-slate-400">berakhir</span>
                            <span class="font-mono font-bold text-rose-600" id="saleTimer">00:00:00</span>
                        </div>
                    @endif

                    <div class="mt-3 flex items-center gap-2 text-xs text-slate-600">
                        <span id="stockStatusDotSide" class="h-2 w-2 rounded-full {{ ($productData['stock'] ?? 0) > 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        <span>Stok tersisa: <strong id="productStock" class="font-semibold text-slate-800">{{ number_format((int) $productData['stock']) }} pcs</strong></span>
                    </div>

                    <div class="mt-4">
                        <span class="mb-2 block text-xs font-semibold text-slate-700">Jumlah</span>
                        <div class="flex w-fit items-center overflow-hidden rounded-md border border-slate-200 bg-white">
                            <button onclick="changeQty(-1)" class="grid h-9 w-9 place-items-center text-slate-600 hover:bg-slate-50" aria-label="Kurangi jumlah">−</button>
                            <input id="qtyDisplay" type="number" min="1" max="{{ max(1, (int) ($productData['stock'] ?? 1)) }}" value="1"
                                inputmode="numeric" oninput="handleQtyInput(this)" onblur="commitQtyInput(this)"
                                class="h-9 w-12 border-x border-slate-200 text-center text-sm font-bold text-slate-800 outline-none" />
                            <button onclick="changeQty(1)" class="grid h-9 w-9 place-items-center text-slate-600 hover:bg-slate-50" aria-label="Tambah jumlah">+</button>
                        </div>
                        <p class="mt-1.5 text-[10px] text-slate-400">Minimum pembelian 1 pcs</p>
                    </div>

                    <div class="mt-4 flex items-end justify-between border-t border-slate-200 pt-3">
                        <span class="text-xs text-slate-500">Subtotal</span>
                        <strong id="productSubtotal" class="text-lg font-extrabold tracking-tight text-slate-950">Rp {{ number_format($displayPrice, 0, ',', '.') }}</strong>
                    </div>

                    <div class="mt-4 hidden flex-col gap-2 md:flex">
                        <button id="buyNowBtn" type="button" onclick="buyNow()"
                            class="product-buy-primary flex h-11 w-full items-center justify-center rounded-md text-sm font-bold text-white transition">
                            <span class="btn-label">Beli Sekarang</span>
                        </button>
                        <button id="addToCartBtn" onclick="addToCart()"
                            class="flex h-10 w-full items-center justify-center gap-2 rounded-md bg-blue-50 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/></svg>
                            <span class="btn-label">Tambah ke Keranjang</span>
                        </button>
                        @if (!empty($productData['isRedeemProduct']))
                            <button type="button" onclick="redeemNow()" class="flex h-10 w-full items-center justify-center rounded-md bg-amber-500 text-sm font-semibold text-white hover:bg-amber-600">Redeem Point</button>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 border-t border-slate-100 pt-3 text-[11px] font-medium text-slate-600">
                        <button onclick="toggleWishlist()" class="flex items-center justify-center gap-1.5 border-r border-slate-100 hover:text-rose-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.3 6.3a4.5 4.5 0 0 0 0 6.4L12 20.4l7.7-7.7a4.5 4.5 0 0 0-6.4-6.4L12 7.6l-1.3-1.3a4.5 4.5 0 0 0-6.4 0Z"/></svg>
                            Wishlist
                        </button>
                        <button onclick="shareProduct()" class="flex items-center justify-center gap-1.5 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.7 13.3a3 3 0 1 0 0-2.6m0 2.6 6.6 3.4m-6.6-6 6.6-3.4m0 0a3 3 0 1 0 5.4-2.6 3 3 0 0 0-5.4 2.6Zm0 9.4a3 3 0 1 0 5.4 2.6 3 3 0 0 0-5.4-2.6Z"/></svg>
                            Bagikan
                        </button>
                    </div>
                </div>

                <div class="product-seller-panel">
                    <div class="flex items-center gap-3">
                        <span class="product-seller-mark">
                            @if (!empty($productData['storeLogo']))
                                <img src="{{ $productData['storeLogo'] }}" alt="Logo {{ $productData['storeName'] }}">
                            @else
                                {{ strtoupper(mb_substr($productData['storeName'] ?: 'M', 0, 1)) }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $productData['storeName'] ?: 'Mitra industri' }}</p>
                            @if (!empty($productData['storeLegalName']) && $productData['storeLegalName'] !== $productData['storeName'])
                                <p class="mt-0.5 truncate text-[10px] text-slate-500">{{ $productData['storeLegalName'] }}</p>
                            @endif
                            <p class="mt-1 flex items-center gap-1 text-[10px] font-semibold text-blue-700"><span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span> Mitra pemasok BOQ</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button type="button" onclick="openSellerChat()" class="h-9 rounded-md border border-blue-700 text-xs font-semibold text-blue-800 hover:bg-blue-50">Chat Penjual</button>
                        <a href="{{ route('frontend.kategori') }}" class="flex h-9 items-center justify-center rounded-md border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat Produk</a>
                    </div>
                </div>

                <div class="product-rfq">
                    <p class="text-sm font-bold text-slate-950">Butuh jumlah besar?</p>
                    <p class="mt-1 text-[11px] leading-5 text-slate-600">Ajukan harga khusus untuk kebutuhan proyek, pembelian volume, atau permintaan perusahaan.</p>
                    <button type="button" onclick="requestQuotation()" class="mt-3 flex h-9 w-full items-center justify-center rounded-md border border-blue-700 bg-white text-xs font-bold text-blue-800 hover:bg-blue-50">Minta Penawaran</button>
                </div>
            </aside>
        </div>

        <!-- Product information navigation -->
        <div class="mt-10" id="detail-produk">
            <nav class="product-section-tabs -mx-4 mb-8 flex gap-6 overflow-x-auto border-y border-slate-200 px-4 sm:mx-0 sm:px-0" aria-label="Informasi produk">
                <a href="#detail-produk">Detail Produk</a>
                <a href="#spesifikasi">Spesifikasi</a>
                <a href="#daftar-varian">Varian</a>
                <a href="#ulasan">Ulasan</a>
                <a href="#diskusi">Diskusi</a>
            </nav>

            <section id="spesifikasi" class="scroll-mt-40 border-b border-slate-200 pb-9">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[.14em] text-blue-800">Data teknis</p>
                        <h2 class="mt-1 text-lg font-extrabold text-slate-950">Spesifikasi Produk</h2>
                    </div>
                    <span class="hidden text-xs text-slate-400 sm:block">SKU {{ $productData['sku'] ?: '-' }}</span>
                </div>
                <dl class="product-spec-table max-w-3xl">
                    <div class="product-spec-row"><dt>Nama Produk</dt><dd>{{ $productData['name'] }}</dd></div>
                    <div class="product-spec-row"><dt>Kategori</dt><dd>{{ $productData['categoryName'] }}</dd></div>
                    @foreach ($otherGroups as $group)
                        <div class="product-spec-row"><dt>{{ $group['label'] }}</dt><dd data-variant-spec="{{ $group['key'] }}">{{ $defaultOther[$group['key']] ?? '-' }}</dd></div>
                    @endforeach
                    <div class="product-spec-row"><dt>SKU</dt><dd id="productSpecSku">{{ $productData['sku'] ?: '-' }}</dd></div>
                    <div class="product-spec-row"><dt>Stok</dt><dd id="productSpecStock">{{ number_format((int) $productData['stock']) }} pcs</dd></div>
                </dl>
            </section>

            <section id="content-desc" class="scroll-mt-40 border-b border-slate-200 py-9">
                <h2 class="mb-4 text-lg font-extrabold text-slate-950">Tentang Produk</h2>
                <div class="product-copy prose prose-slate max-w-none">
                    {!! $productData['description'] ?: '<p>Belum ada deskripsi produk.</p>' !!}
                </div>
            </section>

            <section id="daftar-varian" class="scroll-mt-40 border-b border-slate-200 py-9">
                <div class="mb-4">
                    <p class="text-[11px] font-bold uppercase tracking-[.14em] text-blue-800">Katalog SKU</p>
                    <h2 class="mt-1 text-lg font-extrabold text-slate-950">Daftar Varian</h2>
                </div>
                <div class="overflow-x-auto border-y border-slate-200">
                    <table class="w-full min-w-[680px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                @foreach ($otherGroups as $group)<th class="px-4 py-3 font-semibold">{{ $group['label'] }}</th>@endforeach
                                <th class="px-4 py-3 font-semibold">SKU</th>
                                <th class="px-4 py-3 font-semibold">Stok</th>
                                <th class="px-4 py-3 font-semibold">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse (($productData['variantOptions'] ?? []) as $option)
                                <tr data-variant-row="{{ $option['id'] }}" class="transition-colors">
                                    @foreach ($otherGroups as $group)
                                        <td class="px-4 py-3 text-slate-700">{{ $option['attributes'][$group['key']] ?? '-' }}</td>
                                    @endforeach
                                    <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $option['label'] ?? '-' }}</td>
                                    <td class="px-4 py-3"><span class="{{ ((int) ($option['stock'] ?? 0)) > 0 ? 'text-emerald-700' : 'text-red-600' }}">{{ number_format((int) ($option['stock'] ?? 0)) }}</span></td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">Rp {{ number_format($option['displayPrice'] ?? $option['price'] ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ max(3, $otherGroups->count() + 3) }}" class="px-4 py-4 text-slate-500">Belum ada data varian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="ulasan" class="scroll-mt-40 border-b border-slate-200 py-9">
                <h2 class="mb-6 text-lg font-extrabold text-slate-950">Ulasan Produk</h2>
                <div class="grid md:grid-cols-3 gap-6 mb-8">
                    <div class="text-center">
                        <div class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-slate-800 mb-1">{{ number_format($productData['rating'], 1) }}</div>
                        <div class="text-yellow-400 text-lg sm:text-2xl mb-2">★★★★★</div>
                        <p class="text-slate-500 text-xs sm:text-sm">dari {{ number_format($productData['reviews']) }} ulasan</p>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        @foreach ($reviewDistribution as $dist)
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-600 w-8">{{ $dist['star'] }} ★</span>
                                <div class="flex-1 review-bar">
                                    <div class="review-fill" style="width:{{ $dist['percent'] }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500 w-8">{{ $dist['percent'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="space-y-5">
                    <div class="border-b border-slate-100 pb-5" id="reviews-container">
                    </div>
                </div>
            </section>

            <section id="diskusi" class="scroll-mt-40 py-9">
                <div class="flex flex-col justify-between gap-4 border-l-2 border-blue-700 pl-4 sm:flex-row sm:items-center">
                    <div><h2 class="text-base font-bold text-slate-950">Perlu konfirmasi teknis?</h2><p class="mt-1 text-sm text-slate-500">Diskusikan spesifikasi, kompatibilitas, atau kebutuhan proyek dengan penjual.</p></div>
                    <button type="button" onclick="openSellerChat()" class="h-10 shrink-0 rounded-md border border-blue-700 px-4 text-sm font-semibold text-blue-800 hover:bg-blue-50">Mulai Diskusi</button>
                </div>
            </section>
        </div>

        @if (!empty($sameStoreProductsJson))
            <section class="mt-10 border-t border-slate-200 pt-8 sm:mt-12">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[.14em] text-blue-800">{{ $productData['storeName'] ?: 'Mitra industri' }}</p>
                        <h2 class="mt-1 text-lg font-extrabold text-slate-950">Produk Lain dari Toko Ini</h2>
                    </div>
                </div>
                <div class="flex snap-x gap-3 overflow-x-auto pb-3 sm:grid sm:grid-cols-3 sm:overflow-visible md:grid-cols-5">
                    @foreach ($sameStoreProductsJson as $sp)
                        <article class="store-product-card group min-w-[168px] snap-start sm:min-w-0">
                            <div class="store-product-media"><a href="{{ $sp['url'] }}" class="block h-full"><img src="{{ $sp['image'] }}" alt="{{ $sp['name'] }}" loading="lazy"></a></div>
                            <div class="store-product-body">
                                <a href="{{ $sp['url'] }}" class="store-product-name line-clamp-2 hover:text-blue-700">{{ $sp['name'] }}</a>
                                <p class="store-product-variant truncate">{{ $sp['variant'] ?: 'Produk industri' }}</p>
                                <p class="store-product-price">Rp {{ number_format($sp['price'], 0, ',', '.') }}</p>
                                <p class="store-product-seller"><span>{{ $sp['storeName'] ?: 'Mitra industri' }}</span></p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if (!empty($recentlyViewedProductsJson))
            <div class="mt-10 sm:mt-12">
                <div class="flex items-center justify-between mb-4 sm:mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 sm:h-7 bg-slate-400 rounded-full"></div>
                        <h2 class="text-base sm:text-xl font-bold text-slate-800">Terakhir Dilihat</h2>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach ($recentlyViewedProductsJson as $rv)
                        <article class="store-product-card group">
                            <div class="store-product-media">
                                <a href="{{ $rv['url'] }}" class="block h-full" aria-label="Lihat {{ $rv['name'] }}">
                                    <img src="{{ $rv['image'] }}" alt="{{ $rv['name'] }}" loading="lazy" />
                                </a>
                            </div>
                            <div class="store-product-body">
                                <a href="{{ $rv['url'] }}" class="store-product-name line-clamp-2 hover:text-blue-700">{{ $rv['name'] }}</a>
                                <p class="store-product-variant truncate">{{ $rv['variant'] ?: 'Produk industri' }}</p>
                                <p class="store-product-price">Rp {{ number_format($rv['price'], 0, ',', '.') }}</p>
                                <p class="store-product-seller"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h16v-9.5M3 4h18l-1.5 6a2.5 2.5 0 0 1-4.5 1.1 2.5 2.5 0 0 1-4.5 0A2.5 2.5 0 0 1 6 10L4.5 4M9 20v-5h6v5"/></svg><span>{{ $rv['storeName'] ?: 'Mitra industri' }}</span></p>
                                <div class="store-product-meta">
                                    <span class="store-product-rating"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.7 2.83 5.73 6.32.92-4.58 4.46 1.08 6.3L12 17.14l-5.65 2.97 1.08-6.3-4.58-4.46 6.32-.92L12 2.7Z"/></svg>{{ number_format((float) $rv['rating'], 1) }} <span class="font-normal text-slate-400">({{ number_format((int) $rv['reviews']) }})</span></span>
                                    <span>Terjual {{ number_format((int) $rv['sold']) }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- PRODUK REKOMENDASI -->
        <div class="mt-10 sm:mt-12">
            <div class="flex items-center justify-between mb-4 sm:mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-6 sm:h-7 bg-blue-700 rounded-full"></div>
                    <h2 class="text-base sm:text-xl font-bold text-slate-800">Produk Serupa</h2>
                </div>
                <a href="{{ route('frontend.kategori') }}"
                    class="text-blue-600 hover:text-blue-700 font-semibold text-sm flex items-center gap-1 transition-colors">
                    Lihat Semua <i class="ri-arrow-right-s-line text-base"></i>
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @forelse ($relatedProductsJson as $rp)
                    <article class="store-product-card group">
                        <div class="store-product-media">
                            <a href="{{ url('/detail-produk/' . $rp['slug']) }}" class="block h-full" aria-label="Lihat {{ $rp['name'] }}">
                                <img src="{{ $rp['image'] }}" alt="{{ $rp['name'] }}" loading="lazy" />
                            </a>
                            @if ($rp['isFlashSale'] && $rp['originalPrice'] > $rp['price'])
                                @php $disc = round((1 - $rp['price'] / $rp['originalPrice']) * 100); @endphp
                                <span class="absolute left-2.5 top-2.5 rounded-md bg-rose-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">-{{ $disc }}%</span>
                            @endif
                        </div>
                        <div class="store-product-body">
                            <a href="{{ url('/detail-produk/' . $rp['slug']) }}" class="store-product-name line-clamp-2 hover:text-blue-700">{{ $rp['name'] }}</a>
                            <p class="store-product-variant truncate">{{ $rp['variant'] ?: 'Produk industri' }}</p>
                            <p class="store-product-price">Rp {{ number_format($rp['price'], 0, ',', '.') }}</p>
                            <p class="store-product-seller"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h16v-9.5M3 4h18l-1.5 6a2.5 2.5 0 0 1-4.5 1.1 2.5 2.5 0 0 1-4.5 0A2.5 2.5 0 0 1 6 10L4.5 4M9 20v-5h6v5"/></svg><span>{{ $rp['storeName'] ?: 'Mitra industri' }}</span></p>
                            <div class="store-product-meta">
                                <span class="store-product-rating"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.7 2.83 5.73 6.32.92-4.58 4.46 1.08 6.3L12 17.14l-5.65 2.97 1.08-6.3-4.58-4.46 6.32-.92L12 2.7Z"/></svg>{{ number_format((float) $rp['rating'], 1) }} <span class="font-normal text-slate-400">({{ number_format((int) $rp['reviews']) }})</span></span>
                                <span>Terjual {{ number_format((int) $rp['sold']) }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-10 text-slate-400 text-sm">Belum ada produk rekomendasi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- STICKY BOTTOM BAR (Mobile) -->
    <div id="mobileStickyActions" class="mobile-sticky-actions fixed inset-x-0 bottom-0 md:hidden bg-white border-t border-slate-200 px-3 pt-2.5 pb-[max(.625rem,env(safe-area-inset-bottom))] flex flex-col gap-2 shadow-[0_-4px_16px_rgb(15_23_42/.08)]">
        <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                <div class="font-semibold text-slate-800" id="mobileStickyPrice">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                <div id="mobileStickyStock">Stok {{ number_format((int) ($productData['stock'] ?? 0)) }} item</div>
            </div>
            <div id="mobileStickyStatus" class="text-right font-semibold {{ ($productData['stock'] ?? 0) <= 0 ? 'text-red-600' : (($productData['stock'] ?? 0) <= 5 ? 'text-amber-600' : 'text-blue-600') }}">
                {{ ($productData['stock'] ?? 0) <= 0 ? 'Stok habis' : (($productData['stock'] ?? 0) <= 5 ? 'Stok terbatas' : 'Siap dibeli') }}
            </div>
        </div>
        <div class="flex gap-2">
            <button id="mobileAddToCartBtn" onclick="openVariantDrawer('cart')"
                class="flex-1 bg-blue-50 border border-blue-300 text-blue-700 font-semibold py-2.5 rounded-xl text-sm flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="btn-label">Keranjang</span>
            </button>
            <button id="mobileBuyNowBtn" type="button" onclick="openVariantDrawer('buy')"
                class="flex-1 bg-blue-600 text-white font-semibold py-2.5 rounded-xl text-sm flex items-center justify-center gap-1.5 shadow-sm shadow-blue-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="btn-label">Beli Sekarang</span>
            </button>
        </div>
        @if (!empty($productData['isRedeemProduct']))
            <button type="button" onclick="openVariantDrawer('redeem')"
                class="w-full bg-amber-500 text-white font-semibold py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 shadow-sm shadow-amber-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10V6m0 12v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Redeem dengan Poin
            </button>
        @endif
    </div>

    <!-- Mobile Variant Drawer Overlay -->
    <div id="variantDrawerOverlay" class="variant-drawer-overlay" onclick="closeVariantDrawer()"></div>

    <!-- Mobile Variant Drawer -->
    <div id="variantDrawer" class="variant-drawer">
        <div class="drawer-handle" id="drawerHandle"></div>
        <div class="px-4 pb-3 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800">Pilih Varian</h3>
                <button type="button" onclick="closeVariantDrawer()" aria-label="Tutup pilihan varian" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="variant-drawer-content px-4 py-4">
            <!-- Product Info -->
            <div class="flex gap-3 mb-4 pb-4 border-b border-slate-100">
                <img id="drawerProductImage" src="{{ $productData['image'] }}" alt="{{ $productData['name'] }}" class="w-20 h-20 rounded-xl object-cover border border-slate-200">
                <div class="flex-1">
                    <div id="drawerProductPrice" class="text-xl font-bold text-blue-600 mb-1">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                    <div id="drawerProductStock" class="text-xs text-slate-500">Stok: {{ number_format((int) ($productData['stock'] ?? 0)) }} item</div>
                </div>
            </div>

            <!-- Variants -->
            @foreach ($otherGroups as $group)
                <div class="mb-5" data-variant-group-drawer="{{ $group['key'] }}">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="text-sm font-semibold text-slate-700">{{ $group['label'] }}:</span>
                        <span id="selected-drawer-{{ $group['key'] }}"
                            class="text-sm font-bold text-blue-600">{{ $defaultOther[$group['key']] ?? '-' }}</span>
                    </div>
                    <div class="drawer-variant-select-wrap">
                        <select onchange="selectVariantValueDrawer(this, '{{ $group['key'] }}')"
                            data-group-key-drawer="{{ $group['key'] }}"
                            class="w-full drawer-variant-select">
                            @foreach ($group['values'] as $value)
                                <option value="{{ $value }}" @selected(($defaultOther[$group['key']] ?? null) === $value)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach

            <!-- Quantity -->
            <div class="mb-5">
                <span class="text-sm font-semibold text-slate-700 block mb-2">Jumlah</span>
                <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden w-40">
                    <button onclick="changeQtyDrawer(-1)"
                        class="w-10 py-2 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-colors">−</button>
                    <input id="qtyDisplayDrawer" type="number" min="1" max="{{ max(1, (int) ($productData['stock'] ?? 1)) }}" value="1"
                        inputmode="numeric" oninput="handleQtyInput(this, true)" onblur="commitQtyInput(this, true)"
                        class="w-20 px-3 py-2 font-bold text-slate-800 text-center border-x-2 border-slate-200 text-sm focus:outline-none focus:bg-blue-50" />
                    <button onclick="changeQtyDrawer(1)"
                        class="w-10 py-2 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-colors">+</button>
                </div>
            </div>
        </div>

        <!-- Drawer Actions -->
        <div class="border-t border-slate-100 bg-white px-4 pt-3 pb-[max(.75rem,env(safe-area-inset-bottom))]">
            <button id="drawerActionBtn" onclick="executeDrawerAction()"
                class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl text-sm flex items-center justify-center gap-2 shadow-sm shadow-blue-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span id="drawerActionLabel">Beli Sekarang</span>
            </button>
        </div>
    </div>

    <form id="buyNowForm" method="POST" action="{{ route('frontend.checkout.buy-now') }}" class="hidden">
        @csrf
        <input type="hidden" name="product_variant_id" id="buyNowVariantId" value="{{ $productData['productVariantId'] ?? 0 }}">
        <input type="hidden" name="quantity" id="buyNowQty" value="1">
    </form>

    <form id="redeemNowForm" method="POST" action="{{ route('frontend.redeem.prepare-checkout') }}" class="hidden">
        @csrf
        <input type="hidden" name="product_variant_id" id="redeemNowVariantId" value="{{ $productData['productVariantId'] ?? 0 }}">
        <input type="hidden" name="quantity" id="redeemNowQty" value="1">
    </form>

    <div id="reviewImageModal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/70 p-4">
        <div class="relative max-w-3xl w-full">
            <button type="button" onclick="closeReviewImageModal()"
                class="absolute -top-10 right-0 text-white text-sm font-semibold">Tutup</button>
            <img id="reviewImageModalImg" src="" alt="Review Image"
                class="w-full max-h-[80vh] object-contain rounded-xl bg-white" />
        </div>
    </div>

    <div id="productImageModal" class="fixed inset-0 z-[100000] hidden items-center justify-center bg-slate-950/90 p-4 backdrop-blur-sm" onclick="closeProductImageModal(event)">
        <button type="button" onclick="closeProductImageModal()" class="absolute right-4 top-4 grid h-10 w-10 place-items-center rounded-full bg-white/10 text-2xl text-white hover:bg-white/20" aria-label="Tutup gambar">&times;</button>
        <img id="productImageModalImg" src="" alt="{{ $productData['name'] }}" class="max-h-[90vh] max-w-[92vw] rounded-lg bg-white object-contain shadow-2xl" />
    </div>
@endsection

@section('script')
    <script src="{{ asset('vendor/tom-select/tom-select.complete.min.js') }}"></script>
    <script>
        const productData = @json($productData);
        const isAuthenticated = @json(auth()->check());
        const loginUrl = @json(route('login'));
        const cartStoreUrl = @json(route('frontend.cart.store'));
        const wishlistToggleUrl = @json(route('frontend.wishlist.toggle'));
        const sellerSupportUrl = @json($appStoreSettings['social_whatsapp'] ?? '');
        const csrfToken = @json(csrf_token());
        const pendingAuthActionKey = 'ec_pending_auth_action';
        const images = (productData.images && productData.images.length ? productData.images : [productData.image]);
        let currentImg = 0;
        let activeVariantId = Number(productData.productVariantId || 0);
        let qty = 1;
        let qtyDrawer = 1;
        let drawerAvailableStock = Number(productData.stock || 0);
        let isWishlisted = Boolean(productData.isWishlisted);
        const variantSelectInstances = new Map();
        const variantSelectDrawerInstances = new Map();
        let drawerAction = 'buy'; // 'buy', 'cart', or 'redeem'
        let touchStartY = 0;
        let touchCurrentY = 0;
        let isDragging = false;

        function setImg(i) {
            if (!images.length || i < 0 || i >= images.length) return;
            currentImg = i;
            const img = document.getElementById('mainImg');
            img.style.opacity = 0;
            setTimeout(() => {
                img.src = images[i];
                img.style.opacity = 1;
            }, 150);
            document.querySelectorAll('.thumb-btn').forEach((b, idx) => {
                b.classList.toggle('thumb-active', idx === i);
                b.classList.toggle('border-blue-700', idx === i);
                b.classList.toggle('border-slate-200', idx !== i);
            });
        }

        function prevImg() {
            setImg((currentImg - 1 + images.length) % images.length);
        }

        function nextImg() {
            setImg((currentImg + 1) % images.length);
        }

        function openProductImageModal(src = '') {
            const modal = document.getElementById('productImageModal');
            const modalImg = document.getElementById('productImageModalImg');
            const mainImg = document.getElementById('mainImg');
            if (!modal || !modalImg) return;
            modalImg.src = src || mainImg?.src || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeProductImageModal(event = null) {
            const modal = document.getElementById('productImageModal');
            if (!modal || (event && event.target !== modal)) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function getMaxQty(isDrawer = false) {
            return Math.max(1, Number((isDrawer ? drawerAvailableStock : productData.stock) || 1));
        }

        function clampQty(value, isDrawer = false) {
            const parsed = parseInt(value, 10);
            if (!Number.isFinite(parsed)) return 1;
            return Math.max(1, Math.min(getMaxQty(isDrawer), parsed));
        }

        function updateQtyInput(isDrawer = false) {
            const input = document.getElementById(isDrawer ? 'qtyDisplayDrawer' : 'qtyDisplay');
            if (!input) return;
            input.max = String(getMaxQty(isDrawer));
            input.value = String(isDrawer ? qtyDrawer : qty);
            if (!isDrawer) updateSubtotal();
        }

        function updateSubtotal() {
            const selected = (productData.variantOptions || []).find((option) => Number(option.id) === Number(activeVariantId));
            const unitPrice = Number(selected?.displayPrice || selected?.price || productData.flashSalePrice || productData.price || 0);
            const subtotal = document.getElementById('productSubtotal');
            if (subtotal) subtotal.textContent = formatRupiah(unitPrice * Math.max(1, qty));
        }

        function setQtyValue(value, isDrawer = false) {
            if (isDrawer) {
                qtyDrawer = clampQty(value, true);
            } else {
                qty = clampQty(value);
            }
            updateQtyInput(isDrawer);
        }

        function handleQtyInput(input, isDrawer = false) {
            if (!input.value) return;
            setQtyValue(input.value, isDrawer);
        }

        function commitQtyInput(input, isDrawer = false) {
            setQtyValue(input.value, isDrawer);
        }

        function syncMainQtyInput() {
            const input = document.getElementById('qtyDisplay');
            setQtyValue(input?.value || qty);
        }

        function changeQty(d) {
            setQtyValue(qty + d);
        }

        function changeQtyDrawer(d) {
            setQtyValue(qtyDrawer + d, true);
        }

        function openVariantDrawer(action) {
            if (Number(productData.stock || 0) <= 0) {
                showToast('Stok produk ini sedang habis.');
                return;
            }

            drawerAction = action;
            const drawer = document.getElementById('variantDrawer');
            const overlay = document.getElementById('variantDrawerOverlay');
            const actionBtn = document.getElementById('drawerActionBtn');
            const actionLabel = document.getElementById('drawerActionLabel');

            // Sync drawer quantity with main quantity
            drawerAvailableStock = Number(productData.stock || 0);
            qtyDrawer = qty;
            updateQtyInput(true);

            // Sync variant selections from desktop to drawer
            syncDesktopToDrawer();

            // Update button label based on action
            if (action === 'cart') {
                actionLabel.textContent = 'Tambah ke Keranjang';
                actionBtn.className = 'w-full bg-blue-50 border-2 border-blue-300 text-blue-700 font-semibold py-3 rounded-xl text-sm flex items-center justify-center gap-2';
            } else if (action === 'redeem') {
                actionLabel.textContent = 'Redeem dengan Poin';
                actionBtn.className = 'w-full bg-amber-500 text-white font-semibold py-3 rounded-xl text-sm flex items-center justify-center gap-2 shadow-sm shadow-amber-100';
            } else {
                actionLabel.textContent = 'Beli Sekarang';
                actionBtn.className = 'w-full bg-blue-600 text-white font-semibold py-3 rounded-xl text-sm flex items-center justify-center gap-2 shadow-sm shadow-blue-100';
            }

            drawer.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            if (window.Tawk_API && typeof window.Tawk_API.hideWidget === 'function') {
                window.Tawk_API.hideWidget();
            }
        }

        function closeVariantDrawer() {
            const drawer = document.getElementById('variantDrawer');
            const overlay = document.getElementById('variantDrawerOverlay');

            drawer.classList.remove('active');
            drawer.style.transform = '';
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            if (window.Tawk_API && typeof window.Tawk_API.showWidget === 'function') {
                window.Tawk_API.showWidget();
            }

            // Sync drawer selections back to desktop
            syncDrawerToDesktop();
        }

        function syncDesktopToDrawer() {
            document.querySelectorAll('[data-variant-group]').forEach((group) => {
                const key = group.getAttribute('data-variant-group');
                const desktopSelect = group.querySelector('select[data-group-key]');
                const drawerGroup = document.querySelector(`[data-variant-group-drawer="${key}"]`);

                if (desktopSelect && drawerGroup) {
                    const drawerSelect = drawerGroup.querySelector('select[data-group-key-drawer]');
                    if (drawerSelect) {
                        drawerSelect.value = desktopSelect.value;
                        const label = document.getElementById('selected-drawer-' + key);
                        if (label) label.textContent = desktopSelect.value;
                        refreshVariantSelectDrawerControl(drawerSelect);
                    }
                }
            });
            applySelectedVariantDataDrawer();
        }

        function syncDrawerToDesktop() {
            document.querySelectorAll('[data-variant-group-drawer]').forEach((group) => {
                const key = group.getAttribute('data-variant-group-drawer');
                const drawerSelect = group.querySelector('select[data-group-key-drawer]');
                const desktopGroup = document.querySelector(`[data-variant-group="${key}"]`);

                if (drawerSelect && desktopGroup) {
                    const desktopSelect = desktopGroup.querySelector('select[data-group-key]');
                    if (desktopSelect) {
                        desktopSelect.value = drawerSelect.value;
                        const label = document.getElementById('selected-' + key);
                        if (label) label.textContent = drawerSelect.value;
                        refreshVariantSelectControl(desktopSelect);
                    }
                }
            });

            // Sync quantity back
            qty = qtyDrawer;
            updateQtyInput();

            applySelectedVariantData();
        }

        function executeDrawerAction() {
            closeVariantDrawer();

            if (drawerAction === 'cart') {
                addToCart();
            } else if (drawerAction === 'redeem') {
                redeemNow();
            } else {
                buyNow();
            }
        }

        function selectVariantValueDrawer(select, groupKey) {
            const value = String(select?.value || '');
            const selections = getSelectedVariantSelectionsDrawer();
            const selectedVariant = findBestVariantForSelection(groupKey, value, selections);
            applySelectedVariantDataDrawer(selectedVariant);
        }

        function applySelectedVariantDataDrawer(selectedVariant = null) {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            if (!options.length) return;

            if (!selectedVariant) {
                const selections = getSelectedVariantSelectionsDrawer();
                selectedVariant = options.find((opt) => variantMatchesSelections(opt, selections)) || options[0];
            }
            if (!selectedVariant) return;

            syncVariantControlsToOption(selectedVariant, true);
            activeVariantId = Number(selectedVariant.id || activeVariantId || 0);

            const displayPrice = Number(selectedVariant.displayPrice || selectedVariant.price || 0);
            const drawerPrice = document.getElementById('drawerProductPrice');
            if (drawerPrice) drawerPrice.textContent = formatRupiah(displayPrice);

            const drawerStockEl = document.getElementById('drawerProductStock');
            if (drawerStockEl) drawerStockEl.textContent = `Stok: ${Number(selectedVariant.stock || 0)} item`;

            const drawerImage = document.getElementById('drawerProductImage');
            if (drawerImage && selectedVariant.image) drawerImage.src = selectedVariant.image;

            drawerAvailableStock = Number(selectedVariant.stock || 0);
            qtyDrawer = clampQty(qtyDrawer, true);
            updateQtyInput(true);
        }

        function getSelectedVariantSelectionsDrawer() {
            const selections = {};
            document.querySelectorAll('[data-variant-group-drawer]').forEach((group) => {
                const key = group.getAttribute('data-variant-group-drawer');
                const select = group.querySelector('select[data-group-key-drawer]');
                if (!key || !select) return;
                selections[key] = String(select.value || '');
            });
            return selections;
        }

        function syncVariantAvailabilityDrawer() {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            const groups = Array.isArray(productData.variantGroups) ? productData.variantGroups : [];
            const currentSelections = getSelectedVariantSelectionsDrawer();

            groups.forEach((group) => {
                const groupEl = document.querySelector(`[data-variant-group-drawer="${group.key}"]`);
                if (!groupEl) return;

                const select = groupEl.querySelector('select[data-group-key-drawer]');
                if (!select) return;

                const optionsEls = Array.from(select.options);
                let hasSelectedAvailable = false;

                optionsEls.forEach((optionEl) => {
                    const testSelections = {
                        ...currentSelections,
                        [group.key]: String(optionEl.value || ''),
                    };
                    const available = options.some((option) => variantMatchesSelections(option, testSelections));

                    optionEl.disabled = !available;
                    optionEl.hidden = !available;

                    if (String(select.value || '') === String(optionEl.value || '') && available) {
                        hasSelectedAvailable = true;
                    }
                });

                if (!hasSelectedAvailable) {
                    const firstAvailable = optionsEls.find((optionEl) => !optionEl.disabled);
                    if (!firstAvailable) return;
                    select.value = String(firstAvailable.value || '');
                }

                const label = document.getElementById('selected-drawer-' + group.key);
                if (label) label.textContent = String(select.value || '-');

                refreshVariantSelectDrawerControl(select);
            });
        }

        function initializeVariantSelectsDrawer() {
            document.querySelectorAll('select[data-group-key-drawer]').forEach((select) => {
                const groupKey = select.getAttribute('data-group-key-drawer');
                if (!groupKey) return;

                if (select.tomselect) {
                    variantSelectDrawerInstances.set(groupKey, select.tomselect);
                    return;
                }

                const instance = new TomSelect(select, {
                    create: false,
                    controlInput: null,
                    maxItems: 1,
                    closeAfterSelect: true,
                    allowEmptyOption: false,
                    copyClassesToDropdown: false,
                    hideSelected: true,
                    searchField: [],
                    render: {
                        no_results(data, escape) {
                            return `<div class="ts-no-results">Tidak ditemukan: "${escape(data.input)}"</div>`;
                        },
                    },
                    onChange() {
                        selectVariantValueDrawer(select, groupKey);
                    },
                });

                if (Array.from(select.options).filter((option) => !option.disabled).length <= 1) {
                    instance.lock();
                    instance.wrapper?.classList.add('is-single-option');
                }

                variantSelectDrawerInstances.set(groupKey, instance);
            });
        }

        function refreshVariantSelectDrawerControl(select) {
            const groupKey = select?.getAttribute('data-group-key-drawer');
            const instance = (groupKey && variantSelectDrawerInstances.get(groupKey)) || select?.tomselect;
            if (!instance) return;

            instance.clearCache();
            instance.sync();
            instance.refreshOptions(false);
            instance.inputState();
        }

        // Touch/Drag handlers for drawer
        function initDrawerDragHandlers() {
            const drawer = document.getElementById('variantDrawer');
            const handle = document.getElementById('drawerHandle');

            if (!drawer || !handle) return;

            const startDrag = (e) => {
                isDragging = true;
                touchStartY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
                touchCurrentY = touchStartY;
                drawer.style.transition = 'none';
            };

            const onDrag = (e) => {
                if (!isDragging) return;

                touchCurrentY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
                const deltaY = touchCurrentY - touchStartY;
                if (deltaY > 0) {
                    drawer.style.transform = `translateY(${deltaY}px)`;
                }
            };

            const endDrag = () => {
                if (!isDragging) return;

                isDragging = false;
                drawer.style.transition = '';

                const deltaY = touchCurrentY - touchStartY;

                if (deltaY > 100) {
                    closeVariantDrawer();
                } else {
                    drawer.style.transform = 'translateY(0)';
                }
            };

            // Mouse events
            handle.addEventListener('mousedown', startDrag);
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', endDrag);

            // Touch events
            handle.addEventListener('touchstart', startDrag, { passive: true });
            document.addEventListener('touchmove', onDrag, { passive: true });
            document.addEventListener('touchend', endDrag);
        }

        function updateStockUI() {
            const stock = Number(productData.stock || 0);
            const statusText = document.getElementById('stockStatusText');
            const statusDot = document.getElementById('stockStatusDot');
            const sideStatusDot = document.getElementById('stockStatusDotSide');
            const statusBadge = document.getElementById('stockStatusBadge');
            const stockEl = document.getElementById('productStock');
            const mobileStock = document.getElementById('mobileStickyStock');
            const mobileStatus = document.getElementById('mobileStickyStatus');
            const addToCartBtn = document.getElementById('addToCartBtn');
            const buyNowBtn = document.getElementById('buyNowBtn');
            const mobileAddToCartBtn = document.getElementById('mobileAddToCartBtn');
            const mobileBuyNowBtn = document.getElementById('mobileBuyNowBtn');

            let label = 'Stok Tersedia';
            let badgeClass = ['bg-emerald-50', 'text-emerald-700'];
            let dotClass = 'bg-emerald-500';

            if (stock <= 0) {
                label = 'Stok Habis';
                badgeClass = ['bg-red-50', 'text-red-600'];
                dotClass = 'bg-red-500';
            } else if (stock <= 5) {
                label = 'Stok Terbatas';
                badgeClass = ['bg-amber-50', 'text-amber-600'];
                dotClass = 'bg-amber-500';
            }

            if (statusText) statusText.textContent = label;
            if (statusDot) statusDot.className = `w-2 h-2 rounded-full ${dotClass}`;
            if (sideStatusDot) sideStatusDot.className = `h-2 w-2 rounded-full ${stock > 0 ? 'bg-emerald-500' : 'bg-rose-500'}`;
            if (statusBadge) statusBadge.className = `text-xs font-medium flex items-center gap-1 rounded-full px-2.5 py-1 ${badgeClass.join(' ')}`;
            if (stockEl) stockEl.textContent = `${stock} pcs`;
            if (mobileStock) mobileStock.textContent = `Stok ${stock} item`;
            if (mobileStatus) {
                mobileStatus.textContent = stock <= 0 ? 'Stok habis' : (stock <= 5 ? 'Stok terbatas' : 'Siap dibeli');
                mobileStatus.className = `text-right font-semibold ${stock <= 0 ? 'text-red-600' : (stock <= 5 ? 'text-amber-600' : 'text-blue-600')}`;
            }

            [addToCartBtn, buyNowBtn, mobileAddToCartBtn, mobileBuyNowBtn].forEach((btn) => {
                if (!btn) return;
                btn.disabled = stock <= 0;
                btn.classList.toggle('opacity-50', stock <= 0);
                btn.classList.toggle('cursor-not-allowed', stock <= 0);
            });

            const desktopCartLabel = addToCartBtn?.querySelector('.btn-label');
            const desktopBuyLabel = buyNowBtn?.querySelector('.btn-label');
            const mobileCartLabel = mobileAddToCartBtn?.querySelector('.btn-label');
            const mobileBuyLabel = mobileBuyNowBtn?.querySelector('.btn-label');
            if (desktopCartLabel) desktopCartLabel.textContent = stock <= 0 ? 'Stok Habis' : 'Tambah ke Keranjang';
            if (desktopBuyLabel) desktopBuyLabel.textContent = stock <= 0 ? 'Pilih Produk Lain' : 'Beli Sekarang';
            if (mobileCartLabel) mobileCartLabel.textContent = stock <= 0 ? 'Stok Habis' : 'Keranjang';
            if (mobileBuyLabel) mobileBuyLabel.textContent = stock <= 0 ? 'Pilih Produk Lain' : 'Beli Sekarang';
        }

        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        function getLoginRedirectUrl() {
            return `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
        }

        function savePendingAuthAction(action) {
            try {
                localStorage.setItem(pendingAuthActionKey, JSON.stringify({
                    ...action,
                    sourcePath: window.location.pathname,
                    createdAt: Date.now(),
                }));
            } catch (e) {}
        }

        async function resumePendingAuthActionIfAny() {
            if (!isAuthenticated) return;
            let pending = null;
            try {
                pending = JSON.parse(localStorage.getItem(pendingAuthActionKey) || 'null');
            } catch (e) {
                pending = null;
            }
            if (!pending || pending.sourcePath !== window.location.pathname) return;
            if ((Date.now() - Number(pending.createdAt || 0)) > 30 * 60 * 1000) {
                localStorage.removeItem(pendingAuthActionKey);
                return;
            }

            localStorage.removeItem(pendingAuthActionKey);

            if (pending.type === 'add_to_cart') {
                const variantId = Number(pending.product_variant_id || 0);
                const quantity = Math.max(1, Number(pending.quantity || 1));
                if (!variantId) return;
                const res = await fetch(cartStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        product_variant_id: variantId,
                        quantity,
                    }),
                });
                if (res.ok) {
                    showToast(`${productData.name} (${quantity} item) ditambahkan ke keranjang!`);
                    window.dispatchEvent(new Event('cart:updated'));
                }
                return;
            }

            if (pending.type === 'buy_now') {
                const form = document.getElementById('buyNowForm');
                const variantInput = document.getElementById('buyNowVariantId');
                const qtyInput = document.getElementById('buyNowQty');
                if (!form || !variantInput || !qtyInput) return;
                variantInput.value = String(Number(pending.product_variant_id || 0));
                qtyInput.value = String(Math.max(1, Number(pending.quantity || 1)));
                form.submit();
                return;
            }

            if (pending.type === 'redeem_now') {
                const form = document.getElementById('redeemNowForm');
                const variantInput = document.getElementById('redeemNowVariantId');
                const qtyInput = document.getElementById('redeemNowQty');
                if (!form || !variantInput || !qtyInput) return;
                variantInput.value = String(Number(pending.product_variant_id || 0));
                qtyInput.value = String(Math.max(1, Number(pending.quantity || 1)));
                form.submit();
            }
        }

        function applySelectedVariantData(selectedVariant = null) {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            if (!options.length) return;

            if (!selectedVariant) {
                const selections = getSelectedVariantSelections();
                selectedVariant = options.find((opt) => variantMatchesSelections(opt, selections)) || options[0];
            }
            if (!selectedVariant) return;

            syncVariantControlsToOption(selectedVariant, false);

            activeVariantId = Number(selectedVariant.id || activeVariantId || 0);
            productData.productVariantId = activeVariantId;
            productData.stock = Number(selectedVariant.stock || 0);
            productData.price = Number(selectedVariant.price || 0);

            const buyNowVariantInput = document.getElementById('buyNowVariantId');
            const redeemNowVariantInput = document.getElementById('redeemNowVariantId');
            if (buyNowVariantInput) buyNowVariantInput.value = String(activeVariantId);
            if (redeemNowVariantInput) redeemNowVariantInput.value = String(activeVariantId);

            const displayPrice = Number(selectedVariant.displayPrice || selectedVariant.price || 0);
            const mainPriceEl = document.getElementById('productMainPrice');
            if (mainPriceEl) mainPriceEl.textContent = formatRupiah(displayPrice);
            const mobileStickyPrice = document.getElementById('mobileStickyPrice');
            if (mobileStickyPrice) mobileStickyPrice.textContent = formatRupiah(displayPrice);

            const mainOrigPriceEl = document.getElementById('productMainOrigPrice');
            if (mainOrigPriceEl) mainOrigPriceEl.textContent = formatRupiah(selectedVariant.price || 0);

            const selectedSku = selectedVariant.sku || selectedVariant.label || '-';
            ['productSku', 'productSpecSku'].forEach((id) => {
                const element = document.getElementById(id);
                if (element) element.textContent = selectedSku;
            });
            const specStock = document.getElementById('productSpecStock');
            if (specStock) specStock.textContent = `${productData.stock} pcs`;

            Object.entries(selectedVariant.attributes || {}).forEach(([key, value]) => {
                document.querySelectorAll(`[data-variant-summary="${CSS.escape(key)}"], [data-variant-spec="${CSS.escape(key)}"]`)
                    .forEach((element) => element.textContent = value || '-');
            });
            const mobileVariantSummary = document.getElementById('mobileVariantSummary');
            if (mobileVariantSummary) {
                mobileVariantSummary.textContent = Object.values(selectedVariant.attributes || {}).filter(Boolean).join(' · ') || selectedVariant.summary || '-';
            }
            document.querySelectorAll('[data-variant-row]').forEach((row) => {
                const active = Number(row.dataset.variantRow) === activeVariantId;
                row.classList.toggle('bg-blue-50', active);
                row.classList.toggle('shadow-[inset_3px_0_0_#1256a0]', active);
            });

            const stockEl = document.getElementById('productStock');
            if (stockEl) stockEl.textContent = `${productData.stock} pcs`;

            qty = clampQty(qty);
            updateQtyInput();
            updateStockUI();

            if (selectedVariant.image) {
                const variantImageIndex = images.indexOf(selectedVariant.image);
                if (variantImageIndex >= 0) {
                    setImg(variantImageIndex);
                } else {
                    const mainImg = document.getElementById('mainImg');
                    if (mainImg) mainImg.src = selectedVariant.image;
                }
            }
        }

        function selectVariantValue(select, groupKey) {
            const value = String(select?.value || '');
            const selections = getSelectedVariantSelections();
            const selectedVariant = findBestVariantForSelection(groupKey, value, selections);
            applySelectedVariantData(selectedVariant);

            // Open variant drawer on mobile when variant is selected
            if (window.innerWidth < 768 && drawerAction) {
                syncDesktopToDrawer();
                document.getElementById('variantDrawer').classList.add('active');
                document.getElementById('variantDrawerOverlay').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function normalizeVariantAttrValue(groupKey, value) {
            let normalized = String(value || '').trim().toLowerCase();
            if (/^-?\d+(?:\.\d+)?$/.test(normalized)) return String(Number(normalized));
            return normalized.replace(/\s+/g, ' ');
        }

        function variantDisplayValue(groupKey, value) {
            const raw = String(value || '').trim();
            if (!raw) return '';
            return raw;
        }

        function findBestVariantForSelection(changedKey, changedValue, selections) {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            const candidates = options.filter((option) => {
                return normalizeVariantAttrValue(changedKey, option.attributes?.[changedKey] || '') ===
                    normalizeVariantAttrValue(changedKey, changedValue);
            });

            if (!candidates.length) return options[0] || null;

            return candidates
                .map((option, index) => ({
                    option,
                    index,
                    score: Object.entries(selections).reduce((score, [key, value]) => {
                        if (key === changedKey) return score;
                        return score + (normalizeVariantAttrValue(key, option.attributes?.[key] || '') ===
                            normalizeVariantAttrValue(key, value) ? 1 : 0);
                    }, 0),
                }))
                .sort((a, b) => b.score - a.score || a.index - b.index)[0].option;
        }

        function syncVariantControlsToOption(option, drawer = false) {
            const groups = Array.isArray(productData.variantGroups) ? productData.variantGroups : [];

            groups.forEach((group) => {
                const selector = drawer
                    ? `[data-variant-group-drawer="${group.key}"]`
                    : `[data-variant-group="${group.key}"]`;
                const groupEl = document.querySelector(selector);
                if (!groupEl) return;

                const select = groupEl.querySelector(drawer
                    ? 'select[data-group-key-drawer]'
                    : 'select[data-group-key]');
                if (!select) return;

                const displayValue = variantDisplayValue(group.key, option.attributes?.[group.key] || '');
                if (!displayValue) return;

                select.value = displayValue;
                const label = document.getElementById((drawer ? 'selected-drawer-' : 'selected-') + group.key);
                if (label) label.textContent = displayValue;

                const instance = select.tomselect;
                if (instance) instance.setValue(displayValue, true);
                if (drawer) refreshVariantSelectDrawerControl(select);
                else refreshVariantSelectControl(select);
            });
        }

        function getSelectedVariantSelections() {
            const selections = {};
            document.querySelectorAll('[data-variant-group]').forEach((group) => {
                const key = group.getAttribute('data-variant-group');
                const select = group.querySelector('select[data-group-key]');
                if (!key || !select) return;
                selections[key] = String(select.value || '');
            });
            return selections;
        }

        function variantMatchesSelections(option, selections) {
            const attrs = option.attributes || {};
            return Object.entries(selections).every(([key, value]) => {
                return normalizeVariantAttrValue(key, attrs[key] || '') === normalizeVariantAttrValue(key, value);
            });
        }

        function syncVariantAvailability() {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            const groups = Array.isArray(productData.variantGroups) ? productData.variantGroups : [];
            const currentSelections = getSelectedVariantSelections();

            groups.forEach((group) => {
                const groupEl = document.querySelector(`[data-variant-group="${group.key}"]`);
                if (!groupEl) return;

                const select = groupEl.querySelector('select[data-group-key]');
                if (!select) return;

                const optionsEls = Array.from(select.options);
                let hasSelectedAvailable = false;

                optionsEls.forEach((optionEl) => {
                    const testSelections = {
                        ...currentSelections,
                        [group.key]: String(optionEl.value || ''),
                    };
                    const available = options.some((option) => variantMatchesSelections(option, testSelections));

                    optionEl.disabled = !available;
                    optionEl.hidden = !available;

                    if (String(select.value || '') === String(optionEl.value || '') && available) {
                        hasSelectedAvailable = true;
                    }
                });

                if (!hasSelectedAvailable) {
                    const firstAvailable = optionsEls.find((optionEl) => !optionEl.disabled);
                    if (!firstAvailable) return;
                    select.value = String(firstAvailable.value || '');
                }

                const label = document.getElementById('selected-' + group.key);
                if (label) label.textContent = String(select.value || '-');

                refreshVariantSelectControl(select);
            });
        }

        function initializeVariantSelects() {
            document.querySelectorAll('select[data-group-key]').forEach((select) => {
                const groupKey = select.getAttribute('data-group-key');
                if (!groupKey) return;

                if (select.tomselect) {
                    variantSelectInstances.set(groupKey, select.tomselect);
                    return;
                }

                const instance = new TomSelect(select, {
                    create: false,
                    controlInput: null,
                    maxItems: 1,
                    closeAfterSelect: true,
                    allowEmptyOption: false,
                    copyClassesToDropdown: false,
                    hideSelected: true,
                    searchField: [],
                    render: {
                        no_results(data, escape) {
                            return `<div class="ts-no-results">Tidak ditemukan: "${escape(data.input)}"</div>`;
                        },
                    },
                    onChange() {
                        selectVariantValue(select, groupKey);
                    },
                    onDropdownOpen() {
                        document.body.classList.add('variant-select-open');
                    },
                    onDropdownClose() {
                        document.body.classList.remove('variant-select-open');
                    },
                });

                if (Array.from(select.options).filter((option) => !option.disabled).length <= 1) {
                    instance.lock();
                    instance.wrapper?.classList.add('is-single-option');
                }

                variantSelectInstances.set(groupKey, instance);
            });
        }

        function refreshVariantSelectControl(select) {
            const groupKey = select?.getAttribute('data-group-key');
            const instance = (groupKey && variantSelectInstances.get(groupKey)) || select?.tomselect;
            if (!instance) return;

            instance.clearCache();
            instance.sync();
            instance.refreshOptions(false);
            instance.inputState();
        }

        async function toggleWishlist() {
            if (!isAuthenticated) {
                window.location.href = getLoginRedirectUrl();
                return;
            }
            const res = await fetch(wishlistToggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_id: Number(productData.id),
                }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                showToast('Gagal memproses wishlist');
                return;
            }
            isWishlisted = Boolean(json.wished);
            syncWishIcon();
            showToast(isWishlisted ? 'Ditambahkan ke wishlist!' : 'Dihapus dari wishlist!');
            window.dispatchEvent(new Event('wishlist:updated'));
        }

        function syncWishIcon() {
            const icon = document.getElementById('wishIcon');
            if (!icon) return;
            if (isWishlisted) {
                icon.setAttribute('fill', '#ec4899');
                icon.setAttribute('stroke', '#ec4899');
            } else {
                icon.setAttribute('fill', 'none');
                icon.setAttribute('stroke', 'currentColor');
            }
        }

        function resolveSelectedVariantId() {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            if (!options.length) return Number(productData.productVariantId || 0);
            const selections = getSelectedVariantSelections();
            const exactMatch = options.find((opt) => variantMatchesSelections(opt, selections));
            if (!exactMatch) return 0;
            if (Number(exactMatch.id || 0) !== activeVariantId) applySelectedVariantData(exactMatch);
            return Number(exactMatch.id || 0);
        }

        async function addToCart() {
            syncMainQtyInput();
            const variantId = resolveSelectedVariantId();
            if (!variantId) {
                showToast('Kombinasi varian yang dipilih tidak tersedia. Silakan pilih ulang varian.');
                return;
            }
            if (Number(productData.stock || 0) <= 0) {
                showToast('Stok produk ini sedang habis.');
                return;
            }
            if (!isAuthenticated) {
                savePendingAuthAction({
                    type: 'add_to_cart',
                    product_variant_id: variantId,
                    quantity: qty,
                });
                window.location.href = getLoginRedirectUrl();
                return;
            }
            const variantText = Array.from(document.querySelectorAll('[id^=\"selected-\"]'))
                .map(el => el.textContent)
                .filter(Boolean)
                .join(' | ');
            const price = productData.isFlashSale && productData.flashSalePrice ? productData.flashSalePrice : productData
                .price;
            const res = await fetch(cartStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_variant_id: variantId,
                    quantity: qty,
                }),
            });
            if (!res.ok) return;

            showToast(
                `${productData.name}${variantText ? ' (' + variantText + ')' : ''} (${qty} item) ditambahkan ke keranjang!`
                );
            window.dispatchEvent(new Event('cart:updated'));
        }

        function buyNow() {
            syncMainQtyInput();
            const variantId = resolveSelectedVariantId();
            if (!variantId) {
                showToast('Kombinasi varian yang dipilih tidak tersedia. Silakan pilih ulang varian.');
                return false;
            }
            if (Number(productData.stock || 0) <= 0) {
                showToast('Stok produk ini sedang habis.');
                return false;
            }
            const form = document.getElementById('buyNowForm');
            const variantInput = document.getElementById('buyNowVariantId');
            const qtyInput = document.getElementById('buyNowQty');
            if (!form || !variantInput || !qtyInput) return false;
            variantInput.value = String(variantId || 0);
            qtyInput.value = String(qty || 1);
            form.submit();
            return false;
        }

        function redeemNow() {
            syncMainQtyInput();
            const variantId = resolveSelectedVariantId();
            if (!variantId) {
                showToast('Kombinasi varian yang dipilih tidak tersedia. Silakan pilih ulang varian.');
                return false;
            }
            if (Number(productData.stock || 0) <= 0) {
                showToast('Stok produk ini sedang habis.');
                return false;
            }
            if (!isAuthenticated) {
                savePendingAuthAction({
                    type: 'redeem_now',
                    product_variant_id: variantId,
                    quantity: qty,
                });
                window.location.href = getLoginRedirectUrl();
                return false;
            }
            const form = document.getElementById('redeemNowForm');
            const variantInput = document.getElementById('redeemNowVariantId');
            const qtyInput = document.getElementById('redeemNowQty');
            if (!form || !variantInput || !qtyInput) return false;
            variantInput.value = String(variantId || 0);
            qtyInput.value = String(qty || 1);
            form.submit();
            return false;
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        initializeVariantSelects();
        initializeVariantSelectsDrawer();
        initDrawerDragHandlers();
        applySelectedVariantData();
        updateStockUI();
        syncWishIcon();
        resumePendingAuthActionIfAny();
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeProductImageModal();
        });

        // Reviews
        const reviews = @json($reviewItems->values()->all());

        const reviewColors = ['bg-blue-400', 'bg-blue-400', 'bg-orange-400', 'bg-purple-400'];
        document.getElementById('reviews-container').innerHTML = reviews.length ? reviews.map((r, i) => `
      <div class="${i > 0 ? 'pt-5 border-t border-slate-100 mt-5' : ''}">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-full ${reviewColors[i % reviewColors.length]} flex items-center justify-center text-white font-bold flex-shrink-0">${(r.name || 'U').substring(0, 1).toUpperCase()}</div>
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1 flex-wrap">
              <p class="font-semibold text-slate-800 text-sm">${r.name}</p>
              <span class="text-xs text-slate-400 ml-auto">${r.date}</span>
            </div>
            <div class="text-yellow-400 text-sm mb-1">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
            ${r.variant ? '<div class="flex gap-2 mb-2"><span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">' + r.variant + '</span></div>' : ''}
            <p class="text-sm text-slate-600 leading-relaxed">${r.text}</p>
            ${Array.isArray(r.photos) && r.photos.length ? `<div class="mt-3 flex flex-wrap gap-2">${r.photos.map((photo) => `<button type="button" onclick="openReviewImageModal('${String(photo).replace(/'/g, "\\'")}')" class="block"><img src="${photo}" alt="Foto ulasan" class="w-14 h-14 rounded-lg object-cover border border-slate-200" /></button>`).join('')}</div>` : ''}
          </div>
        </div>
      </div>`).join('') : '<p class="text-sm text-slate-500">Belum ada ulasan untuk produk ini.</p>';

        function openReviewImageModal(src) {
            const modal = document.getElementById('reviewImageModal');
            const img = document.getElementById('reviewImageModalImg');
            if (!modal || !img || !src) return;
            img.src = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeReviewImageModal() {
            const modal = document.getElementById('reviewImageModal');
            const img = document.getElementById('reviewImageModalImg');
            if (!modal || !img) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            img.src = '';
        }
        document.getElementById('reviewImageModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeReviewImageModal();
        });

        // Sale Timer
        function updateSaleTimer() {
            const el = document.getElementById('saleTimer');
            if (!el) return;
            const endRaw = productData.flashSaleEndAt;
            if (!endRaw) {
                el.textContent = '--:--:--';
                return;
            }
            const now = new Date();
            const end = new Date(endRaw);
            const diff = Math.max(end - now, 0);
            const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
            const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
            el.textContent = `${h}:${m}:${s}`;
        }
        if (document.getElementById('saleTimer')) {
            setInterval(updateSaleTimer, 1000);
            updateSaleTimer();
        }

        // Navbar mega dropdown
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
                    title: productData.name,
                    text: `Cek produk ini di ${@json($appStoreName ?? 'Ecommerce Citra')}!`,
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

        function openSellerChat() {
            if (window.Tawk_API && typeof window.Tawk_API.maximize === 'function') {
                window.Tawk_API.maximize();
                return;
            }
            if (sellerSupportUrl) {
                window.open(sellerSupportUrl, '_blank', 'noopener,noreferrer');
                return;
            }
            showToast('Layanan chat penjual belum tersedia.');
        }

        function requestQuotation() {
            if (window.Tawk_API && typeof window.Tawk_API.maximize === 'function') {
                window.Tawk_API.maximize();
                showToast('Sampaikan jumlah dan kebutuhan proyek Anda melalui chat.');
                return;
            }
            if (sellerSupportUrl) {
                window.open(sellerSupportUrl, '_blank', 'noopener,noreferrer');
                return;
            }
            showToast('Kanal permintaan penawaran belum tersedia.');
        }

        setMegaCategory('rumah-tangga');
    </script>
@endsection
