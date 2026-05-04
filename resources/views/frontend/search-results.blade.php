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
                <a href="{{ url('/detail-produk') }}/${p.slug}" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 card-hover group">
                    <div class="relative">
                        <img src="${p.image}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300" alt="${p.name}" />
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-semibold text-slate-800 line-clamp-2 min-h-[40px]">${p.name}</p>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">${p.category}${p.category_detail ? ' • ' + p.category_detail : ''}</p>
                        ${p.variant ? `<p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">${p.variant}</p>` : ''}
                        <div class="mt-2">
                            <p class="text-base font-bold text-slate-900">Rp ${p.price.toLocaleString('id-ID')}</p>
                            ${p.originalPrice > p.price ? `<p class="text-xs text-slate-400 line-through">Rp ${p.originalPrice.toLocaleString('id-ID')}</p>` : ''}
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <p class="text-[11px] text-slate-500">${p.sold.toLocaleString('id-ID')} terjual</p>
                            <p class="text-[11px] text-slate-500">${Number(p.rating || 0).toFixed(1)} (${Number(p.reviews || 0).toLocaleString('id-ID')})</p>
                        </div>
                    </div>
                </a>
            `).join('');
        }
    </script>
@endsection

