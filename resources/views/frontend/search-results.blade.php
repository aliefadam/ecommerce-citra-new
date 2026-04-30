@extends('layouts.user')

@section('title', 'Hasil Pencarian - Ecommerce Citra')

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
                <span class="text-slate-800 font-medium">Hasil Pencarian</span>
            </nav>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-slate-800">Hasil Pencarian</h1>
            <p id="searchMeta" class="text-slate-500 text-sm mt-1"></p>
        </div>

        <div id="searchResultGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"></div>
        <div id="emptyState" class="hidden text-center py-16">
            <div class="text-5xl mb-3">🔎</div>
            <p class="text-lg font-semibold text-slate-700">Produk tidak ditemukan</p>
            <p class="text-slate-500 text-sm mt-1">Coba kata kunci lain yang lebih umum.</p>
        </div>
    </section>
@endsection

@section('script')
    <script>
        const query = @json($query);
        const allProducts = [
            { name: 'Kemeja Oxford Slim Fit', price: 189000, originalPrice: 270000, sold: 1245, image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400&h=400&fit=crop' },
            { name: 'Sneakers Urban Street', price: 459000, originalPrice: 650000, sold: 875, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop' },
            { name: 'Smart Watch Series 5', price: 1299000, originalPrice: 1800000, sold: 892, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop' },
            { name: 'Skincare Serum Vitamin C', price: 189000, originalPrice: 250000, sold: 5678, image: 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop' },
            { name: 'Wireless Earbuds Pro', price: 599000, originalPrice: 850000, sold: 3210, image: 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=400&h=400&fit=crop' },
            { name: 'Hoodie Oversized Fleece', price: 299000, originalPrice: 399000, sold: 4321, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop' },
            { name: 'Kamera Mirrorless Entry', price: 5499000, originalPrice: 6800000, sold: 234, image: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&h=400&fit=crop' },
            { name: 'Yoga Mat Premium', price: 349000, originalPrice: 450000, sold: 1105, image: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=400&h=400&fit=crop' },
            { name: 'Lip Gloss Set Korea', price: 89000, originalPrice: 120000, sold: 540, image: 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=400&h=400&fit=crop' },
            { name: 'Blender Portable Mini', price: 149000, originalPrice: 199000, sold: 789, image: 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=400&h=400&fit=crop' },
        ];

        const cleanQuery = String(query || '').trim().toLowerCase();
        const result = cleanQuery ? allProducts.filter((p) => p.name.toLowerCase().includes(cleanQuery)) : allProducts;
        document.getElementById('searchMeta').textContent = cleanQuery
            ? `Menampilkan ${result.length} hasil untuk "${query}"`
            : `Menampilkan ${result.length} produk`;

        const grid = document.getElementById('searchResultGrid');
        const empty = document.getElementById('emptyState');
        if (!result.length) {
            empty.classList.remove('hidden');
        } else {
            grid.innerHTML = result.map((p) => `
                <a href="{{ route('frontend.detail-produk') }}" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 card-hover group">
                    <div class="relative">
                        <img src="${p.image}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300" alt="${p.name}" />
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-semibold text-slate-800 line-clamp-2 min-h-[40px]">${p.name}</p>
                        <div class="mt-2">
                            <p class="text-base font-bold text-slate-900">Rp ${p.price.toLocaleString('id-ID')}</p>
                            <p class="text-xs text-slate-400 line-through">Rp ${p.originalPrice.toLocaleString('id-ID')}</p>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">${p.sold.toLocaleString('id-ID')} terjual</p>
                    </div>
                </a>
            `).join('');
        }
    </script>
@endsection

