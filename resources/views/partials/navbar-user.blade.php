@php
    $authUser = auth()->user();
    $displayName = $authUser?->name ?: 'Tamu';
    $displayFirstName = trim(explode(' ', $displayName)[0] ?? $displayName);
    $cartCount = (int) ($customerNavigation['cartCount'] ?? 0);
    $megaCategories = $customerNavigation['megaCategories'] ?? [];
    $selectedSearchCategory = collect($megaCategories)->firstWhere('key', (string) request('parent'));
@endphp

<nav class="ec-site-header" aria-label="Navigasi utama" data-storefront-shell>
    <div class="ec-utility-bar">
        <div class="ec-container ec-utility-inner">
            <p class="ec-utility-copy">Solusi Kebutuhan Fastener &amp; Industrial Supply</p>
            <a href="{{ route('frontend.pages.show', 'pusat-bantuan') }}?category=aplikasi#install-aplikasi"
                class="ec-mobile-install" data-pwa-install aria-label="Install aplikasi {{ $appStoreName }} atau lihat panduan instalasi">
                <span class="ec-mobile-install-copy">
                    <small>APLIKASI {{ strtoupper($appStoreName) }}</small>
                    <strong>Belanja lebih cepat dari HP</strong>
                </span>
                <span class="ec-mobile-install-action">
                    INSTALL <i class="fi fi-rr-arrow-small-right" aria-hidden="true"></i>
                </span>
            </a>
            <div class="ec-utility-links" aria-label="Keunggulan layanan">
                <span><i class="fi fi-rr-shield-check" aria-hidden="true"></i> Trusted by Industry</span>
                <span><i class="fi fi-rr-marker" aria-hidden="true"></i> Pengiriman ke Seluruh Indonesia</span>
            </div>
        </div>
    </div>

    <div class="ec-header-main">
        <div class="ec-container ec-header-main-inner">
            <a href="{{ route('frontend.index') }}" class="ec-header-logo" aria-label="{{ $appStoreName }}, beranda">
                <img src="{{ !empty($appStoreLogoUrl) ? $appStoreLogoUrl : asset('logo/BOQ.CO.ID/BOQ.CO.ID-1.png') }}" alt="{{ $appStoreName }}" width="116" height="52" />
            </a>

            <div class="ec-header-search-wrap">
                <form action="{{ route('frontend.search') }}" method="GET" class="ec-search-shell ec-reference-search" role="search">
                    <label for="ecNavSearchDesktop" class="sr-only">Cari produk</label>
                    <input id="ecNavSearchDesktop" name="q" value="{{ trim(request('q', $query ?? '')) }}" placeholder="Cari produk, kategori, atau merek..."
                        class="ec-reference-search-input" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ecNavSearchDropdownDesktop" data-storefront-autocomplete data-suggestions-url="{{ route('frontend.search.suggestions') }}" />
                    <div class="ec-reference-search-category" data-search-category>
                        <input id="ecNavCategory" type="hidden" name="parent" value="{{ $selectedSearchCategory['key'] ?? '' }}" />
                        <button id="ecNavCategoryTrigger" type="button" class="ec-reference-search-category-trigger"
                            aria-haspopup="listbox" aria-expanded="false" aria-controls="ecNavCategoryDropdown">
                            <span data-search-category-label>{{ $selectedSearchCategory['name'] ?? 'Semua Kategori' }}</span>
                            <i class="fi fi-rr-angle-small-down" aria-hidden="true"></i>
                        </button>
                        <div id="ecNavCategoryDropdown" class="ec-reference-search-category-dropdown" role="listbox"
                            aria-label="Kategori pencarian" hidden>
                            <button type="button" class="ec-reference-search-category-option" role="option"
                                aria-selected="{{ $selectedSearchCategory ? 'false' : 'true' }}" data-category-value="" data-category-label="Semua Kategori">
                                <span>Semua Kategori</span><i class="fi fi-rr-check" aria-hidden="true"></i>
                            </button>
                            @foreach ($megaCategories as $category)
                                <button type="button" class="ec-reference-search-category-option" role="option"
                                    aria-selected="{{ ($selectedSearchCategory['key'] ?? null) === $category['key'] ? 'true' : 'false' }}"
                                    data-category-value="{{ $category['key'] }}" data-category-label="{{ $category['name'] }}">
                                    <span>{{ $category['name'] }}</span><i class="fi fi-rr-check" aria-hidden="true"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <button class="ec-reference-search-button" type="submit" aria-label="Cari"><i class="fi fi-rr-search" aria-hidden="true"></i></button>
                </form>
                <div id="ecNavSearchDropdownDesktop" class="ec-dropdown left-0 right-0 top-full mt-2 max-h-[28rem] overflow-y-auto" role="listbox" aria-label="Saran produk" hidden></div>
            </div>

            <div class="ec-header-actions">
                <button id="ecMobileSearchToggle" class="ec-header-icon-button md:hidden" type="button" aria-expanded="false" aria-controls="ecMobileSearch" aria-label="Buka pencarian"><i class="fi fi-rr-search" aria-hidden="true"></i></button>
                @auth
                    <div class="relative">
                        <button id="ecAccountTrigger" type="button" class="ec-header-action" aria-expanded="false" aria-controls="ecAccountDropdown">
                            @if($authUser->avatar)<img src="{{ $authUser->avatar }}" alt="" class="size-8 rounded-full object-cover" />@else<i class="fi fi-rr-user" aria-hidden="true"></i>@endif
                            <span><strong>{{ $displayFirstName }}</strong><small>Akun Saya</small></span>
                        </button>
                        <div id="ecAccountDropdown" class="ec-dropdown right-0 top-full mt-2 w-64 overflow-hidden p-2" hidden>
                            <p class="border-b border-slate-200 px-3 py-3 text-xs text-slate-500"><strong class="block truncate text-sm text-slate-900">{{ $displayName }}</strong>{{ $authUser->email }}</p>
                            <a href="{{ route('frontend.profil') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-user" aria-hidden="true"></i>Profil saya</a>
                            <a href="{{ route('frontend.profil') }}?tab=notif" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-bell" aria-hidden="true"></i>Notifikasi</a>
                            <a href="{{ route('frontend.profil') }}?tab=pesanan" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold hover:bg-slate-100"><i class="fi fi-rr-box-open-full" aria-hidden="true"></i>Pesanan</a>
                            <form action="{{ route('logout') }}" method="POST" class="border-t border-slate-200 pt-1">@csrf<button class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-bold text-red-700 hover:bg-red-50" type="submit"><i class="fi fi-rr-exit" aria-hidden="true"></i>Keluar</button></form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="ec-header-action"><i class="fi fi-rr-user" aria-hidden="true"></i><span><strong>Masuk / Daftar</strong><small>Akun Saya</small></span></a>
                @endauth
                <a href="{{ route('frontend.cart') }}" class="ec-header-action ec-cart-action" aria-label="Keranjang, {{ $cartCount }} barang">
                    <span class="relative"><i class="fi fi-rr-shopping-cart" aria-hidden="true"></i><span id="cartCount" class="ec-header-count" @if($cartCount <= 0) hidden @endif>{{ $cartCount > 99 ? '99+' : $cartCount }}</span></span>
                    <span><strong>Keranjang</strong><small>{{ $cartCount }} barang</small></span>
                </a>
            </div>
        </div>
    </div>

    <div class="ec-primary-nav">
        <div class="ec-container ec-primary-nav-inner">
            <div class="relative ec-category-wrap">
                <button id="ecCategoryTrigger" type="button" class="ec-category-button" aria-expanded="false" aria-controls="ecCategoryDropdown"><i class="fi fi-rr-menu-burger" aria-hidden="true"></i><span>Semua Kategori</span><i class="fi fi-rr-angle-small-down" aria-hidden="true"></i></button>
                <div id="ecCategoryDropdown" class="ec-category-mega" hidden>
                    <aside class="ec-mega-family-panel" aria-label="Product category">
                        <p class="ec-mega-eyebrow">Product Category</p>
                        <div id="ecMegaCategoryMenu" class="ec-mega-family-list" role="tablist" aria-label="Kategori produk"></div>
                    </aside>
                    <div id="ecMegaCategoryContent" class="ec-mega-catalog" role="tabpanel" aria-live="polite"></div>
                </div>
            </div>
            <div class="ec-primary-links">
                @foreach ([
                    ['Beranda', route('frontend.index'), 'frontend.index'],
                    ['Produk', route('frontend.kategori'), 'frontend.kategori'],
                    ['Promo', route('frontend.flash-sale'), 'frontend.flash-sale'],
                    ['Hubungi Kami', route('frontend.pages.show', 'pusat-bantuan'), null],
                ] as [$label, $url, $routeName])
                    <a href="{{ $url }}" class="{{ $routeName && request()->routeIs($routeName) ? 'is-active' : '' }}" @if($routeName && request()->routeIs($routeName)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </div>
            <button id="ecMobileNavToggle" type="button" class="ec-mobile-menu-button md:hidden" aria-expanded="false" aria-controls="ecMobileNavDrawer"><i class="fi fi-rr-menu-burger" aria-hidden="true"></i>Menu</button>
        </div>
        <div id="ecMobileNavDrawer" class="border-t border-slate-200 bg-white p-3 md:hidden" hidden>
            <div class="grid grid-cols-2 gap-1"><a href="{{ route('frontend.index') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">Beranda</a><a href="{{ route('frontend.kategori') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">Produk</a><a href="{{ route('frontend.flash-sale') }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold hover:bg-slate-100">Promo</a></div>
        </div>
    </div>

    <div id="ecMobileSearch" class="border-t border-slate-200 bg-white p-3 md:hidden" hidden>
        <div class="relative">
            <form action="{{ route('frontend.search') }}" method="GET" class="ec-search-shell" role="search"><label for="ecNavSearchMobile" class="sr-only">Cari produk</label><input id="ecNavSearchMobile" name="q" value="{{ trim(request('q', $query ?? '')) }}" placeholder="Cari produk, kategori, atau merek..." class="min-w-0 flex-1 bg-transparent px-4 outline-none" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ecNavSearchDropdownMobile" data-storefront-autocomplete data-suggestions-url="{{ route('frontend.search.suggestions') }}" /><button class="ec-reference-search-button m-1 rounded-md" type="submit" aria-label="Cari"><i class="fi fi-rr-search" aria-hidden="true"></i></button></form>
            <div id="ecNavSearchDropdownMobile" class="ec-dropdown left-0 right-0 top-full mt-2 max-h-[60vh] overflow-y-auto" role="listbox" aria-label="Saran produk" hidden></div>
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
