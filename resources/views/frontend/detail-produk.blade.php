@extends('layouts.user')

@section('title', ($productData['name'] ?? 'Detail Produk') . ' - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
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

        .ts-wrapper.single .ts-control {
            min-height: 44px;
            border-radius: 0.75rem;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            box-shadow: none;
            padding: 0.6rem 0.875rem;
            font-size: 0.875rem;
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
                    class="hover:text-blue-600">{{ $productData['categoryName'] }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-800 font-medium">{{ $productData['name'] }}</span>
            </nav>
        </div>
    </div>

    <!-- MAIN PRODUCT SECTION -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-8 pb-28 md:pb-8">
        <div class="grid md:grid-cols-2 gap-10 lg:gap-16">

            <!-- LEFT: Gallery -->
            <div class="flex flex-col gap-3">
                <!-- Main Image -->
                <div class="relative rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 shadow-sm aspect-square md:aspect-[4/3]">
                    <img id="mainImg" src="{{ $productData['image'] }}" alt="{{ $productData['name'] }}"
                        class="w-full h-full object-cover main-img" />
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
                            <i class="ri-arrow-left-s-line text-xl text-slate-600"></i>
                        </button>
                        <button onclick="nextImg()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-white/80 backdrop-blur-sm rounded-full shadow flex items-center justify-center hover:bg-white transition-colors">
                            <i class="ri-arrow-right-s-line text-xl text-slate-600"></i>
                        </button>
                    @endif
                </div>
                <!-- Thumbnails -->
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @foreach ($productData['images'] ?? [$productData['image']] as $idx => $thumb)
                        <button onclick="setImg({{ $idx }})"
                            class="thumb-btn flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 transition-all {{ $idx === 0 ? 'thumb-active border-blue-400' : 'border-slate-200 hover:border-slate-300' }}">
                            <img src="{{ $thumb }}" class="w-full h-full object-cover" />
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- RIGHT: Product Info -->
            <div>
                <!-- Brand & Status -->
                <div class="flex items-center justify-between mb-2">
                    <span
                        class="text-blue-600 font-semibold text-xs bg-blue-50 px-2.5 py-1 rounded-full">{{ $productData['categoryName'] }}</span>
                    <div class="flex items-center gap-2">
                        <span id="stockStatusBadge" class="text-xs font-medium flex items-center gap-1 rounded-full px-2.5 py-1 {{ ($productData['stock'] ?? 0) <= 0 ? 'bg-red-50 text-red-600' : (($productData['stock'] ?? 0) <= 5 ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600') }}">
                            <span id="stockStatusDot" class="w-2 h-2 rounded-full {{ ($productData['stock'] ?? 0) <= 0 ? 'bg-red-500' : (($productData['stock'] ?? 0) <= 5 ? 'bg-amber-500' : 'bg-blue-500') }}"></span>
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

                <h1 class="text-lg sm:text-2xl md:text-3xl font-extrabold text-slate-900 mb-3 leading-tight">
                    {{ $productData['name'] }}</h1>

                <!-- Rating & Sales -->
                <div class="flex items-center gap-2 sm:gap-4 mb-4 flex-wrap">
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
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <span class="text-slate-600 text-xs"><span class="font-semibold text-slate-700">{{ number_format($productData['sold']) }}</span> terjual</span>
                    @if (!empty($productData['isRedeemProduct']))
                        <span class="text-slate-300 hidden sm:inline">|</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 border border-amber-200">
                            Redeem {{ number_format((int) ($productData['redeemPoints'] ?? 0), 0, ',', '.') }} point
                        </span>
                    @endif
                </div>

                <!-- Price -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-3 sm:p-4 mb-5 border border-blue-100/60">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span id="productPrice" class="text-xl sm:text-2xl md:text-3xl font-extrabold text-blue-600">Rp
                            {{ number_format($displayPrice, 0, ',', '.') }}</span>
                        @if ($productData['isFlashSale'])
                            <span id="productOrigPrice" class="text-xs sm:text-sm text-slate-400 line-through">Rp
                                {{ number_format($productData['origPrice'], 0, ',', '.') }}</span>
                            <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-md">Hemat
                                {{ max(0, $savingPercent) }}%</span>
                        @endif
                    </div>
                    @if ($productData['isFlashSale'])
                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-0.5 rounded">Flash
                                Sale</span>
                            <span class="text-xs text-slate-600">Berakhir dalam:</span>
                            <span class="font-mono font-bold text-red-600 text-sm" id="saleTimer">00:00:00</span>
                        </div>
                    @endif
                </div>

                @foreach ($otherGroups as $group)
                    <div class="mb-5 hidden md:block" data-variant-group="{{ $group['key'] }}">
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Keunggulan</p>
                        <p class="text-sm font-semibold text-slate-700">Produk aktif & siap dibeli</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Pengiriman</p>
                        <p class="text-sm font-semibold text-slate-700">Cek ongkir saat checkout</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Keamanan</p>
                        <p class="text-sm font-semibold text-slate-700">Checkout aman & cepat</p>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-5">
                    <span class="text-xs sm:text-sm font-semibold text-slate-700 block mb-2">Jumlah</span>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden">
                            <button onclick="changeQty(-1)"
                                class="px-2.5 py-1.5 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-colors">−</button>
                            <span id="qtyDisplay"
                                class="px-3 py-1.5 font-bold text-slate-800 min-w-[36px] sm:min-w-[44px] text-center border-x-2 border-slate-200 text-sm">1</span>
                            <button onclick="changeQty(1)"
                                class="px-2.5 py-1.5 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-colors">+</button>
                        </div>
                        <span class="text-xs sm:text-sm text-slate-500">Stok: <span
                                id="productStock" class="text-slate-700 font-semibold">{{ $productData['stock'] }}
                                item</span></span>
                    </div>
                </div>

                <!-- Action Buttons (Desktop) -->
                <div class="hidden md:flex gap-3 mb-6">
                    <button id="addToCartBtn" onclick="addToCart()"
                        class="flex-1 h-11 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-xl border border-blue-200 hover:border-blue-400 transition-all flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="btn-label">Keranjang</span>
                    </button>
                    <button id="buyNowBtn" type="button" onclick="buyNow()"
                        class="flex-1 h-11 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-100 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="btn-label">Beli Sekarang</span>
                    </button>
                    @if (!empty($productData['isRedeemProduct']))
                        <button type="button" onclick="redeemNow()"
                            class="flex-1 h-11 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm shadow-amber-100 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10V6m0 12v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Redeem Point
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- TABS: Deskripsi, Ulasan, Variant -->
        <div class="mt-10">
            <div class="flex border-b border-slate-200 mb-6 gap-4 sm:gap-8 overflow-x-auto">
                <button onclick="switchTab('desc')" id="tab-desc"
                    class="tab-btn active pb-3 text-sm font-semibold text-blue-600 whitespace-nowrap border-b-2 border-blue-500">Deskripsi</button>
                <button onclick="switchTab('review')" id="tab-review"
                    class="tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap">Ulasan
                    ({{ number_format($productData['reviews']) }})</button>
                <button onclick="switchTab('size')" id="tab-size"
                    class="tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap">Varian</button>
            </div>

            <!-- Deskripsi -->
            <div id="content-desc" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-4 text-lg">Tentang Produk</h3>
                <div class="prose text-slate-600 text-sm leading-relaxed">
                    {!! $productData['description'] ?: '<p>Belum ada deskripsi produk.</p>' !!}
                </div>
            </div>
            <!-- Review -->
            <div id="content-review" class="hidden bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
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
            </div>

            <!-- Size Guide -->
            <div id="content-size" class="hidden bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-4">Daftar Varian</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700 rounded-tl-xl">Varian</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Harga</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Stok</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 rounded-tr-xl">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse (($productData['variantOptions'] ?? []) as $option)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $option['summary'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">
                                        Rp {{ number_format($productData['isFlashSale'] ? ($option['displayPrice'] ?? 0) : ($option['price'] ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((int) ($option['stock'] ?? 0)) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ ((int) ($option['stock'] ?? 0)) > 0 ? 'Tersedia' : 'Habis' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-slate-500">Belum ada data variant.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if (!empty($recentlyViewedProductsJson))
            <div class="mt-10 sm:mt-12">
                <div class="flex items-center justify-between mb-4 sm:mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 sm:h-7 bg-gradient-to-b from-emerald-500 to-blue-600 rounded-full"></div>
                        <h2 class="text-base sm:text-xl font-bold text-slate-800">Terakhir Dilihat</h2>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach ($recentlyViewedProductsJson as $rv)
                        <a href="{{ $rv['url'] }}"
                            class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                            <div class="relative overflow-hidden aspect-[4/3]">
                                <img src="{{ $rv['image'] }}" alt="{{ $rv['name'] }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
                            </div>
                            <div class="p-3 flex-1 flex flex-col gap-1">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 line-clamp-2 leading-snug transition-colors">{{ $rv['name'] }}</p>
                                <span class="font-bold text-slate-900 text-sm mt-auto">Rp {{ number_format($rv['price'], 0, ',', '.') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- PRODUK REKOMENDASI -->
        <div class="mt-10 sm:mt-12">
            <div class="flex items-center justify-between mb-4 sm:mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-6 sm:h-7 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></div>
                    <h2 class="text-base sm:text-xl font-bold text-slate-800">Produk Rekomendasi</h2>
                </div>
                <a href="{{ route('frontend.kategori') }}"
                    class="text-blue-600 hover:text-blue-700 font-semibold text-sm flex items-center gap-1 transition-colors">
                    Lihat Semua <i class="ri-arrow-right-s-line text-base"></i>
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @forelse ($relatedProductsJson as $rp)
                    <a href="{{ url('/detail-produk/' . $rp['slug']) }}"
                        class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                        <div class="relative overflow-hidden aspect-[4/3]">
                            <img src="{{ $rp['image'] }}" alt="{{ $rp['name'] }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
                            @if ($rp['isFlashSale'] && $rp['originalPrice'] > $rp['price'])
                                @php $disc = round((1 - $rp['price'] / $rp['originalPrice']) * 100); @endphp
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">-{{ $disc }}%</span>
                            @endif
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                        </div>
                        <div class="p-3 flex-1 flex flex-col gap-1">
                            <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 line-clamp-2 leading-snug transition-colors">{{ $rp['name'] }}</p>
                            <div class="flex items-center gap-1">
                                @php
                                    $rpRating = (float) $rp['rating'];
                                    $rpFull = (int) floor($rpRating);
                                @endphp
                                <div class="flex">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <span class="{{ $s <= $rpFull ? 'text-yellow-400' : 'text-slate-300' }} text-xs">★</span>
                                    @endfor
                                </div>
                                <span class="text-xs font-medium text-slate-700">{{ number_format($rpRating, 1) }}</span>
                                @if ($rp['reviews'] > 0)
                                    <span class="text-xs text-slate-400">({{ number_format($rp['reviews']) }})</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap mt-auto pt-1">
                                <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($rp['price'], 0, ',', '.') }}</span>
                                @if ($rp['originalPrice'] > $rp['price'])
                                    <span class="text-slate-400 text-xs line-through">Rp {{ number_format($rp['originalPrice'], 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-10 text-slate-400 text-sm">Belum ada produk rekomendasi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- STICKY BOTTOM BAR (Mobile) -->
    <div id="mobileStickyActions" class="mobile-sticky-actions sticky bottom-[76px] md:hidden bg-white border-t border-slate-200 px-3 py-2.5 flex flex-col gap-2">
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
                <button onclick="closeVariantDrawer()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-500">
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
                    <select onchange="selectVariantValueDrawer(this, '{{ $group['key'] }}')"
                        data-group-key-drawer="{{ $group['key'] }}"
                        class="w-full drawer-variant-select">
                        @foreach ($group['values'] as $value)
                            <option value="{{ $value }}" @selected(($defaultOther[$group['key']] ?? null) === $value)>{{ $value }}</option>
                        @endforeach
                  </select>
                </div>
            @endforeach

            <!-- Quantity -->
            <div class="mb-5">
                <span class="text-sm font-semibold text-slate-700 block mb-2">Jumlah</span>
                <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden w-32">
                    <button onclick="changeQtyDrawer(-1)"
                        class="px-3 py-2 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-colors">−</button>
                    <span id="qtyDisplayDrawer"
                        class="px-4 py-2 font-bold text-slate-800 min-w-[44px] text-center border-x-2 border-slate-200 text-sm">1</span>
                    <button onclick="changeQtyDrawer(1)"
                        class="px-3 py-2 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-colors">+</button>
                </div>
            </div>
        </div>

        <!-- Drawer Actions -->
        <div class="px-4 py-3 pb-20 border-t border-slate-100 bg-white">
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
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script>
        const productData = @json($productData);
        const isAuthenticated = @json(auth()->check());
        const loginUrl = @json(route('login'));
        const cartStoreUrl = @json(route('frontend.cart.store'));
        const wishlistToggleUrl = @json(route('frontend.wishlist.toggle'));
        const csrfToken = @json(csrf_token());
        const pendingAuthActionKey = 'ec_pending_auth_action';
        const images = (productData.images && productData.images.length ? productData.images : [productData.image]);
        let currentImg = 0;
        let qty = 1;
        let qtyDrawer = 1;
        let isWishlisted = Boolean(productData.isWishlisted);
        const variantSelectInstances = new Map();
        const variantSelectDrawerInstances = new Map();
        let drawerAction = 'buy'; // 'buy', 'cart', or 'redeem'
        let touchStartY = 0;
        let touchCurrentY = 0;
        let isDragging = false;

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
            qty = Math.max(1, Math.min(productData.stock || 1, qty + d));
            document.getElementById('qtyDisplay').textContent = qty;
        }

        function changeQtyDrawer(d) {
            qtyDrawer = Math.max(1, Math.min(productData.stock || 1, qtyDrawer + d));
            document.getElementById('qtyDisplayDrawer').textContent = qtyDrawer;
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
            qtyDrawer = qty;
            document.getElementById('qtyDisplayDrawer').textContent = qtyDrawer;

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
        }

        function closeVariantDrawer() {
            const drawer = document.getElementById('variantDrawer');
            const overlay = document.getElementById('variantDrawerOverlay');

            drawer.classList.remove('active');
            drawer.style.transform = '';
            overlay.classList.remove('active');
            document.body.style.overflow = '';

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
            document.getElementById('qtyDisplay').textContent = qty;

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
            const label = document.getElementById('selected-drawer-' + groupKey);
            if (label) label.textContent = value;
            applySelectedVariantDataDrawer();
        }

        function applySelectedVariantDataDrawer() {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            if (!options.length) return;

            syncVariantAvailabilityDrawer();
            const selections = getSelectedVariantSelectionsDrawer();
            let selectedVariant = options.find((opt) => variantMatchesSelections(opt, selections));
            if (!selectedVariant) selectedVariant = options[0];
            if (!selectedVariant) return;

            const displayPrice = Number(selectedVariant.displayPrice || selectedVariant.price || 0);
            const drawerPrice = document.getElementById('drawerProductPrice');
            if (drawerPrice) drawerPrice.textContent = formatRupiah(displayPrice);

            const drawerStock = document.getElementById('drawerProductStock');
            if (drawerStock) drawerStock.textContent = `Stok: ${Number(selectedVariant.stock || 0)} item`;

            const drawerImage = document.getElementById('drawerProductImage');
            if (drawerImage && selectedVariant.image) drawerImage.src = selectedVariant.image;

            qtyDrawer = Math.min(qtyDrawer, Math.max(1, Number(selectedVariant.stock || 0)));
            document.getElementById('qtyDisplayDrawer').textContent = qtyDrawer;
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
                    maxItems: 1,
                    closeAfterSelect: true,
                    allowEmptyOption: false,
                    copyClassesToDropdown: false,
                    hideSelected: true,
                    searchField: ['text'],
                    render: {
                        no_results(data, escape) {
                            return `<div class="ts-no-results">Tidak ditemukan: "${escape(data.input)}"</div>`;
                        },
                    },
                    onChange() {
                        selectVariantValueDrawer(select, groupKey);
                    },
                });

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
            const statusBadge = document.getElementById('stockStatusBadge');
            const stockEl = document.getElementById('productStock');
            const mobileStock = document.getElementById('mobileStickyStock');
            const mobileStatus = document.getElementById('mobileStickyStatus');
            const addToCartBtn = document.getElementById('addToCartBtn');
            const buyNowBtn = document.getElementById('buyNowBtn');
            const mobileAddToCartBtn = document.getElementById('mobileAddToCartBtn');
            const mobileBuyNowBtn = document.getElementById('mobileBuyNowBtn');

            let label = 'Stok Tersedia';
            let badgeClass = ['bg-blue-50', 'text-blue-600'];
            let dotClass = 'bg-blue-500';

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
            if (statusBadge) statusBadge.className = `text-xs font-medium flex items-center gap-1 rounded-full px-2.5 py-1 ${badgeClass.join(' ')}`;
            if (stockEl) stockEl.textContent = `${stock} item`;
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
            if (desktopCartLabel) desktopCartLabel.textContent = stock <= 0 ? 'Stok Habis' : 'Keranjang';
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

        function applySelectedVariantData() {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            if (!options.length) return;

            syncVariantAvailability();
            const selections = getSelectedVariantSelections();
            let selectedVariant = options.find((opt) => variantMatchesSelections(opt, selections));
            if (!selectedVariant) selectedVariant = options[0];
            if (!selectedVariant) return;

            productData.productVariantId = Number(selectedVariant.id || productData.productVariantId || 0);
            productData.stock = Number(selectedVariant.stock || 0);
            productData.price = Number(selectedVariant.price || 0);

            const displayPrice = Number(selectedVariant.displayPrice || selectedVariant.price || 0);
            const priceEl = document.getElementById('productPrice');
            if (priceEl) priceEl.textContent = formatRupiah(displayPrice);
            const mobileStickyPrice = document.getElementById('mobileStickyPrice');
            if (mobileStickyPrice) mobileStickyPrice.textContent = formatRupiah(displayPrice);

            const origPriceEl = document.getElementById('productOrigPrice');
            if (origPriceEl) origPriceEl.textContent = formatRupiah(selectedVariant.price || 0);

            const stockEl = document.getElementById('productStock');
            if (stockEl) stockEl.textContent = `${productData.stock} item`;

            qty = Math.min(qty, Math.max(1, productData.stock || 1));
            document.getElementById('qtyDisplay').textContent = qty;

            if (selectedVariant.image) {
                const mainImg = document.getElementById('mainImg');
                if (mainImg) mainImg.src = selectedVariant.image;
            }
        }

        function selectVariantValue(select, groupKey) {
            const value = String(select?.value || '');
            const label = document.getElementById('selected-' + groupKey);
            if (label) label.textContent = value;
            applySelectedVariantData();

            // Open variant drawer on mobile when variant is selected
            if (window.innerWidth < 768 && drawerAction) {
                syncDesktopToDrawer();
                document.getElementById('variantDrawer').classList.add('active');
                document.getElementById('variantDrawerOverlay').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function normalizeVariantAttrValue(groupKey, value) {
            const raw = String(value || '').trim().toLowerCase();
            return groupKey === 'length_mm' ? raw.replace(/mm$/i, '') : raw;
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
                    maxItems: 1,
                    closeAfterSelect: true,
                    allowEmptyOption: false,
                    copyClassesToDropdown: false,
                    hideSelected: true,
                    searchField: ['text'],
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
            if (exactMatch) return Number(exactMatch.id || 0);
            return Number(productData.productVariantId || options[0]?.id || 0);
        }

        async function addToCart() {
            const variantId = resolveSelectedVariantId();
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
            const variantId = resolveSelectedVariantId();
            if (Number(productData.stock || 0) <= 0) {
                showToast('Stok produk ini sedang habis.');
                return false;
            }
            if (!isAuthenticated) {
                savePendingAuthAction({
                    type: 'buy_now',
                    product_variant_id: variantId,
                    quantity: qty,
                });
                window.location.href = getLoginRedirectUrl();
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
            const variantId = resolveSelectedVariantId();
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

        function switchTab(tab) {
            ['desc', 'review', 'size'].forEach(t => {
                document.getElementById('tab-' + t).className =
                    'tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap';
                document.getElementById('content-' + t).classList.add('hidden');
            });
            document.getElementById('tab-' + tab).className =
                'tab-btn active pb-3 text-sm font-semibold text-blue-600 whitespace-nowrap border-b-2 border-blue-500';
            document.getElementById('content-' + tab).classList.remove('hidden');
        }



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

        setMegaCategory('rumah-tangga');
    </script>
@endsection

