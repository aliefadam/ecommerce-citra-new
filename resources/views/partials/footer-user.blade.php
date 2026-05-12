@php
    $socials = [
        'facebook' => [
            'key' => 'social_facebook',
            'label' => 'Facebook',
            'color' => 'hover:bg-blue-600',
            'svg' =>
                '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
        ],
        'instagram' => [
            'key' => 'social_instagram',
            'label' => 'Instagram',
            'color' => 'hover:bg-pink-600',
            'svg' =>
                '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
        ],
        'twitter' => [
            'key' => 'social_twitter',
            'label' => 'X / Twitter',
            'color' => 'hover:bg-slate-600',
            'svg' =>
                '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
        ],
        'tiktok' => [
            'key' => 'social_tiktok',
            'label' => 'TikTok',
            'color' => 'hover:bg-black',
            'svg' =>
                '<path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.73a4.85 4.85 0 01-1.01-.04z"/>',
        ],
        'youtube' => [
            'key' => 'social_youtube',
            'label' => 'YouTube',
            'color' => 'hover:bg-red-600',
            'svg' =>
                '<path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
        ],
        'whatsapp' => [
            'key' => 'social_whatsapp',
            'label' => 'WhatsApp',
            'color' => 'hover:bg-green-600',
            'svg' =>
                '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>',
        ],
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
                    <div
                        class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center overflow-hidden shadow-lg shadow-blue-500/30">
                        @if (!empty($appStoreLogoUrl))
                            <img src="{{ $appStoreLogoUrl }}" alt="{{ $appStoreName }}"
                                class="w-full h-full object-contain bg-white p-1">
                        @else
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
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
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors"
                                    fill="currentColor" viewBox="0 0 24 24">
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
                    <li><a href="{{ route('frontend.kategori') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Semua
                            Produk</a></li>
                    <li><a href="{{ route('frontend.flash-sale') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Flash
                            Sale</a></li>
                    <li><a href="{{ route('frontend.kategori') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Produk
                            Baru</a></li>
                    <li><a href="{{ route('frontend.kategori') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Best
                            Seller</a></li>
                    <li><a href="{{ route('frontend.cart') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Keranjang</a>
                    </li>
                </ul>
            </div>

            {{-- Akun Saya --}}
            <div class="col-span-1 md:col-span-2">
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-5">Akun Saya</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Profil</a>
                    </li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Pesanan
                            Saya</a></li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Wishlist</a>
                    </li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Ulasan</a>
                    </li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Pengaturan</a>
                    </li>
                </ul>
            </div>

            {{-- Bantuan --}}
            <div class="col-span-1 md:col-span-3">
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-5">Bantuan</h4>
                <ul class="space-y-3">
                    <li><button onclick="footerModalOpen('modal-pusat-bantuan')"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group cursor-pointer">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Pusat
                            Bantuan</button></li>
                    <li><button onclick="footerModalOpen('modal-kebijakan-privasi')"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group cursor-pointer">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Kebijakan
                            Privasi</button></li>
                    <li><button onclick="footerModalOpen('modal-syarat-ketentuan')"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group cursor-pointer">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Syarat
                            & Ketentuan</button></li>
                    <li><button onclick="footerModalOpen('modal-cara-belanja')"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group cursor-pointer">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Cara
                            Belanja</button></li>
                    <li><button onclick="footerModalOpen('modal-hubungi-kami')"
                            class="text-sm text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 group cursor-pointer">
                            <span
                                class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-blue-500 transition-colors flex-shrink-0"></span>Hubungi
                            Kami</button></li>
                </ul>
            </div>
        </div>

        {{-- Divider --}}
        <div class="h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent mb-8"></div>

        {{-- Bottom bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-5">
            <p class="text-xs text-slate-500">© {{ date('Y') }} <span
                    class="text-slate-400 font-medium">{{ $appStoreName }}</span>. All rights reserved.</p>

            {{-- Payment badges --}}
            <div class="flex items-center flex-wrap gap-2 justify-center">
                @foreach (['Visa', 'Mastercard', 'GoPay', 'OVO', 'DANA', 'BCA', 'COD'] as $method)
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700/60 text-[11px] font-semibold text-slate-400 tracking-wide">{{ $method }}</span>
                @endforeach
            </div>
        </div>
    </div>
</footer>

{{-- ============================================================
     FOOTER MODALS — Bantuan
     ============================================================ --}}

{{-- Backdrop --}}
<div id="footer-modal-backdrop" class="fixed inset-0 z-[900] bg-black/70 backdrop-blur-sm hidden"
    onclick="footerModalCloseAll()"></div>

{{-- ── 1. Pusat Bantuan ── --}}
<div id="modal-pusat-bantuan" class="footer-modal fixed inset-0 z-[901] items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-slate-800 font-semibold text-base">Pusat Bantuan</h3>
            </div>
            <button onclick="footerModalCloseAll()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-5 space-y-5 text-sm text-slate-600 leading-relaxed">
            <p>Selamat datang di Pusat Bantuan <span class="text-blue-600 font-semibold">{{ $appStoreName }}</span>.
                Kami
                siap membantu Anda menemukan produk yang tepat dan menyelesaikan setiap kendala pembelian.</p>

            <div class="space-y-3">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <p class="text-slate-800 font-semibold mb-1">📦 Pesanan & Pengiriman</p>
                    <p>Setelah pembayaran dikonfirmasi, pesanan akan diproses dalam 1×24 jam dan dikirim via ekspedisi
                        pilihan Anda. Nomor resi akan dikirimkan melalui WhatsApp.</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <p class="text-slate-800 font-semibold mb-1">🔄 Pengembalian Barang</p>
                    <p>Barang dapat dikembalikan dalam 3 hari setelah diterima jika terdapat kerusakan atau
                        ketidaksesuaian produk. Hubungi kami terlebih dahulu sebelum mengirim barang kembali.</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <p class="text-slate-800 font-semibold mb-1">💳 Pembayaran</p>
                    <p>Kami menerima pembayaran via transfer bank (BCA), dompet digital (GoPay, OVO, DANA), dan bayar di
                        tempat (COD) untuk area tertentu.</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <p class="text-slate-800 font-semibold mb-1">🔧 Konsultasi Produk</p>
                    <p>Tidak yakin produk mana yang sesuai kebutuhan proyek Anda? Hubungi tim kami via WhatsApp, kami
                        dengan senang hati membantu.</p>
                </div>
            </div>

            <p class="text-xs text-slate-400">Jam operasional: Senin – Sabtu, 08.00 – 17.00 WIB</p>
        </div>
    </div>
</div>

{{-- ── 2. Kebijakan Privasi ── --}}
<div id="modal-kebijakan-privasi" class="footer-modal fixed inset-0 z-[901] items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-slate-800 font-semibold text-base">Kebijakan Privasi</h3>
            </div>
            <button onclick="footerModalCloseAll()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-5 space-y-4 text-sm text-slate-600 leading-relaxed">
            <p>Kebijakan privasi ini menjelaskan bagaimana <span
                    class="text-blue-600 font-semibold">{{ $appStoreName }}</span> mengumpulkan, menggunakan, dan
                melindungi informasi pribadi Anda.</p>

            <div class="space-y-3">
                <div>
                    <p class="text-slate-800 font-semibold mb-1">1. Informasi yang Kami Kumpulkan</p>
                    <p>Kami mengumpulkan informasi yang Anda berikan saat mendaftar, memesan, atau menghubungi kami —
                        termasuk nama, nomor telepon, alamat pengiriman, dan data transaksi.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">2. Penggunaan Informasi</p>
                    <p>Data digunakan semata-mata untuk memproses pesanan, mengirim notifikasi status pengiriman, serta
                        meningkatkan layanan kami.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">3. Keamanan Data</p>
                    <p>Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang wajar untuk melindungi data
                        pribadi Anda dari akses yang tidak sah.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">4. Berbagi Data dengan Pihak Ketiga</p>
                    <p>Kami tidak menjual atau menyewakan data pribadi Anda. Data hanya dibagikan kepada mitra
                        pengiriman dan payment gateway untuk keperluan transaksi.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">5. Hak Pengguna</p>
                    <p>Anda berhak meminta akses, koreksi, atau penghapusan data pribadi Anda kapan saja dengan
                        menghubungi kami.</p>
                </div>
            </div>

            <p class="text-xs text-slate-400">Kebijakan ini berlaku efektif per {{ date('d F Y') }} dan dapat
                diperbarui sewaktu-waktu.</p>
        </div>
    </div>
</div>

{{-- ── 3. Syarat & Ketentuan ── --}}
<div id="modal-syarat-ketentuan" class="footer-modal fixed inset-0 z-[901] items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-slate-800 font-semibold text-base">Syarat & Ketentuan</h3>
            </div>
            <button onclick="footerModalCloseAll()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-5 space-y-4 text-sm text-slate-600 leading-relaxed">
            <p>Dengan menggunakan layanan <span class="text-blue-600 font-semibold">{{ $appStoreName }}</span>, Anda
                menyetujui syarat dan ketentuan berikut.</p>

            <div class="space-y-3">
                <div>
                    <p class="text-slate-800 font-semibold mb-1">1. Akun Pengguna</p>
                    <p>Anda bertanggung jawab menjaga kerahasiaan akun dan kata sandi. Segala aktivitas yang terjadi
                        pada akun Anda menjadi tanggung jawab Anda.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">2. Pemesanan & Pembayaran</p>
                    <p>Pesanan dianggap sah setelah pembayaran dikonfirmasi. Harga yang tertera sudah termasuk PPN dan
                        dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">3. Pengiriman</p>
                    <p>Estimasi waktu pengiriman bersifat perkiraan dan dapat berbeda tergantung kondisi ekspedisi dan
                        lokasi tujuan. Kami tidak bertanggung jawab atas keterlambatan akibat force majeure.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">4. Retur & Refund</p>
                    <p>Pengembalian barang hanya diterima untuk produk yang cacat atau tidak sesuai pesanan, diajukan
                        dalam 3 hari setelah barang diterima.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">5. Hak Kekayaan Intelektual</p>
                    <p>Seluruh konten di website ini — termasuk logo, foto produk, dan teks — adalah milik
                        {{ $appStoreName }} dan dilindungi hak cipta.</p>
                </div>
                <div>
                    <p class="text-slate-800 font-semibold mb-1">6. Perubahan Ketentuan</p>
                    <p>Kami berhak mengubah syarat dan ketentuan ini kapan saja. Perubahan berlaku sejak dipublikasikan
                        di website.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 4. Cara Belanja ── --}}
<div id="modal-cara-belanja" class="footer-modal fixed inset-0 z-[901] items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-slate-800 font-semibold text-base">Cara Belanja</h3>
            </div>
            <button onclick="footerModalCloseAll()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-5 space-y-4 text-sm text-slate-600 leading-relaxed">
            <p>Belanja di <span class="text-blue-600 font-semibold">{{ $appStoreName }}</span> mudah dan aman. Ikuti
                langkah berikut:</p>

            <div class="space-y-3">
                @foreach ([['step' => '1', 'title' => 'Cari Produk', 'desc' => 'Gunakan kolom pencarian atau jelajahi kategori untuk menemukan baut, mur, fastener, anchor, atau perkakas yang Anda butuhkan.'], ['step' => '2', 'title' => 'Pilih Varian & Tambah ke Keranjang', 'desc' => 'Pilih ukuran, material, atau spesifikasi yang sesuai, lalu klik tombol "Tambah ke Keranjang".'], ['step' => '3', 'title' => 'Checkout', 'desc' => 'Masuk ke keranjang, periksa pesanan Anda, isi alamat pengiriman, dan pilih metode pengiriman.'], ['step' => '4', 'title' => 'Pembayaran', 'desc' => 'Pilih metode pembayaran (transfer bank, dompet digital, atau COD) dan selesaikan pembayaran.'], ['step' => '5', 'title' => 'Konfirmasi & Pengiriman', 'desc' => 'Setelah pembayaran kami verifikasi, pesanan akan dikemas dan dikirim. Nomor resi akan dikirimkan ke WhatsApp Anda.'], ['step' => '6', 'title' => 'Terima Pesanan', 'desc' => 'Periksa produk saat diterima. Jika ada masalah, hubungi kami dalam 3 hari setelah penerimaan.']] as $item)
                    <div class="flex gap-3">
                        <div
                            class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-bold text-xs">
                            {{ $item['step'] }}</div>
                        <div>
                            <p class="text-slate-800 font-semibold mb-0.5">{{ $item['title'] }}</p>
                            <p>{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex gap-2 items-start">
                <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-blue-700 text-xs">Butuh bantuan memilih produk? Tim kami siap konsultasi gratis via
                    WhatsApp.</p>
            </div>
        </div>
    </div>
</div>

{{-- ── 5. Hubungi Kami ── --}}
<div id="modal-hubungi-kami" class="footer-modal fixed inset-0 z-[901] items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <h3 class="text-slate-800 font-semibold text-base">Hubungi Kami</h3>
            </div>
            <button onclick="footerModalCloseAll()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-5 space-y-4 text-sm text-slate-600 leading-relaxed">
            <p>Kami siap membantu Anda! Pilih saluran komunikasi yang paling nyaman untuk Anda.</p>

            <div class="space-y-3">
                @if (!empty($appStoreSettings['social_whatsapp']))
                    <a href="{{ $appStoreSettings['social_whatsapp'] }}" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-4 bg-green-50 border border-green-200 hover:border-green-400 hover:bg-green-100 rounded-xl p-4 transition-all group">
                        <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-800 font-semibold group-hover:text-green-700 transition-colors">
                                WhatsApp</p>
                            <p class="text-xs text-slate-500">Chat langsung, respons cepat</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-green-600 ml-auto transition-colors"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif

                @if (!empty($appStoreSettings['social_instagram']))
                    <a href="{{ $appStoreSettings['social_instagram'] }}" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-4 bg-pink-50 border border-pink-200 hover:border-pink-400 hover:bg-pink-100 rounded-xl p-4 transition-all group">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-purple-600 via-pink-600 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-800 font-semibold group-hover:text-pink-700 transition-colors">
                                Instagram</p>
                            <p class="text-xs text-slate-500">Ikuti update produk terbaru</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-pink-600 ml-auto transition-colors"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif

                <div class="flex items-center gap-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-800 font-semibold">Jam Operasional</p>
                        <p class="text-xs text-slate-500">Senin – Sabtu, 08.00 – 17.00 WIB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function footerModalOpen(id) {
        document.getElementById('footer-modal-backdrop').classList.remove('hidden');
        var el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function footerModalCloseAll() {
        document.getElementById('footer-modal-backdrop').classList.add('hidden');
        document.querySelectorAll('.footer-modal').forEach(function(el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        });
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') footerModalCloseAll();
    });
</script>
