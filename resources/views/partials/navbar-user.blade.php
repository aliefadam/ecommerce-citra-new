@php
    $authUser = auth()->user();
    $displayName = $authUser?->name ?: 'Tamu';
    $displayFirstName = trim(explode(' ', $displayName)[0] ?? $displayName);
    $initial = strtoupper(substr($displayFirstName, 0, 1));
    $cartCount = (int) ($customerNavigation['cartCount'] ?? 0);
    $megaCategories = $customerNavigation['megaCategories'] ?? [];
@endphp

<div class="ec-announcement px-4 py-2 text-center text-xs" role="region" aria-label="Pengumuman toko">
    <span class="font-semibold">Pasokan teknik untuk bengkel, proyek, dan industri.</span>
    <a href="{{ route('frontend.flash-sale') }}" class="ml-2 inline-flex min-h-8 items-center font-bold text-orange-300 underline decoration-orange-400/60 underline-offset-4">Lihat promo <span aria-hidden="true">→</span></a>
</div>

<nav class="ec-site-header" aria-label="Navigasi utama" data-storefront-shell>
    <div class="ec-container flex h-[72px] items-center gap-3 sm:gap-5">
        <a href="{{ route('frontend.index') }}" class="flex min-h-11 shrink-0 items-center gap-3" aria-label="{{ $appStoreName }}, beranda">
            @if (!empty($appStoreLogoUrl))
                <img src="{{ $appStoreLogoUrl }}" alt="" width="144" height="44" class="h-10 w-auto max-w-[120px] object-contain" />
            @else
                <span class="ec-display grid size-10 place-items-center rounded-lg bg-slate-900 text-sm text-white" aria-hidden="true">EC</span>
                <span class="hidden sm:block"><span class="ec-display block text-lg leading-none text-slate-950">{{ $appStoreName }}</span><span class="mt-1 block text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Industrial Supply</span></span>
            @endif
        </a>

        <div class="relative hidden min-w-0 flex-1 md:block">
            <form action="{{ route('frontend.search') }}" method="GET" class="ec-search-shell" role="search">
                <span class="grid w-11 shrink-0 place-items-center text-slate-500" aria-hidden="true"><i class="fi fi-rr-search"></i></span>
                <label for="ecNavSearchDesktop" class="sr-only">Cari produk</label>
                <input id="ecNavSearchDesktop" name="q" value="{{ trim(request('q', $query ?? '')) }}" placeholder="Cari nama, SKU, ukuran, atau material"
                    class="min-w-0 flex-1 bg-transparent px-1 text-sm outline-none" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ecNavSearchDropdownDesktop" data-storefront-autocomplete data-suggestions-url="{{ route('frontend.search.suggestions') }}" />
                <button class="m-1 min-w-20 rounded-md bg-slate-900 px-4 text-sm font-bold text-white hover:bg-slate-800" type="submit">Cari</button>
            </form>
            <div id="ecNavSearchDropdownDesktop" class="ec-dropdown top-full left-0 right-0 mt-2 max-h-[28rem] overflow-y-auto" role="listbox" aria-label="Saran produk" hidden></div>
        </div>

        <div class="ml-auto flex shrink-0 items-center gap-1">
            <button id="ecMobileSearchToggle" class="grid size-11 place-items-center rounded-lg text-slate-700 hover:bg-slate-100 md:hidden" type="button" aria-expanded="false" aria-controls="ecMobileSearch" aria-label="Buka pencarian"><i class="fi fi-rr-search" aria-hidden="true"></i></button>
            <a href="{{ route('frontend.profil') }}?tab=wishlist" class="hidden size-11 place-items-center rounded-lg text-slate-700 hover:bg-slate-100 sm:grid" aria-label="Wishlist"><i class="fi fi-rr-heart" aria-hidden="true"></i></a>
            @auth
                <div class="relative">
                    <button id="ecNotifTrigger" class="relative grid size-11 place-items-center rounded-lg text-slate-700 hover:bg-slate-100" type="button" aria-expanded="false" aria-controls="ecNotifDropdown" aria-label="Notifikasi"><i class="fi fi-rr-bell" aria-hidden="true"></i><span id="ecNotifBadge" class="absolute right-0 top-0 grid min-h-4 min-w-4 place-items-center rounded-full bg-red-700 px-1 text-[9px] font-bold text-white" hidden></span></button>
                    <div id="ecNotifDropdown" class="ec-dropdown right-0 top-full mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden" hidden>
                        <div class="flex items-center justify-between border-b border-slate-200 p-4"><strong class="ec-display">Notifikasi</strong><button id="ecNotifReadAll" class="min-h-11 text-xs font-bold text-blue-700" type="button">Tandai dibaca</button></div>
                        <div id="ecNotifList" class="max-h-80 overflow-y-auto" aria-live="polite"><p class="p-6 text-center text-sm text-slate-500">Memuat notifikasi…</p></div>
                        <a href="{{ route('frontend.profil') }}?tab=notif" class="block min-h-11 border-t border-slate-200 p-3 text-center text-sm font-bold text-blue-700">Lihat semua</a>
                    </div>
                </div>
            @endauth
            <a href="{{ route('frontend.cart') }}" class="relative grid size-11 place-items-center rounded-lg text-slate-700 hover:bg-slate-100" aria-label="Keranjang, {{ $cartCount }} barang"><i class="fi fi-rr-shopping-cart" aria-hidden="true"></i><span id="cartCount" class="absolute right-0 top-0 grid min-h-4 min-w-4 place-items-center rounded-full bg-orange-600 px-1 text-[9px] font-bold text-white" @if($cartCount <= 0) hidden @endif>{{ $cartCount > 99 ? '99+' : $cartCount }}</span></a>
            @auth
                <div class="relative">
                    <button id="ecAccountTrigger" type="button" class="flex min-h-11 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 hover:border-slate-400" aria-expanded="false" aria-controls="ecAccountDropdown">
                        @if($authUser->avatar)<img src="{{ $authUser->avatar }}" alt="" class="size-8 rounded-md object-cover" />@else<span class="grid size-8 place-items-center rounded-md bg-blue-100 text-sm font-extrabold text-blue-800">{{ $initial }}</span>@endif
                        <span class="hidden text-left lg:block"><span class="block text-[10px] text-slate-500">Akun</span><span class="block max-w-24 truncate text-xs font-bold">{{ $displayFirstName }}</span></span>
                    </button>
                    <div id="ecAccountDropdown" class="ec-dropdown right-0 top-full mt-2 w-60 overflow-hidden p-2" hidden>
                        <p class="border-b border-slate-200 px-3 py-3 text-xs text-slate-500"><strong class="block truncate text-sm text-slate-900">{{ $displayName }}</strong>{{ $authUser->email }}</p>
                        <a href="{{ route('frontend.profil') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-user" aria-hidden="true"></i>Profil saya</a>
                        <a href="{{ route('frontend.profil') }}?tab=pesanan" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-box-open-full" aria-hidden="true"></i>Pesanan</a>
                        <form action="{{ route('logout') }}" method="POST" class="border-t border-slate-200 pt-1">@csrf<button class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-bold text-red-700 hover:bg-red-50" type="submit"><i class="fi fi-rr-exit" aria-hidden="true"></i>Keluar</button></form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="ec-btn ec-btn-outline hidden sm:inline-flex">Masuk</a>
            @endauth
        </div>
    </div>

    <div class="border-t border-slate-200 bg-white/80">
        <div class="ec-container flex min-h-12 items-center gap-2">
            <div class="relative">
                <button id="ecCategoryTrigger" type="button" class="ec-btn ec-btn-secondary rounded-none" aria-expanded="false" aria-controls="ecCategoryDropdown"><i class="fi fi-rr-apps" aria-hidden="true"></i>Kategori <span aria-hidden="true">⌄</span></button>
                <div id="ecCategoryDropdown" class="ec-dropdown left-0 top-full mt-2 grid w-[min(76rem,calc(100vw-2rem))] grid-cols-[14rem_1fr] overflow-hidden" hidden>
                    <div id="ecMegaCategoryMenu" class="grid content-start gap-1 border-r border-slate-200 bg-slate-50 p-3" role="tablist" aria-label="Kategori produk"></div>
                    <div id="ecMegaCategoryContent" class="min-h-72 p-5"></div>
                </div>
            </div>
            <div class="hidden items-center md:flex">
                @foreach ([['Beranda', route('frontend.index'), 'frontend.index'], ['Semua Produk', route('frontend.kategori'), 'frontend.kategori'], ['Promo', route('frontend.flash-sale'), 'frontend.flash-sale'], ['Produk Terbaru', route('frontend.search', ['sort' => 'newest']), null], ['Terlaris', route('frontend.search', ['sort' => 'popular']), null]] as [$label, $url, $routeName])
                    <a href="{{ $url }}" class="flex min-h-12 items-center border-b-2 px-3 text-sm font-bold {{ $routeName && request()->routeIs($routeName) ? 'border-blue-700 text-blue-800' : 'border-transparent text-slate-600 hover:text-slate-950' }}" @if($routeName && request()->routeIs($routeName)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </div>
            <button id="ecMobileNavToggle" type="button" class="ml-auto flex min-h-11 items-center gap-2 px-3 text-sm font-bold md:hidden" aria-expanded="false" aria-controls="ecMobileNavDrawer"><i class="fi fi-rr-menu-burger" aria-hidden="true"></i>Menu</button>
        </div>
        <div id="ecMobileNavDrawer" class="border-t border-slate-200 bg-white p-3 md:hidden" hidden>
            <div class="grid grid-cols-2 gap-1"><a href="{{ route('frontend.index') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">Beranda</a><a href="{{ route('frontend.kategori') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">Semua Produk</a><a href="{{ route('frontend.flash-sale') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">Promo</a><a href="{{ route('frontend.redeem-point') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">Redeem Point</a></div>
        </div>
    </div>

    <div id="ecMobileSearch" class="border-t border-slate-200 bg-white p-3 md:hidden" hidden>
        <div class="relative">
            <form action="{{ route('frontend.search') }}" method="GET" class="ec-search-shell" role="search"><label for="ecNavSearchMobile" class="sr-only">Cari produk</label><span class="grid w-11 place-items-center text-slate-500"><i class="fi fi-rr-search" aria-hidden="true"></i></span><input id="ecNavSearchMobile" name="q" value="{{ trim(request('q', $query ?? '')) }}" placeholder="Cari nama, SKU, atau ukuran" class="min-w-0 flex-1 bg-transparent outline-none" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ecNavSearchDropdownMobile" data-storefront-autocomplete data-suggestions-url="{{ route('frontend.search.suggestions') }}" /><button class="m-1 rounded-md bg-slate-900 px-4 text-sm font-bold text-white" type="submit">Cari</button></form>
            <div id="ecNavSearchDropdownMobile" class="ec-dropdown top-full left-0 right-0 mt-2 max-h-[60vh] overflow-y-auto" role="listbox" aria-label="Saran produk" hidden></div>
        </div>
    </div>
</nav>

<script id="ec-shell-config" type="application/json">{!! json_encode([
    'categories' => $megaCategories,
    'cartCountUrl' => auth()->check() ? route('frontend.cart.count') : null,
    'notifUrl' => auth()->check() ? route('frontend.notifications.index') : null,
    'notifReadAllUrl' => auth()->check() ? route('frontend.notifications.read-all') : null,
    'csrfToken' => csrf_token(),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
