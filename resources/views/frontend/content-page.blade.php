@extends('layouts.user')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    <main class="bg-slate-50">
        <section class="bg-white border-b border-slate-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
                <div class="max-w-3xl">
                    <p class="inline-flex rounded-full bg-blue-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-blue-600 mb-4">
                        {{ $page->type === 'post' ? 'Blog' : 'Halaman' }}
                    </p>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 leading-tight">{{ $page->title }}</h1>
                    @if ($page->excerpt)
                        <p class="mt-4 text-base sm:text-lg leading-8 text-slate-600">{{ $page->excerpt }}</p>
                    @endif
                    @if ($page->type === 'post' && $page->published_at)
                        <p class="mt-4 text-sm text-slate-400">{{ $page->published_at->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
        </section>

        @if ($page->hero_image)
            <section class="max-w-5xl mx-auto px-4 sm:px-6 pt-8">
                <img src="{{ $page->hero_image }}" alt="{{ $page->title }}" class="w-full max-h-[460px] rounded-2xl object-cover shadow-sm border border-slate-100">
            </section>
        @endif

        <section class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-12">
            <article class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 sm:p-8">
                <div class="content-body text-slate-700 leading-8">
                    @if ($page->content)
                        {!! $page->content !!}
                    @else
                        <p>Konten belum tersedia.</p>
                    @endif
                </div>
            </article>
        </section>
    </main>

@endsection

@section('style')
    <style>
        .content-body h2,
        .content-body h3 {
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
            color: #0f172a;
            font-weight: 800;
            line-height: 1.25;
        }

        .content-body h2 {
            font-size: 1.5rem;
        }

        .content-body h3 {
            font-size: 1.2rem;
        }

        .content-body p {
            margin-bottom: 1rem;
        }

        .content-body ul,
        .content-body ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .content-body ul {
            list-style: disc;
        }

        .content-body ol {
            list-style: decimal;
        }

        .content-body a {
            color: #2563eb;
            font-weight: 600;
        }
    </style>
@endsection
