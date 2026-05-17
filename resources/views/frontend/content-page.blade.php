@extends('layouts.user')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    @php
        $isPost = $page->type === 'post';
        $readMinutes = max(1, ceil(str_word_count(strip_tags((string) $page->content)) / 180));
        $relatedPosts = $relatedPosts ?? collect();
    @endphp

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
    </style>
@endsection
