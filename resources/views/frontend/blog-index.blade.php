@extends('layouts.user')

@section('title', 'Blog - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    <main class="bg-slate-50">
        <section class="bg-white border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
                <p class="inline-flex rounded-full bg-blue-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-blue-600 mb-4">Blog</p>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 leading-tight">Artikel & Informasi</h1>
                <p class="mt-4 max-w-2xl text-base sm:text-lg leading-8 text-slate-600">Kumpulan informasi, tips, dan update terbaru dari {{ $appStoreName ?? 'Ecommerce Citra' }}.</p>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    <a href="{{ route('frontend.blog.show', $post->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                        <div class="aspect-[16/10] bg-slate-100 overflow-hidden">
                            @if ($post->hero_image)
                                <img src="{{ $post->hero_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">No Image</div>
                            @endif
                        </div>
                        <div class="p-5">
                            <p class="text-xs font-semibold text-blue-600 mb-2">{{ optional($post->published_at)->format('d M Y') ?: 'Blog' }}</p>
                            <h2 class="text-lg font-bold text-slate-900 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $post->title }}</h2>
                            @if ($post->excerpt)
                                <p class="mt-2 text-sm leading-6 text-slate-500 line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-200 bg-white py-14 text-center text-slate-400">Belum ada artikel blog.</div>
                @endforelse
            </div>

            <div class="mt-8">{{ $posts->links() }}</div>
        </section>
    </main>

@endsection
