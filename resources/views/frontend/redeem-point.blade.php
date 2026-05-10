@extends('layouts.user')

@section('title', 'Redeem Point - ' . ($appStoreName ?? 'Ecommerce Citra'))

@section('content')
    @include('partials.navbar-user')

    <section class="bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_34%),linear-gradient(180deg,_#eff6ff_0%,_#f8fafc_100%)] border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-4">
                <a href="{{ route('frontend.index') }}" class="hover:text-blue-600">Beranda</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Redeem Point</span>
            </nav>

            <div class="grid lg:grid-cols-[1.5fr,0.9fr] gap-5 items-start">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white/80 px-3 py-1 text-xs font-semibold text-amber-700">
                        Katalog Reward Member
                    </span>
                    <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Tukarkan point kamu dengan produk pilihan</h1>
                    <p class="mt-3 max-w-2xl text-sm sm:text-base text-slate-600">
                        Jelajahi produk redeem yang sudah ditandai admin. Untuk sementara halaman ini fokus menampilkan katalog redeem dan kebutuhan point per produk.
                    </p>
                </div>

                <div class="rounded-3xl border border-blue-100 bg-white/90 backdrop-blur p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Saldo Point</p>
                    @auth
                        <div class="mt-2 text-3xl font-extrabold text-blue-600">{{ number_format((int) $userPointBalance, 0, ',', '.') }}</div>
                        <p class="mt-2 text-sm text-slate-500">Saldo point saat ini di akun kamu. Produk redeem sekarang bisa dilanjutkan ke checkout khusus point.</p>
                    @else
                        <div class="mt-2 text-xl font-bold text-slate-800">Login untuk melihat point kamu</div>
                        <p class="mt-2 text-sm text-slate-500">Katalog redeem tetap bisa dilihat tanpa login, tapi saldo point hanya muncul setelah masuk.</p>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Daftar Produk Redeem</h2>
                <p class="text-sm text-slate-500 mt-1">{{ count($redeemProducts ?? []) }} produk tersedia untuk ditukar dengan point</p>
            </div>
        </div>

        @if (count($redeemProducts ?? []) > 0)
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($redeemProducts as $product)
                    <article class="group rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm hover:shadow-lg transition-all">
                        <a href="{{ $product['detailUrl'] }}" class="relative block aspect-[4/3] overflow-hidden bg-slate-100">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute left-3 top-3 rounded-full bg-amber-400 px-3 py-1 text-[11px] font-bold text-slate-900">
                                Redeem
                            </span>
                        </a>

                        <div class="p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $product['category'] }}</p>
                            <a href="{{ $product['detailUrl'] }}" class="mt-2 block text-sm sm:text-base font-bold leading-snug text-slate-800 hover:text-blue-600 line-clamp-2">
                                {{ $product['name'] }}
                            </a>

                            <div class="mt-3 flex items-center gap-2 flex-wrap text-xs text-slate-500">
                                <span>{{ number_format((float) $product['rating'], 1) }} / 5</span>
                                <span class="text-slate-300">|</span>
                                <span>{{ number_format((int) $product['reviews']) }} ulasan</span>
                                <span class="text-slate-300">|</span>
                                <span>Stok {{ number_format((int) $product['stock']) }}</span>
                            </div>

                            <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-100 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Harga Redeem</p>
                                <p class="mt-1 text-lg sm:text-xl font-extrabold text-amber-800">{{ number_format((int) $product['redeemPoints'], 0, ',', '.') }} point</p>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-3">
                                <span class="text-xs text-slate-500">{{ number_format((int) $product['sold']) }} kali ditebus/terjual</span>
                                <div class="flex items-center gap-2">
                                    @auth
                                        <form method="POST" action="{{ route('frontend.redeem.prepare-checkout') }}">
                                            @csrf
                                            <input type="hidden" name="product_variant_id" value="{{ $product['productVariantId'] }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-full bg-amber-500 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-600 transition-colors">
                                                Redeem 1x
                                            </button>
                                        </form>
                                    @endauth
                                    <a href="{{ $product['detailUrl'] }}"
                                        class="inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <h3 class="text-lg font-bold text-slate-800">Belum ada produk redeem</h3>
                <p class="mt-2 text-sm text-slate-500">Admin sudah bisa menandai produk sebagai redeem, tapi belum ada produk aktif yang ditampilkan di halaman ini.</p>
            </div>
        @endif
    </section>
@endsection
