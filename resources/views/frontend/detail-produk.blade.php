@extends('layouts.user')

@section('title', ($productData['name'] ?? 'Detail Produk') . ' - Ecommerce Citra')

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
    @php
        $displayPrice = $productData['isFlashSale'] ? $productData['flashSalePrice'] : $productData['price'];
        $savingPercent =
            $productData['origPrice'] > 0 ? round((1 - $displayPrice / $productData['origPrice']) * 100) : 0;
        $variantGroups = collect($productData['variantGroups'] ?? []);
        $colorGroup = $variantGroups->first(fn($g) => str_contains(strtolower($g['key'] ?? ''), 'warna'));
        $otherGroups = $variantGroups->filter(fn($g) => !str_contains(strtolower($g['key'] ?? ''), 'warna'))->values();
        $defaultColor = $colorGroup['values'][0] ?? null;
        $defaultOther = $otherGroups->mapWithKeys(fn($g) => [$g['key'] => $g['values'][0] ?? null])->all();
        $reviewItems = collect($productData['reviewItems'] ?? []);
        $reviewDistribution = collect($productData['reviewDistribution'] ?? []);
    @endphp
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
                        <span class="text-blue-600 text-xs font-medium flex items-center gap-1">
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

                @if ($colorGroup)
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-slate-700">{{ $colorGroup['label'] }}: <span
                                    id="selectedColor" class="text-blue-600 font-bold">{{ $defaultColor }}</span></span>
                        </div>
                        <div class="flex gap-3 flex-wrap">
                            @foreach ($colorGroup['values'] as $idx => $color)
                                @php
                                    $key = strtolower(trim($color));
                                    $swatchClass = match (true) {
                                        str_contains($key, 'putih') => 'bg-white border-2 border-slate-200',
                                        str_contains($key, 'hitam') => 'bg-slate-900',
                                        str_contains($key, 'abu') => 'bg-slate-400',
                                        str_contains($key, 'biru') => 'bg-blue-700',
                                        str_contains($key, 'merah') => 'bg-red-500',
                                        str_contains($key, 'hijau') => 'bg-green-600',
                                        str_contains($key, 'kuning') => 'bg-yellow-400',
                                        str_contains($key, 'ungu') => 'bg-purple-600',
                                        str_contains($key, 'pink') => 'bg-pink-400',
                                        str_contains($key, 'orange') => 'bg-orange-500',
                                        default => 'bg-slate-300',
                                    };
                                @endphp
                                <button onclick="selectColor(this, '{{ $color }}')"
                                    class="color-swatch w-10 h-10 rounded-full {{ $swatchClass }} {{ $idx === 0 ? 'outline outline-2 outline-blue-500 outline-offset-2' : '' }} hover:scale-110 transition-transform"
                                    title="{{ $color }}"></button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @foreach ($otherGroups as $group)
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs sm:text-sm font-semibold text-slate-700">{{ $group['label'] }}:
                                <span id="selected-{{ $group['key'] }}"
                                    class="text-blue-600 font-bold">{{ $defaultOther[$group['key']] ?? '-' }}</span>
                            </span>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            @foreach ($group['values'] as $idx => $value)
                                <button onclick="selectVariantValue(this, '{{ $group['key'] }}', '{{ $value }}')"
                                    class="variant-btn {{ $idx === 0 ? 'active border-blue-400' : 'border-slate-200 text-slate-600' }} border-2 rounded-xl px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:border-blue-300 transition-all">{{ $value }}</button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
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
                    <button onclick="addToCart()"
                        class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-3.5 rounded-2xl border-2 border-blue-200 hover:border-blue-400 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Tambah ke Keranjang
                    </button>
                    <button type="button" onclick="buyNow()"
                        class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-3.5 rounded-2xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-200 hover:shadow-blue-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Beli Sekarang
                    </button>
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
                    class="tab-btn pb-3 text-sm font-semibold text-slate-500 hover:text-slate-700 whitespace-nowrap">Variant</button>
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
                <h3 class="font-bold text-slate-800 mb-4">Daftar Variant</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700 rounded-tl-xl">Variant</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Harga</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Stok</th>
                                <th class="px-4 py-3 font-semibold text-slate-700 rounded-tr-xl">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse (($productData['variantOptions'] ?? []) as $option)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $option['name'] }}: {{ $option['value'] }}</td>
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
        <button type="button" onclick="buyNow()"
            class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold py-2.5 rounded-xl text-sm flex items-center justify-center gap-1.5 shadow-md shadow-blue-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Beli Sekarang
        </button>
    </div>

    <form id="buyNowForm" method="POST" action="{{ route('frontend.checkout.buy-now') }}" class="hidden">
        @csrf
        <input type="hidden" name="product_variant_id" id="buyNowVariantId" value="{{ $productData['productVariantId'] ?? 0 }}">
        <input type="hidden" name="quantity" id="buyNowQty" value="1">
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
        let isWishlisted = Boolean(productData.isWishlisted);

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
            }
        }

        function applySelectedVariantData() {
            const options = Array.isArray(productData.variantOptions) ? productData.variantOptions : [];
            if (!options.length) return;

            const selectedColor = (document.getElementById('selectedColor')?.textContent || '').trim().toLowerCase();
            const selectedOthers = Array.from(document.querySelectorAll('[id^="selected-"]'))
                .map((el) => (el.textContent || '').trim().toLowerCase())
                .filter(Boolean);
            const selectedValues = [selectedColor, ...selectedOthers].filter(Boolean);

            let selectedVariant = options.find((opt) => selectedValues.includes(String(opt.value || '').trim()
                .toLowerCase()));
            if (!selectedVariant) selectedVariant = options[0];
            if (!selectedVariant) return;

            productData.productVariantId = Number(selectedVariant.id || productData.productVariantId || 0);
            productData.stock = Number(selectedVariant.stock || 0);
            productData.price = Number(selectedVariant.price || 0);

            const displayPrice = Number(selectedVariant.displayPrice || selectedVariant.price || 0);
            const priceEl = document.getElementById('productPrice');
            if (priceEl) priceEl.textContent = formatRupiah(displayPrice);

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

        function selectColor(btn, color) {
            document.querySelectorAll('.color-swatch').forEach(b => b.style.outline = 'none');
            btn.style.outline = '2px solid #2563eb';
            btn.style.outlineOffset = '2px';
            const label = document.getElementById('selectedColor');
            if (label) label.textContent = color;
            applySelectedVariantData();
        }

        function selectVariantValue(btn, groupKey, value) {
            const wrapper = btn.closest('.mb-5');
            if (wrapper) {
                wrapper.querySelectorAll('.variant-btn').forEach(b => {
                    b.classList.remove('active', 'border-blue-400');
                    b.classList.add('border-slate-200', 'text-slate-600');
                });
            }
            btn.classList.add('active', 'border-blue-400');
            btn.classList.remove('border-slate-200', 'text-slate-600');
            const label = document.getElementById('selected-' + groupKey);
            if (label) label.textContent = value;
            applySelectedVariantData();
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
            const selectedColor = (document.getElementById('selectedColor')?.textContent || '').trim().toLowerCase();
            const selectedOthers = Array.from(document.querySelectorAll('[id^="selected-"]'))
                .map((el) => (el.textContent || '').trim().toLowerCase())
                .filter(Boolean);
            const byColor = options.find((opt) => String(opt.value || '').trim().toLowerCase() === selectedColor);
            if (byColor) return Number(byColor.id || 0);
            const byOther = options.find((opt) => selectedOthers.includes(String(opt.value || '').trim().toLowerCase()));
            if (byOther) return Number(byOther.id || 0);
            return Number(productData.productVariantId || options[0]?.id || 0);
        }

        async function addToCart() {
            const variantId = resolveSelectedVariantId();
            if (!isAuthenticated) {
                savePendingAuthAction({
                    type: 'add_to_cart',
                    product_variant_id: variantId,
                    quantity: qty,
                });
                window.location.href = getLoginRedirectUrl();
                return;
            }
            const color = document.getElementById('selectedColor')?.textContent || '';
            const variantSelections = Array.from(document.querySelectorAll('[id^=\"selected-\"]'))
                .map(el => el.textContent)
                .filter(Boolean)
                .join(', ');
            const variantText = [color, variantSelections].filter(Boolean).join(' | ');
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

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        applySelectedVariantData();
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
                    title: productData.name,
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
