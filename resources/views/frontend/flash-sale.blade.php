@extends('layouts.user')

@section('title', 'Flash Sale - Ecommerce Citra')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12); }
    </style>
@endsection

@section('content')
    @include('partials.navbar-user')

    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-800 font-medium">Flash Sale</span>
            </nav>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-3xl p-6 border border-red-100 mb-6">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                        <i class="ri-flashlight-fill text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-800">Flash Sale</h1>
                        <p class="text-slate-500 text-sm">Penawaran terbatas, jangan sampai kehabisan</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-500">Berakhir:</span>
                    <span id="fs-hours" class="bg-red-500 text-white text-sm font-bold px-2.5 py-1 rounded-lg">05</span>
                    <span class="text-red-400 font-bold">:</span>
                    <span id="fs-minutes" class="bg-red-500 text-white text-sm font-bold px-2.5 py-1 rounded-lg">23</span>
                    <span class="text-red-400 font-bold">:</span>
                    <span id="fs-seconds" class="bg-red-500 text-white text-sm font-bold px-2.5 py-1 rounded-lg">47</span>
                </div>
            </div>
        </div>

        <div id="flashSaleGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"></div>
    </section>
@endsection

@section('script')
    <script>
        const flashSaleProducts = [
            { name: 'Kemeja Oxford Slim Fit', price: 189000, originalPrice: 270000, sold: 1245, image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400&h=400&fit=crop' },
            { name: 'Sneakers Urban Street', price: 459000, originalPrice: 650000, sold: 875, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop' },
            { name: 'Wireless Earbuds Pro', price: 599000, originalPrice: 850000, sold: 3210, image: 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=400&h=400&fit=crop' },
            { name: 'Skincare Serum Vitamin C', price: 189000, originalPrice: 250000, sold: 5678, image: 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop' },
            { name: 'Hoodie Oversized Fleece', price: 299000, originalPrice: 399000, sold: 4321, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop' },
            { name: 'Blender Portable Mini', price: 149000, originalPrice: 199000, sold: 789, image: 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=400&h=400&fit=crop' },
            { name: 'Smart Watch Series 5', price: 1299000, originalPrice: 1800000, sold: 892, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop' },
            { name: 'Kamera Mirrorless Entry', price: 5499000, originalPrice: 6800000, sold: 234, image: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&h=400&fit=crop' },
            { name: 'Dress Floral Premium', price: 279000, originalPrice: 399000, sold: 1567, image: 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=400&h=400&fit=crop' },
            { name: 'Running Shoes Lite', price: 539000, originalPrice: 720000, sold: 2345, image: 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=400&h=400&fit=crop' },
        ];

        const grid = document.getElementById('flashSaleGrid');
        grid.innerHTML = flashSaleProducts.map((p) => {
            const disc = Math.round((1 - p.price / p.originalPrice) * 100);
            return `
            <a href="{{ route('frontend.detail-produk') }}" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-red-100 card-hover group">
                <div class="relative">
                    <img src="${p.image}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300" alt="${p.name}" />
                    <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-${disc}%</span>
                </div>
                <div class="p-3">
                    <p class="text-sm font-semibold text-slate-800 line-clamp-2 min-h-[40px]">${p.name}</p>
                    <div class="mt-2">
                        <p class="text-base font-bold text-red-500">Rp ${p.price.toLocaleString('id-ID')}</p>
                        <p class="text-xs text-slate-400 line-through">Rp ${p.originalPrice.toLocaleString('id-ID')}</p>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">${p.sold.toLocaleString('id-ID')} terjual</p>
                </div>
            </a>`;
        }).join('');

        function updateTimer() {
            const now = new Date();
            const end = new Date();
            end.setHours(23, 59, 59, 0);
            const diff = Math.max(end - now, 0);
            document.getElementById('fs-hours').textContent = String(Math.floor(diff / 3600000)).padStart(2, '0');
            document.getElementById('fs-minutes').textContent = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
            document.getElementById('fs-seconds').textContent = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
        }
        setInterval(updateTimer, 1000);
        updateTimer();
    </script>
@endsection

