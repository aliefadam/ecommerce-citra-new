@extends('layouts.user')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . ($appStoreName ?? 'Ecommerce Citra'))
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(trim(strip_tags((string) ($page->excerpt ?: $page->content))), 160))
@section('canonical', $page->public_url)
@section('og_image', $page->hero_image ?? '')
@section('og_type', $page->type === 'post' ? 'article' : 'website')

@if ($page->type === 'post')
    @push('structured_data')
        @php
            $articleSchema = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $page->title,
                'description' => $page->meta_description ?: $page->excerpt,
                'image' => $page->hero_image ?: null,
                'datePublished' => $page->published_at?->toAtomString(),
                'dateModified' => $page->updated_at?->toAtomString(),
                'mainEntityOfPage' => $page->public_url,
                'author' => ['@type' => 'Organization', 'name' => $appStoreName ?? 'Ecommerce Citra'],
                'publisher' => ['@type' => 'Organization', 'name' => $appStoreName ?? 'Ecommerce Citra'],
            ]);
        @endphp
        <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endpush
@endif

@section('content')
    @include('partials.navbar-user')

    @php
        $isPost = $page->type === 'post';
        $readMinutes = max(1, ceil(str_word_count(strip_tags((string) $page->content)) / 180));
        $relatedPosts = $relatedPosts ?? collect();
        $informationPages = [
            'cara-belanja' => ['label' => 'Cara Belanja', 'icon' => 'shopping-cart'],
            'kebijakan-privasi' => ['label' => 'Kebijakan Privasi', 'icon' => 'shield-check'],
            'syarat-ketentuan' => ['label' => 'Syarat & Ketentuan', 'icon' => 'document-signed'],
        ];
    @endphp

    @if ($isPost)

    <main class="bg-slate-50">
        <section class="relative overflow-hidden bg-white">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-blue-50 to-transparent pointer-events-none"></div>
            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
                <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <div>
                        <a href="{{ $isPost ? route('frontend.blog.index') : route('frontend.index') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600 mb-5">
                            <i class="fi fi-rr-arrow-small-left text-sm leading-none"></i>
                            {{ $isPost ? 'Blog' : 'Halaman' }}
                        </a>
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-950 leading-[1.05] tracking-tight">{{ $page->title }}</h1>
                        @if ($page->excerpt)
                            <p class="mt-5 text-base sm:text-lg leading-8 text-slate-600">{{ $page->excerpt }}</p>
                        @endif
                        <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                                <i class="fi fi-rr-user text-xs"></i>
                                {{ $appStoreName ?? 'BOQ' }}
                            </span>
                            @if ($isPost && $page->published_at)
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                                    <i class="fi fi-rr-calendar text-xs"></i>
                                    {{ $page->published_at->format('d M Y') }}
                                </span>
                            @endif
                            @if ($isPost)
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                                    <i class="fi fi-rr-clock-three text-xs"></i>
                                    {{ $readMinutes }} menit baca
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="relative">
                        @if ($page->hero_image)
                            <img src="{{ $page->hero_image }}" alt="{{ $page->title }}"
                                class="aspect-[16/11] w-full rounded-3xl object-cover shadow-2xl shadow-slate-200 border border-white">
                        @else
                            <div class="aspect-[16/11] w-full rounded-3xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-600 shadow-2xl shadow-slate-200 border border-white"></div>
                        @endif
                        <div class="absolute -bottom-4 left-6 right-6 rounded-2xl border border-white/70 bg-white/90 p-4 shadow-xl backdrop-blur">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">{{ $isPost ? 'Artikel Teknik' : 'Informasi' }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">{{ $appStoreName ?? 'BOQ' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start">
                <article class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-8 lg:p-10">
                    <div class="content-body text-slate-700">
                        @if ($page->content)
                            {!! $page->content !!}
                        @else
                            <p>Konten belum tersedia.</p>
                        @endif
                    </div>
                </article>

                <aside class="space-y-4 lg:sticky lg:top-24">
                    <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600 mb-3">Butuh Bantuan?</p>
                        <h2 class="text-lg font-extrabold text-slate-900">Konsultasi kebutuhan teknik</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Tim kami siap membantu memilih produk yang sesuai untuk kebutuhan proyek atau industri.</p>
                        @if (!empty($appStoreSettings['social_whatsapp']))
                            <a href="{{ $appStoreSettings['social_whatsapp'] }}" target="_blank" rel="noopener noreferrer"
                                class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-bold text-white hover:bg-blue-700 transition-colors">
                                Hubungi Kami
                            </a>
                        @endif
                    </div>

                    @if ($isPost && $relatedPosts->isNotEmpty())
                        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400 mb-3">Artikel Lainnya</p>
                            <div class="space-y-4">
                                @foreach ($relatedPosts as $related)
                                    <a href="{{ route('frontend.blog.show', $related->slug) }}" class="block group">
                                        <p class="text-sm font-bold leading-snug text-slate-800 group-hover:text-blue-600 transition-colors">{{ $related->title }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ optional($related->published_at)->format('d M Y') ?: 'Blog' }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </section>
    </main>
    @else
        <main class="info-page">
            <nav class="info-breadcrumb ec-container" aria-label="Breadcrumb">
                <a href="{{ route('frontend.index') }}"><i class="fi fi-rr-home" aria-hidden="true"></i><span>Beranda</span></a>
                <i class="fi fi-rr-angle-small-right" aria-hidden="true"></i>
                <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}">Pusat Bantuan</a>
                <i class="fi fi-rr-angle-small-right" aria-hidden="true"></i>
                <span aria-current="page">{{ $page->title }}</span>
            </nav>

            <section class="info-hero" aria-labelledby="infoPageTitle">
                <img src="{{ asset('imgs/help-center/hero-help-center.webp') }}" alt="" width="2048" height="768" fetchpriority="high" aria-hidden="true">
                <div class="info-hero-shade" aria-hidden="true"></div>
                <div class="info-hero-content ec-container">
                    <p class="info-hero-label">Informasi &amp; Panduan</p>
                    <h1 id="infoPageTitle">{{ $page->title }}</h1>
                    @if ($page->excerpt)
                        <p>{{ $page->excerpt }}</p>
                    @endif
                </div>
            </section>

            <div class="info-layout ec-container">
                <aside class="info-sidebar" aria-labelledby="infoNavigationTitle">
                    <h2 id="infoNavigationTitle">Informasi Pelanggan</h2>
                    <nav aria-label="Halaman informasi pelanggan">
                        @foreach ($informationPages as $slug => $informationPage)
                            <a href="{{ route('frontend.pages.show', $slug) }}" @class(['is-active' => $page->slug === $slug]) @if($page->slug === $slug) aria-current="page" @endif>
                                <i class="fi fi-rr-{{ $informationPage['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $informationPage['label'] }}</span>
                                <i class="fi fi-rr-angle-small-right" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </nav>

                    <div class="info-sidebar-help">
                        <i class="fi fi-rr-headset" aria-hidden="true"></i>
                        <div>
                            <h3>Masih butuh bantuan?</h3>
                            <p>Temukan jawaban lain di pusat bantuan kami.</p>
                            <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}">Buka Pusat Bantuan <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </aside>

                <article class="info-article">
                    <header>
                        <p>BOQ Customer Care</p>
                        <h2>{{ $page->title }}</h2>
                    </header>
                    <div class="content-body">
                        @if ($page->content)
                            {!! $page->content !!}
                        @else
                            <p>Konten belum tersedia.</p>
                        @endif
                    </div>
                </article>
            </div>

            <section class="info-support ec-container" aria-labelledby="infoSupportTitle">
                <div>
                    <i class="fi fi-rr-comment-alt" aria-hidden="true"></i>
                    <div>
                        <h2 id="infoSupportTitle">Ada pertanyaan lain?</h2>
                        <p>Tim kami siap membantu kebutuhan pemesanan, produk, pembayaran, dan pengiriman Anda.</p>
                    </div>
                </div>
                <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}">Kunjungi Pusat Bantuan <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i></a>
            </section>
        </main>
    @endif
@endsection

@section('style')
    <style>
        .content-body {
            font-size: 1rem;
            line-height: 1.9;
        }

        .content-body > *:first-child {
            margin-top: 0;
        }

        .content-body h2,
        .content-body h3,
        .content-body h4 {
            margin-top: 2rem;
            margin-bottom: 0.8rem;
            color: #0f172a;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: 0;
        }

        .content-body h2 {
            font-size: clamp(1.45rem, 2vw, 2rem);
        }

        .content-body h3 {
            font-size: 1.25rem;
        }

        .content-body p {
            margin-bottom: 1.1rem;
        }

        .content-body ul,
        .content-body ol {
            margin: 1.1rem 0;
            padding-left: 1.35rem;
        }

        .content-body ul {
            list-style: disc;
        }

        .content-body ol {
            list-style: decimal;
        }

        .content-body li {
            margin-bottom: 0.55rem;
            padding-left: 0.15rem;
        }

        .content-body blockquote {
            margin: 1.5rem 0;
            border-left: 4px solid #2563eb;
            background: #eff6ff;
            border-radius: 0 1rem 1rem 0;
            padding: 1rem 1.25rem;
            color: #1e3a8a;
            font-weight: 600;
        }

        .content-body a {
            color: #2563eb;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
        }

        .content-body img {
            margin: 1.5rem 0;
            border-radius: 1.25rem;
            border: 1px solid #e2e8f0;
        }

        .info-page { background: #f8fafc; color: #0b1f43; }
        .info-breadcrumb { display: flex; min-height: 48px; align-items: center; gap: .55rem; overflow: hidden; color: #64748b; font-size: .75rem; white-space: nowrap; }
        .info-breadcrumb a { display: inline-flex; align-items: center; gap: .45rem; color: #475569; }
        .info-breadcrumb a:hover { color: #174d91; }
        .info-breadcrumb > i { flex: 0 0 auto; font-size: .7rem; }
        .info-breadcrumb span[aria-current=page] { overflow: hidden; text-overflow: ellipsis; }
        .info-hero { position: relative; min-height: 220px; overflow: hidden; border-block: 1px solid #e5e7eb; background: #eef3f8; }
        .info-hero > img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .info-hero-shade { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(247,250,252,.96) 0%, rgba(247,250,252,.84) 50%, rgba(247,250,252,.12) 82%); }
        .info-hero-content { position: relative; z-index: 1; display: flex; min-height: 220px; flex-direction: column; justify-content: center; padding-block: 2rem; }
        .info-hero-label { color: #0b5fc7; font-size: .72rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }
        .info-hero h1 { max-width: 44rem; margin-top: .45rem; color: #0b1f43; font-size: clamp(2rem,4vw,2.75rem); font-weight: 800; letter-spacing: -.04em; line-height: 1.05; }
        .info-hero-content > p:last-child { max-width: 41rem; margin-top: .65rem; color: #52627a; font-size: .88rem; line-height: 1.65; }
        .info-layout { display: grid; grid-template-columns: 244px minmax(0,1fr); gap: 24px; padding-block: 24px; }
        .info-sidebar { align-self: start; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; padding: 14px 10px; }
        .info-sidebar > h2 { padding: 0 8px 9px; font-size: .95rem; font-weight: 800; }
        .info-sidebar nav { display: grid; }
        .info-sidebar nav > a { display: grid; grid-template-columns: 20px minmax(0,1fr) 12px; min-height: 46px; align-items: center; gap: .7rem; border-left: 3px solid transparent; padding: .55rem .65rem; color: #213555; font-size: .76rem; }
        .info-sidebar nav > a > i:first-child { color: #163f78; font-size: 1rem; }
        .info-sidebar nav > a > i:last-child { color: #94a3b8; font-size: .72rem; }
        .info-sidebar nav > a:hover { background: #edf4fd; color: #0d4fa6; }
        .info-sidebar nav > a.is-active { border-left-color: #1765d1; background: #e5f0ff; color: #0b55ba; font-weight: 700; }
        .info-sidebar-help { display: flex; gap: .75rem; margin: 16px 4px 2px; border: 1px solid #dce3ec; border-radius: 7px; background: #fff; padding: 14px 12px; }
        .info-sidebar-help > i { color: #0b62d2; font-size: 1.3rem; }
        .info-sidebar-help h3 { font-size: .78rem; font-weight: 800; line-height: 1.35; }
        .info-sidebar-help p { margin-top: .25rem; color: #64748b; font-size: .7rem; line-height: 1.5; }
        .info-sidebar-help a { display: inline-flex; align-items: center; gap: .25rem; margin-top: .55rem; color: #0b5fc7; font-size: .7rem; font-weight: 700; }
        .info-article { min-width: 0; overflow: hidden; border: 1px solid #dce3ec; border-radius: 8px; background: #fff; }
        .info-article > header { border-bottom: 1px solid #e5e9ef; padding: 20px 24px; background: linear-gradient(135deg,#f8fbff,#fff); }
        .info-article > header p { color: #0b5fc7; font-size: .67rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }
        .info-article > header h2 { margin-top: .3rem; color: #102449; font-size: 1.2rem; font-weight: 800; }
        .info-article .content-body { padding: 24px; color: #52627a; font-size: .85rem; line-height: 1.8; }
        .info-article .content-body h2, .info-article .content-body h3, .info-article .content-body h4 { color: #102449; }
        .info-article .content-body h2 { font-size: 1.05rem; }
        .info-article .content-body h3 { font-size: .95rem; }
        .info-article .content-body a { color: #0b5fc7; }
        .info-support { display: flex; min-height: 108px; align-items: center; justify-content: space-between; gap: 1.5rem; margin-bottom: 24px; border-radius: 8px; background: #082f63; padding: 22px 28px; color: #fff; }
        .info-support > div { display: flex; align-items: center; gap: 1rem; }
        .info-support > div > i { font-size: 1.8rem; }
        .info-support h2 { font-size: 1rem; font-weight: 800; }
        .info-support p { margin-top: .2rem; color: #dce8f7; font-size: .76rem; line-height: 1.55; }
        .info-support > a { display: inline-flex; min-height: 40px; flex: 0 0 auto; align-items: center; justify-content: center; gap: .4rem; border-radius: 6px; background: #fff; padding: .55rem 1rem; color: #0b55ba; font-size: .72rem; font-weight: 700; }

        @media (max-width: 767px) {
            .info-breadcrumb { min-height: 40px; }
            .info-hero { min-height: 205px; }
            .info-hero > img { object-position: 68% center; }
            .info-hero-shade { background: rgba(248,250,252,.86); }
            .info-hero-content { min-height: 205px; }
            .info-hero h1 { font-size: 1.8rem; }
            .info-hero-content > p:last-child { font-size: .78rem; }
            .info-layout { display: block; padding-block: 16px; }
            .info-sidebar { margin-bottom: 16px; }
            .info-sidebar-help { display: none; }
            .info-article > header { padding: 18px; }
            .info-article .content-body { padding: 18px; font-size: .8rem; }
            .info-support { display: block; margin-bottom: 20px; padding: 20px; }
            .info-support > a { width: 100%; margin-top: 1rem; }
        }
    </style>
@endsection
