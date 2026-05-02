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

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-8">
        @forelse (($flashSaleCampaigns ?? []) as $campaign)
            <div>
                <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-3xl p-6 border border-red-100 mb-6"
                    data-end-at="{{ $campaign['end_at'] ?? '' }}">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                <i class="ri-flashlight-fill text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-extrabold text-slate-800">{{ $campaign['name'] }}</h1>
                                <p class="text-slate-500 text-sm">Penawaran terbatas, jangan sampai kehabisan</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-500">Berakhir:</span>
                            <span class="bg-red-500 text-white text-sm font-bold px-2.5 py-1 rounded-lg fs-hours">00</span>
                            <span class="text-red-400 font-bold">:</span>
                            <span class="bg-red-500 text-white text-sm font-bold px-2.5 py-1 rounded-lg fs-minutes">00</span>
                            <span class="text-red-400 font-bold">:</span>
                            <span class="bg-red-500 text-white text-sm font-bold px-2.5 py-1 rounded-lg fs-seconds">00</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach (($campaign['items'] ?? []) as $fs)
                        <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-red-100 card-hover group">
                            <div class="relative">
                                <img src="{{ $fs['image'] }}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $fs['name'] }}" />
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-{{ $fs['discountPercent'] }}%</span>
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-semibold text-slate-800 line-clamp-2 min-h-[40px]">{{ $fs['name'] }}</p>
                                <div class="mt-2">
                                    <p class="text-base font-bold text-red-500">Rp {{ number_format($fs['price'], 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-400 line-through">Rp {{ number_format($fs['originalPrice'], 0, ',', '.') }}</p>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-2">{{ number_format($fs['sold']) }} terjual</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-red-100 p-6 text-center text-sm text-slate-500">
                Belum ada flash sale aktif saat ini.
            </div>
        @endforelse
    </section>
@endsection

@section('script')
    <script>
        function updateCampaignTimer(container) {
            const endAt = container.getAttribute('data-end-at');
            const end = endAt ? new Date(endAt) : null;
            const now = new Date();
            const diff = end ? Math.max(end - now, 0) : 0;

            const hh = String(Math.floor(diff / 3600000)).padStart(2, '0');
            const mm = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
            const ss = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');

            const h = container.querySelector('.fs-hours');
            const m = container.querySelector('.fs-minutes');
            const s = container.querySelector('.fs-seconds');
            if (h) h.textContent = hh;
            if (m) m.textContent = mm;
            if (s) s.textContent = ss;
        }

        function updateAllCampaignTimers() {
            document.querySelectorAll('[data-end-at]').forEach(updateCampaignTimer);
        }

        setInterval(updateAllCampaignTimers, 1000);
        updateAllCampaignTimers();
    </script>
@endsection

