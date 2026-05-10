@php
    $socials = [
        'facebook'  => ['key' => 'social_facebook',  'label' => 'Facebook',  'color' => 'hover:bg-blue-600',  'svg' => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>'],
        'instagram' => ['key' => 'social_instagram', 'label' => 'Instagram', 'color' => 'hover:bg-pink-600',  'svg' => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>'],
        'twitter'   => ['key' => 'social_twitter',   'label' => 'X / Twitter','color' => 'hover:bg-slate-600','svg' => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>'],
        'tiktok'    => ['key' => 'social_tiktok',    'label' => 'TikTok',    'color' => 'hover:bg-black',     'svg' => '<path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.73a4.85 4.85 0 01-1.01-.04z"/>'],
        'youtube'   => ['key' => 'social_youtube',   'label' => 'YouTube',   'color' => 'hover:bg-red-600',   'svg' => '<path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>'],
        'whatsapp'  => ['key' => 'social_whatsapp',  'label' => 'WhatsApp',  'color' => 'hover:bg-green-600', 'svg' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>'],
    ];

    $activeSocials = collect($socials)->filter(function ($s) use ($appStoreSettings) {
        return !empty($appStoreSettings[$s['key']]);
    });
@endphp

<footer class="bg-[#0d1117] text-slate-400 mt-8 relative overflow-hidden">
    {{-- subtle top border gradient --}}
    <div class="h-px w-full bg-gradient-to-r from-transparent via-blue-500/40 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-10">
        <div class="grid grid-cols-2 md:grid-cols-12 gap-8 mb-12">

            {{-- Brand column --}}
            <div class="col-span-2 md:col-span-4">
                {{-- Logo + name --}}
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center overflow-hidden shadow-lg shadow-blue-500/30">
                        @if (!empty($appStoreLogoUrl))
                            <img src="{{ $appStoreLogoUrl }}" alt="{{ $appStoreName }}" class="w-full h-full object-contain bg-white p-1">
                        @else
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                            </svg>
                        @endif
                    </div>
                    <span class="text-white font-extrabold text-xl tracking-tight">{{ $appStoreName }}</span>
                </div>

                <p class="text-sm leading-relaxed text-slate-400 mb-6 max-w-xs">
                    Toko baut, mur, fastener, anchor, dan perkakas teknik untuk kebutuhan bengkel, proyek, dan industri.
                </p>

                {{-- Social icons --}}
                @if ($activeSocials->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeSocials as $id => $s)
                            <a href="{{ $appStoreSettings[$s['key']] }}" target="_blank" rel="noopener noreferrer"
                                title="{{ $s['label'] }}"
                                class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700/50 flex items-center justify-center {{ $s['color'] }} hover:border-transparent hover:shadow-md transition-all duration-200 group">
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                    {!! $s['svg'] !!}
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @else
                    {{-- fallback placeholders when no socials are set yet --}}
                    <div class="flex gap-2">
                        <div class="w-9 h-9 rounded-xl bg-slate-800/60 border border-slate-700/40"></div>
                        <div class="w-9 h-9 rounded-xl bg-slate-800/60 border border-slate-700/40"></div>
                        <div class="w-9 h-9 rounded-xl bg-slate-800/60 border border-slate-700/40"></div>
                    </div>
                @endif
            </div>

            {{-- Spacer on md --}}
            <div class="hidden md:block md:col-span-1"></div>

            {{-- Belanja --}}
            <div class="col-span-1 md:col-span-2">
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-5">Belanja</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('frontend.kategori') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Semua Produk</a></li>
                    <li><a href="{{ route('frontend.flash-sale') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Flash Sale</a></li>
                    <li><a href="{{ route('frontend.redeem-point') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Redeem Point</a></li>
                    <li><a href="{{ route('frontend.kategori') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Produk Baru</a></li>
                    <li><a href="{{ route('frontend.kategori') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Best Seller</a></li>
                    <li><a href="{{ route('frontend.cart') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Keranjang</a></li>
                </ul>
            </div>

            {{-- Akun Saya --}}
            <div class="col-span-1 md:col-span-2">
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-5">Akun Saya</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('frontend.profil') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Profil</a></li>
                    <li><a href="{{ route('frontend.profil') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Pesanan Saya</a></li>
                    <li><a href="{{ route('frontend.profil') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Wishlist</a></li>
                    <li><a href="{{ route('frontend.profil') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Ulasan</a></li>
                    <li><a href="{{ route('frontend.profil') }}" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Pengaturan</a></li>
                </ul>
            </div>

            {{-- Bantuan --}}
            <div class="col-span-1 md:col-span-3">
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-5">Bantuan</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Pusat Bantuan</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Syarat & Ketentuan</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Cara Belanja</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                        <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Hubungi Kami</a></li>
                </ul>
            </div>
        </div>

        {{-- Divider --}}
        <div class="h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent mb-8"></div>

        {{-- Bottom bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-5">
            <p class="text-xs text-slate-500">© {{ date('Y') }} <span class="text-slate-400 font-medium">{{ $appStoreName }}</span>. All rights reserved.</p>

            {{-- Payment badges --}}
            <div class="flex items-center flex-wrap gap-2 justify-center">
                @foreach (['Visa', 'Mastercard', 'GoPay', 'OVO', 'DANA', 'BCA', 'COD'] as $method)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700/60 text-[11px] font-semibold text-slate-400 tracking-wide">{{ $method }}</span>
                @endforeach
            </div>
        </div>
    </div>
</footer>
