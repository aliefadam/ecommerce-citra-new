@extends('layouts.user')

@section('title', ($promo->title ?? 'Promo') . ' - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    <section class="bg-gradient-to-r from-orange-50 via-amber-50 to-yellow-50 border-b border-orange-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <div class="grid gap-8 lg:grid-cols-2 items-center">
                <div>
                    <p class="inline-flex rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-orange-600 shadow-sm mb-4">Promo Campaign</p>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-4">{{ $promo->title }}</h1>
                    @if ($promo->subtitle)
                        <p class="text-lg text-slate-700 mb-4">{{ $promo->subtitle }}</p>
                    @endif
                    @if ($promo->description)
                        <p class="text-sm sm:text-base text-slate-600 leading-7 mb-6">{!! nl2br(e($promo->description)) !!}</p>
                    @endif
                    <div class="flex flex-wrap gap-3 text-sm text-slate-500 mb-6">
                        @if ($promo->starts_at)
                            <span class="rounded-full border border-orange-200 bg-white px-4 py-2">Mulai: {{ $promo->starts_at->format('d M Y H:i') }}</span>
                        @endif
                        @if ($promo->ends_at)
                            <span class="rounded-full border border-orange-200 bg-white px-4 py-2">Berakhir: {{ $promo->ends_at->format('d M Y H:i') }}</span>
                        @endif
                    </div>
                    @if ($promo->cta_label && $promo->cta_url)
                        <a href="{{ $promo->cta_url }}" class="inline-flex items-center justify-center rounded-full bg-orange-500 px-6 py-3 text-sm font-bold text-white hover:bg-orange-600 transition-colors">{{ $promo->cta_label }}</a>
                    @endif
                </div>
                <div>
                    @if ($promo->hero_image)
                        <img src="{{ $promo->hero_image }}" alt="{{ $promo->title }}" class="w-full rounded-3xl shadow-xl border border-orange-100 object-cover">
                    @else
                        <div class="rounded-3xl border border-dashed border-orange-200 bg-white/60 min-h-[320px] flex items-center justify-center text-orange-400">Banner promo belum diatur</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
