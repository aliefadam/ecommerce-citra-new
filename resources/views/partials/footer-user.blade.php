@php
    $socialLinks = collect([
        ['key' => 'social_instagram', 'label' => 'Instagram', 'icon' => 'instagram'],
        ['key' => 'social_facebook', 'label' => 'Facebook', 'icon' => 'facebook'],
        ['key' => 'social_youtube', 'label' => 'YouTube', 'icon' => 'youtube'],
        ['key' => 'social_whatsapp', 'label' => 'WhatsApp', 'icon' => 'whatsapp'],
    ])->filter(fn ($social) => !empty($appStoreSettings[$social['key']]));
@endphp

<footer class="mt-10 border-t-4 border-orange-500 bg-slate-950 text-slate-300">
    <div class="ec-container py-12">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
            <div>
                <a href="{{ route('frontend.index') }}" class="inline-flex min-h-11 items-center gap-3 text-white">
                    @if(!empty($appStoreLogoUrl))<img src="{{ $appStoreLogoUrl }}" alt="" width="140" height="44" class="h-10 w-auto max-w-36 object-contain" />
                    @else<span class="ec-display grid size-10 place-items-center rounded-lg bg-white text-sm text-slate-950" aria-hidden="true">EC</span>@endif
                    <span class="ec-display text-xl">{{ $appStoreName }}</span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">Mitra pengadaan baut, mur, fastener, anchor, dan perkakas teknik untuk bengkel, proyek, dan industri.</p>
                <p class="mt-4 border-l-2 border-orange-500 pl-3 text-xs font-semibold uppercase tracking-wider text-slate-300">Professional partner for your professional results</p>
            </div>

            <div>
                <h2 class="ec-display text-base text-white">Belanja</h2>
                <ul class="mt-4 grid gap-1 text-sm">
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.kategori') }}">Semua produk</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.flash-sale') }}">Promo aktif</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.search', ['sort' => 'newest']) }}">Produk terbaru</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.order-tracking.index') }}">Lacak pesanan</a></li>
                </ul>
            </div>

            <div>
                <h2 class="ec-display text-base text-white">Informasi</h2>
                <ul class="mt-4 grid gap-1 text-sm">
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'pusat-bantuan') }}">Pusat bantuan</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'cara-belanja') }}">Cara belanja</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'kebijakan-privasi') }}">Kebijakan privasi</a></li>
                    <li><a class="flex min-h-11 items-center hover:text-white" href="{{ route('frontend.pages.show', 'syarat-ketentuan') }}">Syarat &amp; ketentuan</a></li>
                </ul>
            </div>

            <div>
                <h2 class="ec-display text-base text-white">Dukungan teknis</h2>
                <p class="mt-4 text-sm leading-6 text-slate-400">Senin–Sabtu, 08.00–17.00 WIB. Konsultasikan spesifikasi sebelum memesan bila Anda belum yakin.</p>
                @if(!empty($appStoreSettings['social_whatsapp']))
                    <a href="{{ $appStoreSettings['social_whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="ec-btn ec-btn-primary mt-5">Konsultasi WhatsApp</a>
                @else
                    <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}" class="ec-btn ec-btn-outline mt-5 border-slate-600 bg-transparent text-white">Buka pusat bantuan</a>
                @endif
                @if($socialLinks->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2" aria-label="Media sosial">
                        @foreach($socialLinks as $social)
                            <a href="{{ $appStoreSettings[$social['key']] }}" target="_blank" rel="noopener noreferrer" class="grid size-11 place-items-center rounded-lg border border-slate-700 text-[10px] font-extrabold hover:border-slate-500 hover:text-white" aria-label="{{ $social['label'] }}">{{ strtoupper(substr($social['label'], 0, 2)) }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-slate-800 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ $appStoreName }}. Hak cipta dilindungi.</p>
            <p>PT Citra Abadi Teknik Indonesia · Indonesia</p>
        </div>
    </div>
</footer>
