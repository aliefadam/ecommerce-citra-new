@extends('layouts.user')

@section('title', 'Blog - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    <main class="bg-slate-50">
        <section class="relative overflow-hidden bg-white border-b border-slate-100">
            <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-blue-50 to-transparent pointer-events-none"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
                <p class="inline-flex rounded-full bg-blue-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-blue-600 mb-4">Blog</p>
                <div class="grid gap-5 lg:grid-cols-[1fr_420px] lg:items-end">
                    <div>
                        <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-950 leading-tight tracking-tight">Artikel & Informasi</h1>
                        <p class="mt-4 max-w-2xl text-base sm:text-lg leading-8 text-slate-600">Kumpulan insight, tips, dan update terbaru untuk kebutuhan teknik, proyek, dan industri.</p>
                    </div>
                    <div class="rounded-3xl border border-blue-100 bg-blue-50/70 p-5">
                        <p class="text-sm leading-7 text-blue-900">Temukan panduan produk, inspirasi penggunaan, dan informasi teknis yang membantu kamu memilih kebutuhan dengan lebih yakin.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    <a href="{{ route('frontend.blog.show', $post->slug) }}" class="group overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative aspect-[16/10] bg-slate-100 overflow-hidden">
                            @if ($post->hero_image)
                                <img src="{{ $post->hero_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-slate-950 via-blue-950 to-blue-600"></div>
                            @endif
                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600 shadow-sm">Blog</span>
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-3">
                                <span>{{ optional($post->published_at)->format('d M Y') ?: 'Artikel' }}</span>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span>{{ max(1, ceil(str_word_count(strip_tags((string) $post->content)) / 180)) }} min read</span>
                            </div>
                            <h2 class="text-lg font-extrabold text-slate-950 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $post->title }}</h2>
                            @if ($post->excerpt)
                                <p class="mt-3 text-sm leading-6 text-slate-500 line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                            <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-blue-600">
                                Baca Artikel
                                <i class="fi fi-rr-arrow-small-right leading-none"></i>
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-200 bg-white py-14 text-center text-slate-400">Belum ada artikel blog.</div>
                @endforelse
            </div>

            <div class="mt-8">{{ $posts->links() }}</div>
        </section>
    </main>
@endsection
