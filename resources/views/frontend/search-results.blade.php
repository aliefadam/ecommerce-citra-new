@extends('layouts.user')

@section('title', 'Hasil Pencarian - ' . ($appStoreName ?? 'Ecommerce Citra'))

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

        <div id="searchResultGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-4"></div>
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
        const allProducts = @json($results ?? []);

        const cleanQuery = String(query || '').trim().toLowerCase();
        const result = allProducts;
        document.getElementById('searchMeta').textContent = cleanQuery
            ? `Menampilkan ${result.length} hasil untuk "${query}"`
            : `Menampilkan ${result.length} produk`;

        const grid = document.getElementById('searchResultGrid');
        const empty = document.getElementById('emptyState');
        if (!result.length) {
            empty.classList.remove('hidden');
        } else {
            grid.innerHTML = result.map((p) => `
                <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 card-hover group flex flex-col">
                    <a href="{{ url('/detail-produk') }}/${p.slug}" class="relative block overflow-hidden aspect-square">
                        <img src="${p.image}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="${p.name}" />
                        ${p.originalPrice > p.price ? `<span class="absolute top-1.5 left-1.5 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">-${Math.round((1 - p.price / p.originalPrice) * 100)}%</span>` : ''}
                    </a>
                    <div class="p-2 flex-1 flex flex-col">
                        <a href="{{ url('/detail-produk') }}/${p.slug}" class="text-[11px] sm:text-xs font-semibold text-slate-800 hover:text-blue-600 line-clamp-2 leading-snug transition-colors">${p.name}</a>
                        <div class="flex items-center gap-0.5 mt-1">
                            <span class="text-yellow-400 text-[10px]">★</span>
                            <span class="text-[10px] font-medium text-slate-600">${Number(p.rating || 0).toFixed(1)}</span>
                            <span class="text-[10px] text-slate-400 ml-0.5">· ${p.sold.toLocaleString('id-ID')} terjual</span>
                        </div>
                        <div class="mt-auto pt-1">
                            <p class="text-xs sm:text-sm font-bold text-slate-900">Rp ${p.price.toLocaleString('id-ID')}</p>
                            ${p.originalPrice > p.price ? `<p class="text-[10px] text-slate-400 line-through">Rp ${p.originalPrice.toLocaleString('id-ID')}</p>` : ''}
                            <a href="{{ url('/detail-produk') }}/${p.slug}" class="mt-2 inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-[11px] sm:text-xs font-semibold text-blue-600 transition-colors hover:border-blue-500 hover:bg-blue-500 hover:text-white">Detail</a>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    </script>
@endsection
