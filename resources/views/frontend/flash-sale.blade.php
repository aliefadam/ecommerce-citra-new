@extends('layouts.user')

@section('title', 'Flash Sale - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12); }
        .hero-sale {
            background:
                radial-gradient(circle at top right, rgba(251, 191, 36, 0.28), transparent 28%),
                radial-gradient(circle at left center, rgba(239, 68, 68, 0.22), transparent 32%),
                linear-gradient(135deg, #7f1d1d 0%, #b91c1c 38%, #ea580c 100%);
        }
        .glass-card {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
            backdrop-filter: blur(8px);
        }
    </style>
@endsection
@section('content')
    @include('partials.navbar-user')

    @php
        $campaignCount = collect($flashSaleCampaigns ?? [])->count();
        $totalItems = collect($flashSaleCampaigns ?? [])->sum(fn($campaign) => count($campaign['items'] ?? []));
        $firstCampaign = collect($flashSaleCampaigns ?? [])->first();
    @endphp

    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-800 font-medium">Flash Sale</span>
            </nav>
        </div>
    </div>

    <section class="hero-sale text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/12 px-4 py-2 text-sm font-semibold tracking-wide uppercase text-orange-100 border border-white/10 mb-5">
                        <i class="ri-fire-fill text-yellow-300"></i>
                        Promo Terbatas
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight mb-4">
                        Flash Sale Spesial,<br class="hidden sm:block"> Harga Turun Saatnya Checkout
                    </h1>
                    <p class="max-w-2xl text-sm sm:text-lg text-orange-50/90 leading-7 mb-7">
                        Nikmati promo pilihan dengan harga yang lebih hemat, stok terbatas, dan periode penawaran yang bisa berakhir kapan saja. Kalau kamu sudah incar produknya, ini momen yang pas buat eksekusi.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 mb-8">
                        <a href="#flash-sale-list"
                            class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-bold text-red-600 hover:bg-orange-50 transition-colors shadow-lg">
                            Lihat Promo Sekarang
                        </a>
                        <a href="{{ route('frontend.kategori') }}"
                            class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10 transition-colors">
                            Lanjut Belanja
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-3xl">
                        <div class="glass-card rounded-2xl px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-orange-100/80 mb-1">Campaign Aktif</p>
                            <p class="text-2xl font-extrabold">{{ $campaignCount }}</p>
                        </div>
                        <div class="glass-card rounded-2xl px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-orange-100/80 mb-1">Produk Promo</p>
                            <p class="text-2xl font-extrabold">{{ $totalItems }}</p>
                        </div>
                        <div class="glass-card rounded-2xl px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-orange-100/80 mb-1">Status</p>
                            <p class="text-2xl font-extrabold">Live</p>
                        </div>
                    </div>
                </div>

                <div class="lg:justify-self-end w-full max-w-md">
                    <div class="bg-white text-slate-900 rounded-3xl p-6 shadow-2xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-red-500 flex items-center justify-center text-white shadow-lg shadow-red-200">
                                <i class="ri-timer-flash-fill text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-500">Sedang Berlangsung</p>
                                <h2 class="text-xl font-extrabold text-slate-900">{{ $firstCampaign['name'] ?? 'Flash Sale Aktif' }}</h2>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 leading-6 mb-5">
                            Jangan tunggu sampai stok habis. Promo yang sedang aktif ini punya potensi konversi paling tinggi karena diskon dan urgensinya jelas.
                        </p>
                        <div class="rounded-2xl bg-gradient-to-r from-red-50 to-orange-50 border border-red-100 p-4" data-end-at="{{ $firstCampaign['end_at'] ?? '' }}">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-3">Hitung Mundur Promo</p>
                            <div class="flex items-center justify-between gap-2 text-center">
                                <div class="flex-1 rounded-2xl bg-red-500 text-white py-3">
                                    <div class="text-2xl font-extrabold fs-hours">00</div>
                                    <div class="text-[11px] uppercase tracking-wide text-red-100">Jam</div>
                                </div>
                                <div class="text-red-400 font-bold text-xl">:</div>
                                <div class="flex-1 rounded-2xl bg-red-500 text-white py-3">
                                    <div class="text-2xl font-extrabold fs-minutes">00</div>
                                    <div class="text-[11px] uppercase tracking-wide text-red-100">Menit</div>
                                </div>
                                <div class="text-red-400 font-bold text-xl">:</div>
                                <div class="flex-1 rounded-2xl bg-red-500 text-white py-3">
                                    <div class="text-2xl font-extrabold fs-seconds">00</div>
                                    <div class="text-[11px] uppercase tracking-wide text-red-100">Detik</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-xs text-slate-500 leading-5">
                            *Waktu promo dapat berubah sesuai campaign yang sedang berjalan di sistem.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="w-11 h-11 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mb-4">
                    <i class="ri-price-tag-3-fill text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Harga Lagi Turun</h3>
                <p class="text-sm text-slate-600 leading-6">Produk promo dipilih untuk mendorong pembelian cepat dengan harga lebih kompetitif.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="w-11 h-11 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mb-4">
                    <i class="ri-alarm-warning-fill text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Waktu Terbatas</h3>
                <p class="text-sm text-slate-600 leading-6">Campaign berjalan dengan countdown aktif, jadi urgency-nya jelas untuk mendorong checkout.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center mb-4">
                    <i class="ri-shopping-bag-3-fill text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Siap Langsung Belanja</h3>
                <p class="text-sm text-slate-600 leading-6">Pengunjung bisa langsung klik ke detail produk dan lanjut checkout tanpa alur tambahan.</p>
            </div>
        </div>
    </section>

    <section id="flash-sale-list" class="max-w-7xl mx-auto px-4 sm:px-6 pb-10 space-y-8">
        @forelse (($flashSaleCampaigns ?? []) as $campaign)
            <div>
                <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-3xl p-6 border border-red-100 mb-6"
                    data-end-at="{{ $campaign['end_at'] ?? '' }}">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                                <i class="ri-flashlight-fill text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-extrabold text-slate-800">{{ $campaign['name'] }}</h2>
                                <p class="text-slate-500 text-sm">Penawaran terbatas, jangan sampai kehabisan</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
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
                        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-red-100 card-hover group flex flex-col">
                            <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="relative block overflow-hidden">
                                <img src="{{ $fs['image'] }}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $fs['name'] }}" />
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-{{ $fs['discountPercent'] }}%</span>
                            </a>
                            <div class="p-3 flex-1 flex flex-col">
                                <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="text-sm font-semibold text-slate-800 hover:text-blue-600 line-clamp-2 min-h-[40px] transition-colors">{{ $fs['name'] }}</a>
                                <p class="text-[11px] text-slate-500 mt-1">{{ number_format($fs['sold']) }} terjual</p>
                                <div class="mt-2 w-full bg-red-100 rounded-full h-1.5">
                                    <div class="bg-red-500 h-1.5 rounded-full" style="width:{{ 100 - $fs['remainingPercent'] }}%"></div>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">Sisa {{ $fs['remainingPercent'] }}%</p>
                                <div class="mt-auto pt-2">
                                    <p class="text-base font-bold text-red-500">Rp {{ number_format($fs['price'], 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-400 line-through">Rp {{ number_format($fs['originalPrice'], 0, ',', '.') }}</p>
                                </div>
                                <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="mt-2 w-full bg-blue-50 hover:bg-blue-500 text-blue-600 hover:text-white text-xs font-semibold py-2 rounded-full transition-all border border-blue-200 hover:border-blue-500 flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 113 12a9 9 0 0118 0z"/></svg>
                                    Detail Produk
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-red-100 p-10 text-center text-sm text-slate-500 shadow-sm">
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

