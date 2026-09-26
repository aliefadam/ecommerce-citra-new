@extends('layouts.user')

@section('title', 'Flash Sale - ' . ($appStoreName ?? config('app.name')))
@section('meta_description', 'Temukan promo dan harga spesial produk pilihan di '.($appStoreName ?? config('app.name')).'.')
@section('canonical', route('frontend.flash-sale'))

@section('style')
    <style>
        .sale-masthead {
            position: relative; overflow: hidden; border-block: 1px solid #dbe3ed;
            background: linear-gradient(90deg, rgb(8 37 87 / .035) 1px, transparent 1px) 0 0 / 32px 32px,
                linear-gradient(rgb(8 37 87 / .035) 1px, transparent 1px) 0 0 / 32px 32px,
                linear-gradient(110deg, #fff 0%, #f3f8fd 64%, #eaf2fb 100%);
        }
        .sale-masthead::before { position: absolute; inset: 0 auto 0 0; width: 5px; background: #f02046; content: ''; }
        .sale-timer { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .5rem; }
        .sale-timer-unit {
            min-width: 4.25rem; border-radius: 9px; background: linear-gradient(145deg, #103a69, #061d3b);
            padding: .65rem .55rem; color: #fff; text-align: center; box-shadow: 0 7px 14px rgb(8 38 76 / .16);
        }
        .sale-product-card {
            display: flex; min-width: 0; height: 100%; flex-direction: column; overflow: hidden;
            border: 1px solid #dbe3ed; border-radius: 8px; background: #fff;
            box-shadow: 0 1px 2px rgb(15 23 42 / .025);
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .sale-product-card:hover { transform: translateY(-2px); border-color: #b9c9de; box-shadow: 0 12px 24px rgb(15 45 86 / .09); }
        .sale-product-media { position: relative; display: block; aspect-ratio: 1.18 / 1; overflow: hidden; background: linear-gradient(145deg, #fff 55%, #f8fafc); }
        .sale-product-media img { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
        .sale-product-card:hover .sale-product-media img { transform: scale(1.04); }
        .sale-progress { height: .45rem; overflow: hidden; border-radius: 999px; background: #e4eaf2; }
        .sale-progress > span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #fb3658, #f02046); }
        @media (max-width: 639px) {
            .sale-product-media { aspect-ratio: 1 / 1; }
            .sale-timer-unit { min-width: 0; padding: .55rem .4rem; }
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

    <section class="sale-masthead">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10">
            <div class="grid items-center gap-7 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-12">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-rose-600">
                        <i class="ri-flashlight-fill text-xl"></i>
                        Promo Terbatas
                    </div>
                    <h1 class="max-w-3xl text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                        Harga proyek lebih hemat,<br class="hidden sm:block"> selama stok masih tersedia.
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Pilihan produk industri dengan harga khusus dalam periode terbatas. Cek ketersediaan dan amankan kebutuhan Anda sebelum promo berakhir.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                        <a href="#flash-sale-list"
                            class="inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-bold text-white transition-colors hover:bg-blue-800">
                            Lihat penawaran
                        </a>
                        <a href="{{ route('frontend.kategori') }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-blue-400 hover:text-blue-700">
                            Semua produk
                        </a>
                    </div>

                    <div class="mt-5 flex items-center gap-5">
                        <div>
                            <p class="text-xs text-slate-500">Campaign aktif</p>
                            <p class="text-xl font-extrabold text-slate-950">{{ $campaignCount }}</p>
                        </div>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <div>
                            <p class="text-xs text-slate-500">Produk promo</p>
                            <p class="text-xl font-extrabold text-slate-950">{{ $totalItems }}</p>
                        </div>
                    </div>
                </div>

                <div class="lg:justify-self-end w-full max-w-md">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 text-slate-900 shadow-[0_14px_35px_rgba(15,45,86,.1)]">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-950 text-white">
                                <i class="ri-timer-flash-fill text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-500">Sedang Berlangsung</p>
                                <h2 class="text-xl font-extrabold text-slate-900">{{ $firstCampaign['name'] ?? 'Flash Sale Aktif' }}</h2>
                            </div>
                        </div>
                        <div data-end-at="{{ $firstCampaign['end_at'] ?? '' }}">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-3">Hitung Mundur Promo</p>
                            <div class="sale-timer">
                                <div class="sale-timer-unit">
                                    <div class="text-2xl font-extrabold fs-hours">00</div>
                                    <div class="text-[11px] uppercase tracking-wide text-red-100">Jam</div>
                                </div>
                                <div class="sale-timer-unit">
                                    <div class="text-2xl font-extrabold fs-minutes">00</div>
                                    <div class="text-[11px] uppercase tracking-wide text-red-100">Menit</div>
                                </div>
                                <div class="sale-timer-unit">
                                    <div class="text-2xl font-extrabold fs-seconds">00</div>
                                    <div class="text-[11px] uppercase tracking-wide text-red-100">Detik</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="flash-sale-list" class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 sm:py-10">
        @forelse (($flashSaleCampaigns ?? []) as $campaign)
            <div>
                <div class="mb-5 border-b border-slate-200 pb-4"
                    data-end-at="{{ $campaign['end_at'] ?? '' }}">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-950">
                                <i class="ri-flashlight-fill text-xl text-rose-400"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">{{ $campaign['name'] }}</h2>
                                <p class="text-sm text-slate-500">Harga berlaku selama periode promo dan kuota tersedia.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm text-slate-500">Berakhir:</span>
                            <span class="rounded-md bg-blue-950 px-2.5 py-1 text-sm font-bold text-white fs-hours">00</span>
                            <span class="font-bold text-slate-400">:</span>
                            <span class="rounded-md bg-blue-950 px-2.5 py-1 text-sm font-bold text-white fs-minutes">00</span>
                            <span class="font-bold text-slate-400">:</span>
                            <span class="rounded-md bg-blue-950 px-2.5 py-1 text-sm font-bold text-white fs-seconds">00</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                    @foreach (($campaign['items'] ?? []) as $fs)
                        <article class="sale-product-card">
                            <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="sale-product-media">
                                <img src="{{ $fs['image'] }}" alt="{{ $fs['name'] }}" loading="lazy" />
                                <span class="absolute left-2.5 top-2.5 rounded-md bg-rose-500 px-2 py-1 text-[10px] font-bold text-white shadow-sm">-{{ $fs['discountPercent'] }}%</span>
                            </a>
                            <div class="flex flex-1 flex-col p-2.5 sm:p-3">
                                <a href="{{ url('/detail-produk/' . $fs['slug']) }}" class="text-sm font-semibold text-slate-800 hover:text-blue-600 line-clamp-2 min-h-[40px] transition-colors">{{ $fs['name'] }}</a>
                                <p class="text-[11px] text-slate-500 mt-1">{{ number_format($fs['sold']) }} terjual</p>
                                <div class="sale-progress mt-2">
                                    <span style="width:{{ 100 - $fs['remainingPercent'] }}%"></span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">Sisa {{ $fs['remainingPercent'] }}%</p>
                                <div class="mt-auto pt-2">
                                    <p class="text-base font-bold text-red-500">Rp {{ number_format($fs['price'], 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-400 line-through">Rp {{ number_format($fs['originalPrice'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </article>
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

