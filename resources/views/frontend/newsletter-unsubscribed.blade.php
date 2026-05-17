@extends('layouts.user')

@section('title', 'Unsubscribe Newsletter - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    <section class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 sm:p-10 shadow-sm text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 text-orange-500">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mb-3">Kamu berhasil unsubscribe</h1>
            <p class="text-slate-600 leading-7">
                Email <strong>{{ $subscriber->email }}</strong> sudah tidak akan menerima newsletter lagi dari {{ $appStoreName ?? 'Ecommerce Citra' }}.
            </p>
            <a href="{{ route('frontend.index') }}" class="mt-6 inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                Kembali ke Beranda
            </a>
        </div>
    </section>
@endsection
