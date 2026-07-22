{{-- Announcement bar --}}
<div
    class="relative overflow-hidden bg-gradient-to-r from-violet-700 via-indigo-600 to-sky-600 text-white text-center text-xs py-2 px-4">
    <div class="relative z-10 flex items-center justify-center gap-2">
        <span class="text-yellow-300 text-sm">✨</span>
        <span class="font-medium tracking-wide">Belanja lebih hemat dengan promo spesial hari ini</span>
        <span class="hidden sm:inline text-indigo-200">→</span>
        <a href="{{ route('frontend.flash-sale') }}"
            class="hidden sm:inline-flex items-center gap-1 font-bold text-yellow-300 hover:text-yellow-100 transition-colors ml-1 border-b border-yellow-400/50 pb-px">
            Lihat Promo
            <i class="fi fi-rr-angle-small-right text-xs leading-none"></i>
        </a>
    </div>
    {{-- Shine effect --}}
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -skew-x-12 animate-[shimmer_3s_infinite]"
        style="animation: shimmer 4s ease-in-out infinite; transform: translateX(-100%) skewX(-12deg);"></div>
</div>

<style>
    @keyframes shimmer {
        0% {
            transform: translateX(-100%) skewX(-12deg);
        }

        60%,
        100% {
            transform: translateX(200%) skewX(-12deg);
        }
    }

    .nav-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
        white-space: nowrap;
        flex-shrink: 0;
        transition: color 0.2s;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        right: 50%;
        height: 2px;
        border-radius: 9999px;
        background: #2563eb;
        transition: left 0.25s ease, right 0.25s ease;
    }

    .nav-link:hover {
        color: #2563eb;
    }

    .nav-link:hover::after {
        left: 0.75rem;
        right: 0.75rem;
    }

    .nav-link.active {
        color: #2563eb;
        font-weight: 600;
    }

    .nav-link.active::after {
        left: 0.75rem;
        right: 0.75rem;
        background: #2563eb;
    }

    .nav-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        line-height: 1;
        color: #5b6475;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .group:hover .nav-action-icon {
        color: #334155;
        transform: translateY(-1px);
    }

    .nav-action-badge {
        position: absolute;
        top: -0.50rem;
        right: -0.48rem;
        min-width: 1rem;
        height: 1rem;
        padding: 0 0.10rem;
        border-radius: 9999px;
        background: #ef4444;
        color: #fff;
        font-size: 0.60rem;
        font-weight: 700;
        line-height: 1;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 2px #fff;
    }

    .nav-action-badge.flex {
        display: flex;
    }
</style>

<nav class="bg-white/95 backdrop-blur-sm sticky top-0 z-50 border-b border-slate-200/80"
    style="box-shadow: 0 1px 20px 0 rgba(0,0,0,0.06);">
    @php
        $authUser = auth()->user();
        $displayName = $authUser?->name ?: 'Guest';
        $displayFirstName = trim(explode(' ', $displayName)[0] ?? $displayName);
        $initial = strtoupper(substr($displayFirstName, 0, 1));
        $avatarUrl = trim((string) ($authUser?->avatar ?? ''));
        $hasAvatarImage = $avatarUrl !== '';
        $cartCount = $authUser ? (int) $authUser->carts()->sum('quantity') : 0;
        $navbarCategoryTree = \App\Models\MainCategory::query()
            ->with(['categoryDetails' => fn($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();
        $navbarSearchProducts = \App\Models\Product::query()
            ->with(['mainCategory', 'categoryDetail', 'productVariants'])
            ->where('status', 'active')
            ->whereNotNull('slug')
            ->latest()
            ->take(60)
            ->get()
            ->map(function ($product) {
                $image = trim((string) ($product->firstAvailableImagePath() ?? ''));
                if ($image !== '' && !str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
                    $image = asset('storage/' . ltrim($image, '/'));
                }
                if ($image === '') {
                    $image = 'https://via.placeholder.com/120x120?text=No+Image';
                }

                return [
                    'name' => (string) $product->name,
                    'meta' => (string) ($product->categoryDetail?->name ?? ($product->mainCategory?->name ?? 'Produk')),
                    'image' => $image,
                    'url' => route('frontend.detail-produk', ['slug' => $product->slug]),
                ];
            })
            ->values()
            ->all();
        $megaCategories = $navbarCategoryTree
            ->map(function ($parent) {
                $children = $parent->categoryDetails->values();
                $chunkSize = max(1, (int) ceil(max(1, $children->count()) / 4));
                $columns = $children
                    ->chunk($chunkSize)
                    ->take(4)
                    ->map(function ($chunk) {
                        return [
                            'title' => 'Kategori',
                            'items' => $chunk
                                ->map(
                                    fn($child) => [
                                        'name' => $child->name,
                                        'url' => route('frontend.kategori', ['category' => $child->slug]),
                                    ],
                                )
                                ->values()
                                ->all(),
                        ];
                    })
                    ->values()
                    ->all();

                if (empty($columns)) {
                    $columns[] = [
                        'title' => 'Kategori',
                        'items' => [],
                    ];
                }

                return [
                    'key' => $parent->slug,
                    'name' => $parent->name,
                    'url' => route('frontend.kategori', ['parent' => $parent->slug]),
                    'columns' => $columns,
                ];
            })
            ->values()
            ->all();
    @endphp

    {{-- Main navbar row --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-4 h-[68px]">

            {{-- Logo --}}
            <a href="{{ route('frontend.index') }}" class="flex items-center gap-2.5 flex-shrink-0 group">
                @if (!empty($appStoreLogoUrl))
                    <img src="{{ $appStoreLogoUrl }}" alt="{{ $appStoreName }}"
                        class="h-10 w-auto max-w-[120px] object-contain transition-transform duration-200 group-hover:scale-105">
                @else
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-sky-400 via-blue-500 to-violet-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-sky-200 group-hover:scale-105 transition-all duration-200 overflow-hidden">
                        <i class="fi fi-rr-shopping-cart text-white text-xl leading-none"></i>
                    </div>
                    <div class="hidden sm:flex flex-col leading-none">
                        <span class="text-[10px] font-medium text-slate-400 tracking-widest uppercase">Official Store</span>
                        <span
                            class="text-lg font-extrabold bg-gradient-to-r from-sky-500 to-violet-600 bg-clip-text text-transparent leading-tight max-w-[180px] truncate">{{ $appStoreName }}</span>
                    </div>
                @endif
            </a>

            {{-- Search bar (desktop) --}}
            <div class="hidden md:flex flex-1 max-w-3xl ml-4 mr-2 relative items-center">
                <div class="relative flex-1">
                    <form action="{{ route('frontend.search') }}" method="GET"
                        class="flex rounded-full overflow-hidden border-2 border-slate-200 focus-within:border-sky-400 focus-within:ring-4 focus-within:ring-sky-100 transition-all bg-slate-50 focus-within:bg-white">
                        <div class="flex items-center pl-4 text-slate-400 flex-shrink-0">
                            <i class="fi fi-rr-search text-sm leading-none"></i>
                        </div>
                        <input type="text" id="ecNavSearchDesktop" name="q"
                            value="{{ trim(request('q', $query ?? '')) }}" placeholder="Cari produk, merek, kategori..."
                            class="flex-1 px-3 py-2.5 text-sm outline-none bg-transparent text-slate-700 placeholder-slate-400"
                            autocomplete="off" />
                        <button type="submit"
                            class="bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white px-5 font-semibold text-sm flex items-center gap-1.5 transition-all flex-shrink-0 rounded-full my-1 mr-1">
                            Cari
                        </button>
                    </form>
                    <div id="ecNavSearchDropdownDesktop"
                        class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                    </div>
                </div>
            </div>

            {{-- Right actions --}}
            <div class="flex items-center gap-0.5 flex-shrink-0 ml-auto">
                {{-- Mobile search toggle --}}
                <button id="ecMobileSearchToggle"
                    class="md:hidden p-2.5 rounded-xl hover:bg-slate-100 transition-colors text-slate-500">
                    <i class="fi fi-rr-search text-lg leading-none"></i>
                </button>

                {{-- Wishlist --}}
                <a href="{{ route('frontend.profil') }}?tab=wishlist"
                    class="hidden sm:flex items-center justify-center p-2.5 rounded-full hover:bg-slate-50 transition-all group"
                    title="Wishlist">
                    <div class="relative">
                        <i class="fi fi-rr-heart nav-action-icon"></i>
                    </div>
                </a>

                {{-- Notification (auth only) --}}
                @auth
                    <div class="relative">
                        <button id="ecNotifTrigger" type="button"
                            class="flex items-center justify-center p-2.5 rounded-full hover:bg-slate-50 transition-all group relative"
                            title="Notifikasi">
                            <div class="relative">
                                <i class="fi fi-rr-bell nav-action-icon"></i>
                                <span id="ecNotifBadge" class="nav-action-badge hidden"></span>
                            </div>
                        </button>
                        <div id="ecNotifDropdown"
                            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                            <div
                                class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                                <span class="font-semibold text-sm text-slate-800 flex items-center gap-2">
                                    <i class="fi fi-rr-bell text-sky-500 text-sm leading-none"></i>
                                    Notifikasi
                                </span>
                                <button id="ecNotifReadAll"
                                    class="text-xs text-sky-600 hover:text-sky-700 font-semibold px-2 py-1 rounded-lg hover:bg-sky-50 transition-colors">Tandai
                                    dibaca</button>
                            </div>
                            <div id="ecNotifList" class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
                                <div class="px-4 py-6 text-center text-sm text-slate-400">Memuat...</div>
                            </div>
                            <div class="px-4 py-3 border-t border-slate-100 text-center bg-slate-50/60">
                                <a href="{{ route('frontend.profil') }}?tab=notif"
                                    class="inline-flex items-center gap-1 text-sm text-sky-600 hover:text-sky-700 font-semibold">Lihat Semua Notifikasi
                                    <i class="fi fi-rr-angle-small-right text-xs leading-none"></i></a>
                            </div>
                        </div>
                    </div>
                @endauth

                {{-- Cart --}}
                <a href="{{ route('frontend.cart') }}"
                    class="flex items-center justify-center p-2.5 rounded-full hover:bg-slate-50 transition-all group relative"
                    title="Keranjang">
                    <div class="relative">
                        <i class="fi fi-rr-shopping-cart nav-action-icon"></i>
                        <span id="cartCount"
                            class="nav-action-badge {{ $cartCount > 0 ? 'flex' : 'hidden' }}">{{ $cartCount }}</span>
                    </div>
                </a>

                {{-- Divider --}}
                <div class="w-px h-8 bg-slate-200 mx-1 hidden sm:block"></div>

                {{-- User / Login --}}
                @auth
                    <div class="relative">
                        <button id="ecAccountTrigger" type="button"
                            class="flex items-center gap-2 px-2.5 py-2 rounded-xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-200">
                            @if ($hasAvatarImage)
                                <img src="{{ $avatarUrl }}" alt="{{ $displayFirstName }}"
                                    class="w-8 h-8 rounded-full object-cover border-2 border-sky-200 ring-2 ring-offset-1 ring-sky-100" />
                            @else
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-white text-sm font-bold shadow-sm ring-2 ring-offset-1 ring-sky-100">
                                    {{ $initial }}
                                </div>
                            @endif
                            <div class="hidden sm:flex flex-col items-start leading-tight">
                                <span class="text-[10px] text-slate-400 font-normal">Halo,</span>
                                <span class="text-sm font-bold text-slate-700">{{ $displayFirstName }}</span>
                            </div>
                            <i class="fi fi-rr-angle-small-down hidden sm:block text-sm text-slate-400 ml-0.5 leading-none"></i>
                        </button>
                        <div id="ecAccountDropdown"
                            class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-br from-sky-50 to-violet-50">
                                <p class="text-sm font-bold text-slate-800">{{ $displayName }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('frontend.profil') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                                    <i class="fi fi-rr-user text-[13px] text-slate-500 leading-none"></i>
                                </div>
                                Profil Saya
                            </a>
                            <a href="{{ route('frontend.profil') }}?tab=pesanan"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                                    <i class="fi fi-rr-box-open-full text-[13px] text-slate-500 leading-none"></i>
                                </div>
                                Riwayat Pesanan
                            </a>
                            @if ($authUser?->canAccessAdminPanel())
                                <a href="{{ route('pages.index') }}"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                                        <i class="fi fi-rr-dashboard text-[13px] text-slate-500 leading-none"></i>
                                    </div>
                                    Kembali ke Admin Dashboard
                                </a>
                            @endif
                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-rose-500 hover:bg-rose-50 transition-colors">
                                        <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center">
                                            <i class="fi fi-rr-exit text-[13px] text-rose-400 leading-none"></i>
                                        </div>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-200">
                        <div
                            class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                            <i class="fi fi-rr-user text-base text-slate-500 leading-none"></i>
                        </div>
                        <div class="hidden sm:flex flex-col items-start leading-tight">
                            <span class="text-[10px] text-slate-400 font-normal">Halo,</span>
                            <span class="text-sm font-bold text-slate-700">Login / Daftar</span>
                        </div>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Secondary nav row --}}
    <div class="border-t border-slate-100 bg-white py-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center h-11 overflow-x-auto scrollbar-hide" style="scrollbar-width:none;">

                {{-- Browse Categories button --}}
                <div class="relative flex-shrink-0">
                    <button id="ecCategoryTrigger" type="button"
                        class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold transition-all whitespace-nowrap group">
                        <i class="fi fi-rr-apps text-sm leading-none"></i>
                        Jelajahi Kategori
                        <i class="fi fi-rr-angle-small-down w-3 text-slate-400 group-hover:rotate-180 transition-transform duration-200 leading-none"></i>
                    </button>

                    {{-- Category mega dropdown --}}
                    <div id="ecCategoryDropdown"
                        class="hidden fixed bg-white rounded-2xl shadow-2xl border border-slate-100 z-[70] overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-sky-400 via-violet-500 to-fuchsia-400"></div>
                        <div class="flex" style="min-height:320px">
                            <div class="bg-slate-50/60 border-r border-slate-100 flex flex-col flex-shrink-0" style="width:140px">
                                <div class="px-3 pt-4 pb-2">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kategori</p>
                                </div>
                                <div id="ecMegaCategoryMenu" class="flex-1 overflow-y-auto px-1.5 pb-2 space-y-0.5">
                                </div>
                                <div class="border-t border-slate-100 p-2">
                                    <a href="{{ route('frontend.kategori') }}"
                                        class="flex items-center justify-center gap-1 text-[11px] text-sky-600 hover:text-sky-700 font-semibold py-1.5 px-2 rounded-lg hover:bg-sky-50 transition-colors">
                                        <i class="fi fi-rr-apps text-xs leading-none"></i>
                                        Semua Kategori
                                    </a>
                                </div>
                            </div>
                            <div id="ecMegaCategoryContent" class="flex-1 p-4 overflow-y-auto min-w-0"></div>
                        </div>
                    </div>
                </div>

                {{-- Mobile hamburger (hidden on desktop) --}}
                <button id="ecMobileNavToggle" type="button"
                    class="md:hidden flex items-center gap-1.5 ml-auto px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors flex-shrink-0">
                    <i id="ecMobileNavIcon" class="fi fi-rr-menu-burger text-sm leading-none"></i>
                    <span class="text-xs">Menu</span>
                </button>

                {{-- Nav links (desktop only) --}}
                <div class="hidden md:flex items-center">
                    <div class="w-px h-5 bg-slate-200 mx-3 flex-shrink-0"></div>
                    <a href="{{ route('frontend.index') }}"
                        class="nav-link {{ request()->routeIs('frontend.index') ? 'active' : '' }}">
                        Beranda
                    </a>
                    <a href="{{ route('frontend.kategori') }}"
                        class="nav-link {{ request()->routeIs('frontend.kategori') ? 'active' : '' }}">
                        Semua Produk
                    </a>
                    <a href="{{ route('frontend.redeem-point') }}"
                        class="nav-link {{ request()->routeIs('frontend.redeem-point') ? 'active' : '' }}">
                        Redeem Point
                    </a>
                    <a href="{{ route('frontend.search') }}?sort=newest" class="nav-link">
                        Produk Terbaru
                    </a>
                    <a href="{{ route('frontend.search') }}?sort=popular"
                        class="nav-link flex items-center gap-1.5 !text-amber-500 !font-semibold">
                        <i class="fi fi-rr-star text-[13px] text-amber-400 leading-none"></i>
                        Terlaris
                    </a>
                    @auth
                        <a href="{{ route('frontend.profil') }}?tab=pesanan" class="nav-link">
                            Pesanan Saya
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile nav drawer --}}
    <div id="ecMobileNavDrawer"
        class="hidden md:hidden border-t border-slate-100 bg-white shadow-lg z-[60]">
        <div class="px-4 py-3 grid grid-cols-2 gap-2">
            <a href="{{ route('frontend.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                       {{ request()->routeIs('frontend.index') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fi fi-rr-home text-base leading-none {{ request()->routeIs('frontend.index') ? 'text-blue-500' : 'text-slate-400' }}"></i>
                Beranda
            </a>
            <a href="{{ route('frontend.kategori') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                       {{ request()->routeIs('frontend.kategori') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fi fi-rr-apps text-base leading-none {{ request()->routeIs('frontend.kategori') ? 'text-blue-500' : 'text-slate-400' }}"></i>
                Semua Produk
            </a>
            <a href="{{ route('frontend.redeem-point') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                       {{ request()->routeIs('frontend.redeem-point') ? 'bg-amber-50 text-amber-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fi fi-rr-coin text-base leading-none {{ request()->routeIs('frontend.redeem-point') ? 'text-amber-500' : 'text-slate-400' }}"></i>
                Redeem Point
            </a>
            <a href="{{ route('frontend.search') }}?sort=newest"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fi fi-rr-sparkles text-base text-slate-400 leading-none"></i>
                Produk Terbaru
            </a>
            <a href="{{ route('frontend.search') }}?sort=popular"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-amber-500 hover:bg-amber-50 transition-colors">
                <i class="fi fi-rr-star text-base text-amber-400 leading-none"></i>
                Terlaris
            </a>
            @auth
            <a href="{{ route('frontend.profil') }}?tab=pesanan"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fi fi-rr-box-open-full text-base text-slate-400 leading-none"></i>
                Pesanan Saya
            </a>
            @endauth
        </div>
    </div>
</nav>

{{-- Mobile search row --}}
<div id="ecMobileSearch" class="hidden md:hidden px-4 pb-3 border-b border-slate-100 pt-3 bg-white overflow-hidden">
    <form action="{{ route('frontend.search') }}" method="GET"
        class="flex w-full border-2 border-slate-200 rounded-full overflow-hidden focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100 transition-all bg-slate-50">
        <div class="flex items-center pl-3 text-slate-400 flex-shrink-0">
            <i class="fi fi-rr-search text-sm leading-none"></i>
        </div>
        <input type="text" id="ecNavSearchMobile" name="q" value="{{ trim(request('q', $query ?? '')) }}"
            placeholder="Cari produk..." class="flex-1 min-w-0 px-3 py-2.5 text-sm outline-none bg-transparent"
            autocomplete="off" />
        <button type="submit"
            class="flex-shrink-0 bg-sky-500 hover:bg-sky-600 text-white px-4 flex items-center font-medium text-sm transition-colors rounded-full my-1 mr-1">
            Cari
        </button>
    </form>
    <div id="ecNavSearchDropdownMobile"
        class="hidden mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden"></div>
</div>

<script>
    (() => {
        if (window.__ecNavbarReady) return;
        window.__ecNavbarReady = true;

        const products = @json($navbarSearchProducts);

        const megaCategories = @json($megaCategories);

        const skeletonHtml = () => Array.from({
            length: 4
        }, () => `
            <div class="flex items-center gap-3 px-3 py-3 border-b border-slate-100 last:border-b-0 animate-pulse">
                <div class="w-12 h-12 rounded-lg bg-slate-200"></div>
                <div class="flex-1">
                    <div class="h-3 rounded bg-slate-200 w-40 mb-2"></div>
                    <div class="h-3 rounded bg-slate-200 w-24"></div>
                </div>
                <div class="w-5 h-5 rounded bg-slate-200"></div>
            </div>
        `).join('');

        const normalizeSearch = (value) => String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        const compactSearch = (value) => normalizeSearch(value).replace(/\s+/g, '');

        function scoreSearchMatch(product, keyword) {
            const q = normalizeSearch(keyword);
            if (!q) return -1;

            const qCompact = compactSearch(q);
            const name = normalizeSearch(product.name || '');
            const meta = normalizeSearch(product.meta || '');
            const haystack = `${name} ${meta}`.trim();
            const haystackCompact = compactSearch(haystack);
            const qTokens = q.split(' ').filter(Boolean);

            let score = 0;
            if (name === q) score += 120;
            if (name.startsWith(q)) score += 90;
            if (name.includes(q)) score += 70;
            if (meta.includes(q)) score += 35;
            if (haystackCompact.includes(qCompact)) score += 60;

            const tokenHits = qTokens.filter(token => haystack.includes(token) || haystackCompact.includes(token)).length;
            score += tokenHits * 18;

            if (!score && qCompact.length >= 3) {
                const chars = qCompact.split('');
                let pointer = 0;
                for (const char of haystackCompact) {
                    if (char === chars[pointer]) pointer++;
                    if (pointer >= chars.length) break;
                }
                if (pointer >= Math.max(3, qCompact.length - 1)) score += 24;
            }

            return score;
        }

        function renderSearchResults(container, keyword) {
            const val = keyword.trim();
            if (!val) {
                container.classList.add('hidden');
                container.innerHTML = '';
                return;
            }

            container.classList.remove('hidden');
            container.innerHTML = skeletonHtml();

            setTimeout(() => {
                const scored = products
                    .map((p) => ({ ...p, __score: scoreSearchMatch(p, val) }))
                    .filter((p) => p.__score > 0)
                    .sort((a, b) => b.__score - a.__score)
                    .slice(0, 5);

                if (!scored.length) {
                    container.innerHTML = `
                        <div class="px-4 py-5 text-center text-sm text-slate-500">
                            Produk tidak ditemukan.<br>
                            <span class="text-xs text-slate-400">Coba kata yang lebih umum atau ejaan yang lebih singkat.</span>
                        </div>`;
                    return;
                }

                const suggestion = normalizeSearch(val) !== normalizeSearch(scored[0]?.name || '')
                    ? `<div class="px-3 py-2 text-[11px] text-slate-400 border-b border-slate-100">Saran terdekat: <span class="font-semibold text-slate-600">${scored[0].name}</span></div>`
                    : '';

                container.innerHTML = suggestion + scored.map((p) => `
                    <a href="${p.url}" class="flex items-center gap-3 px-3 py-3 border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition-colors">
                        <img src="${p.image}" alt="${p.name}" class="w-12 h-12 rounded-lg object-cover border border-slate-100" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">${p.name}</p>
                            <p class="text-xs text-slate-500 truncate">${p.meta}</p>
                        </div>
                        <i class="fi fi-rr-angle-small-right text-sm text-slate-400 flex-shrink-0 leading-none"></i>
                    </a>
                `).join('');
            }, 220);
        }

        function bindLiveSearch(inputId, dropdownId) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            if (!input || !dropdown) return;
            let timer = null;
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => renderSearchResults(dropdown, input.value || ''), 80);
            });
            input.addEventListener('focus', () => {
                if ((input.value || '').trim()) renderSearchResults(dropdown, input.value || '');
            });
        }

        bindLiveSearch('ecNavSearchDesktop', 'ecNavSearchDropdownDesktop');
        bindLiveSearch('ecNavSearchMobile', 'ecNavSearchDropdownMobile');

        const isAuthenticated = @json(auth()->check());
        const cartCountUrl = @json(auth()->check() ? route('frontend.cart.count') : null);
        const notifUrl = @json(auth()->check() ? route('frontend.notifications.index') : null);
        const notifReadAllUrl = @json(auth()->check() ? route('frontend.notifications.read-all') : null);
        const csrfToken = @json(csrf_token());

        function syncCartBadge() {
            const badge = document.getElementById('cartCount');
            const mobileBadge = document.getElementById('mobileCartBadge');

            const applyCount = (qty) => {
                if (badge) {
                    badge.textContent = String(qty);
                    badge.classList.toggle('hidden', qty <= 0);
                    badge.classList.toggle('flex', qty > 0);
                }
                if (mobileBadge) {
                    mobileBadge.textContent = String(qty);
                    mobileBadge.classList.toggle('hidden', qty <= 0);
                    mobileBadge.classList.toggle('flex', qty > 0);
                }
            };

            if (!isAuthenticated || !cartCountUrl) {
                applyCount(0);
                return;
            }

            fetch(cartCountUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then((res) => res.ok ? res.json() : { count: 0 })
                .then((data) => applyCount(Number(data?.count || 0)))
                .catch(() => applyCount(0));
        }
        syncCartBadge();
        window.addEventListener('cart:updated', syncCartBadge);

        // Ã¢â€â‚¬Ã¢â€â‚¬ Notification dropdown Ã¢â€â‚¬Ã¢â€â‚¬
        const notifTrigger = document.getElementById('ecNotifTrigger');
        const notifDropdown = document.getElementById('ecNotifDropdown');
        const notifBadge = document.getElementById('ecNotifBadge');
        const notifList = document.getElementById('ecNotifList');
        const notifReadAllBtn = document.getElementById('ecNotifReadAll');
        let notifLoaded = false;

        const notifTypeIcon = {
            transaction_created: {
                bg: 'bg-sky-100',
                icon: `<i class="fi fi-rr-receipt text-sm text-sky-600 leading-none"></i>`
            },
            payment_received: {
                bg: 'bg-green-100',
                icon: `<i class="fi fi-rr-badge-check text-sm text-green-600 leading-none"></i>`
            },
            payment_rejected: {
                bg: 'bg-rose-100',
                icon: `<i class="fi fi-rr-triangle-warning text-sm text-rose-600 leading-none"></i>`
            },
            order_processed: {
                bg: 'bg-indigo-100',
                icon: `<i class="fi fi-rr-box-open-full text-sm text-indigo-600 leading-none"></i>`
            },
            order_shipped: {
                bg: 'bg-purple-100',
                icon: `<i class="fi fi-rr-truck-side text-sm text-purple-600 leading-none"></i>`
            },
            order_completed: {
                bg: 'bg-emerald-100',
                icon: `<i class="fi fi-rr-badge-check text-sm text-emerald-600 leading-none"></i>`
            },
        };

        function syncNotifBadge(unread) {
            if (!notifBadge) return;
            if (unread > 0) {
                notifBadge.textContent = unread > 9 ? '9+' : String(unread);
                notifBadge.classList.remove('hidden');
                notifBadge.classList.add('flex');
            } else {
                notifBadge.textContent = '';
                notifBadge.classList.add('hidden');
                notifBadge.classList.remove('flex');
            }
        }

        function renderNotifList(notifications) {
            if (!notifList) return;
            if (!notifications.length) {
                notifList.innerHTML =
                    `<div class="px-4 py-6 text-center text-sm text-slate-400">Belum ada notifikasi</div>`;
                return;
            }
            notifList.innerHTML = notifications.slice(0, 5).map(n => {
                const ic = notifTypeIcon[n.type] || {
                    bg: 'bg-slate-100',
                    icon: `<i class="fi fi-rr-bell text-sm text-slate-500 leading-none"></i>`
                };
                const tag = n.url ? 'a' : 'div';
                const href = n.url ? `href="${n.url}"` : '';
                return `<${tag} ${href} class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors cursor-pointer ${n.read ? '' : 'bg-sky-50/40'}">
                    <div class="w-8 h-8 rounded-full ${ic.bg} flex items-center justify-center shrink-0 mt-0.5">${ic.icon}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 leading-tight">${n.title}</p>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">${n.body}</p>
                        <p class="text-[11px] text-slate-400 mt-1">${n.created_at}</p>
                    </div>
                    ${!n.read ? '<span class="w-2 h-2 rounded-full bg-sky-500 shrink-0 mt-1.5"></span>' : ''}
                </${tag}>`;
            }).join('');
        }

        async function loadNotifications() {
            if (!notifUrl || !isAuthenticated) return;
            try {
                const res = await fetch(notifUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                syncNotifBadge(data.unread || 0);
                renderNotifList(data.notifications || []);
                notifLoaded = true;
            } catch {
                syncNotifBadge(0);
            }
        }

        const positionNotifDropdown = () => {
            const rect = notifTrigger.getBoundingClientRect();
            if (window.innerWidth < 768) {
                const margin = 12;
                notifDropdown.style.position = 'fixed';
                notifDropdown.style.top = (rect.bottom + 6) + 'px';
                notifDropdown.style.left = margin + 'px';
                notifDropdown.style.right = margin + 'px';
                notifDropdown.style.width = 'auto';
            } else {
                notifDropdown.style.position = '';
                notifDropdown.style.top = '';
                notifDropdown.style.left = '';
                notifDropdown.style.right = '';
                notifDropdown.style.width = '';
            }
        };

        if (notifTrigger && notifDropdown && isAuthenticated) {
            notifTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const opening = notifDropdown.classList.contains('hidden');
                notifDropdown.classList.toggle('hidden');
                if (opening) {
                    positionNotifDropdown();
                    if (!notifLoaded) loadNotifications();
                }
            });
        }

        if (notifReadAllBtn && isAuthenticated) {
            notifReadAllBtn.addEventListener('click', async () => {
                await fetch(notifReadAllUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                syncNotifBadge(0);
                loadNotifications();
            });
        }

        if (isAuthenticated && notifUrl) {
            loadNotifications();
            setInterval(() => {
                fetch(notifUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.ok ? r.json() : null)
                    .then(d => d && syncNotifBadge(d.unread || 0))
                    .catch(() => {});
            }, 60000);
        }

        const mobileSearch = document.getElementById('ecMobileSearch');
        const mobileSearchToggle = document.getElementById('ecMobileSearchToggle');
        if (mobileSearch && mobileSearchToggle) {
            mobileSearchToggle.addEventListener('click', () => {
                mobileSearch.classList.toggle('hidden');
            });
        }

        const categoryTrigger = document.getElementById('ecCategoryTrigger');
        const categoryDropdown = document.getElementById('ecCategoryDropdown');
        const categoryMenu = document.getElementById('ecMegaCategoryMenu');
        const categoryContent = document.getElementById('ecMegaCategoryContent');
        if (categoryTrigger && categoryDropdown && categoryMenu && categoryContent) {
            let active = megaCategories[0]?.key || null;

            const positionCategoryDropdown = () => {
                const rect = categoryTrigger.getBoundingClientRect();
                const gap = 6;
                const pageGap = window.innerWidth >= 640 ? 24 : 16;
                const containerWidth = Math.min(1280, window.innerWidth - (pageGap * 2));
                const containerLeft = Math.max(pageGap, (window.innerWidth - containerWidth) / 2);
                const left = containerLeft;
                const width = containerWidth;

                categoryDropdown.style.width = `${width}px`;
                categoryDropdown.style.left = `${left}px`;
                categoryDropdown.style.top = `${rect.bottom + gap}px`;
                categoryDropdown.style.maxHeight = `${Math.max(280, window.innerHeight - rect.bottom - 24)}px`;
            };

            const catIcons = {
                'elektronik': '<i class="fi fi-rr-laptop text-sm leading-none"></i>',
                'fashion pria': '<i class="fi fi-rr-user text-sm leading-none"></i>',
                'fashion wanita': '<i class="fi fi-rr-user text-sm leading-none"></i>',
                'hp & tablet': '<i class="fi fi-rr-mobile-notch text-sm leading-none"></i>',
                'ibu & bayi': '<i class="fi fi-rr-heart text-sm leading-none"></i>',
                'kecantikan': '<i class="fi fi-rr-sparkles text-sm leading-none"></i>',
                'mainan & anak': '<i class="fi fi-rr-face-smile text-sm leading-none"></i>',
                'makanan & minuman': '<i class="fi fi-rr-utensils text-sm leading-none"></i>',
                'olahraga': '<i class="fi fi-rr-bolt text-sm leading-none"></i>',
                'rumah tangga': '<i class="fi fi-rr-home text-sm leading-none"></i>',
            };
            const getCatIcon = (name) => catIcons[name.toLowerCase()] ||
                '<i class="fi fi-rr-box text-sm leading-none"></i>';

            const renderMenu = () => {
                if (!megaCategories.length) {
                    categoryMenu.innerHTML =
                        `<div class="px-3 py-2 text-sm text-slate-400">Belum ada kategori</div>`;
                    return;
                }

                categoryMenu.innerHTML = megaCategories.map((cat) => {
                    const isActive = cat.key === active;
                    return `
                    <a href="#" data-key="${cat.key}"
                       class="flex items-center gap-2 px-2 py-2 rounded-lg text-xs transition-all group
                              ${isActive ? 'bg-sky-500 text-white font-semibold shadow-sm shadow-sky-200' : 'text-slate-600 hover:bg-white hover:text-slate-800 hover:shadow-sm'}">
                        <span class="flex-shrink-0 ${isActive ? 'text-sky-100' : 'text-slate-400 group-hover:text-sky-500 transition-colors'}">${getCatIcon(cat.name)}</span>
                        <span class="leading-tight truncate">${cat.name}</span>
                    </a>`;
                }).join('');
            };

            const renderColumns = () => {
                if (!megaCategories.length) {
                    categoryContent.innerHTML =
                        `<div class="h-full flex items-center justify-center text-sm text-slate-400">Kategori belum tersedia</div>`;
                    return;
                }

                const found = megaCategories.find((x) => x.key === active) || megaCategories[0];
                const allItems = found.columns.flatMap(col => col.items);
                const hasItems = allItems.length > 0;

                categoryContent.innerHTML = `
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-800 truncate">${found.name}</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">${allItems.length} sub-kategori</p>
                        </div>
                        <a href="${found.url}"
                           class="flex-shrink-0 flex items-center gap-1 text-[11px] font-semibold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2.5 py-1.5 rounded-lg transition-colors">
                            Lihat Semua
                            <i class="fi fi-rr-angle-small-right text-xs leading-none"></i>
                        </a>
                    </div>
                    <div class="h-px bg-gradient-to-r from-sky-100 via-slate-100 to-transparent mb-3"></div>
                    <div class="grid ${window.innerWidth < 640 ? 'grid-cols-2 gap-x-3' : 'grid-cols-4 gap-x-6'} gap-y-1">
                        ${found.columns.map((col) => `
                            <div>
                                <ul class="space-y-0.5">
                                    ${col.items.length
                                        ? col.items.map((item) => `
                                            <li>
                                                <a href="${item.url}"
                                                   class="flex items-center gap-2 text-sm text-slate-600 hover:text-sky-600 py-1.5 px-2 rounded-lg hover:bg-sky-50 group transition-all">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-200 group-hover:bg-sky-400 flex-shrink-0 transition-colors"></span>
                                                    <span class="flex-1 leading-tight">${item.name}</span>
                                                    <i class="fi fi-rr-angle-small-right text-xs text-sky-300 opacity-0 group-hover:opacity-100 flex-shrink-0 transition-opacity leading-none"></i>
                                                </a>
                                            </li>`).join('')
                                        : '<li><span class="text-sm text-slate-300 px-2">Segera hadir</span></li>'}
                                </ul>
                            </div>
                        `).join('')}
                    </div>
                    ${hasItems ? '' : `
                    <div class="mt-6 flex items-center justify-center h-24 rounded-xl bg-slate-50 border border-dashed border-slate-200">
                        <p class="text-sm text-slate-400">Belum ada sub-kategori</p>
                    </div>`}
                `;
            };

            renderMenu();
            renderColumns();

            categoryMenu.addEventListener('click', (e) => {
                e.stopPropagation();
                const target = e.target.closest('[data-key]');
                if (!target) return;
                e.preventDefault();
                active = target.getAttribute('data-key') || megaCategories[0].key;
                renderMenu();
                renderColumns();
            });

            categoryTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                positionCategoryDropdown();
                categoryDropdown.classList.toggle('hidden');
            });

            window.addEventListener('resize', () => {
                if (!categoryDropdown.classList.contains('hidden')) {
                    positionCategoryDropdown();
                }
            });

            window.addEventListener('scroll', () => {
                if (!categoryDropdown.classList.contains('hidden')) {
                    positionCategoryDropdown();
                }
            }, true);
        }

        const accountTrigger = document.getElementById('ecAccountTrigger');
        const accountDropdown = document.getElementById('ecAccountDropdown');
        if (accountTrigger && accountDropdown) {
            accountTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                accountDropdown.classList.toggle('hidden');
            });
        }

        // Mobile nav hamburger toggle
        const mobileNavToggle = document.getElementById('ecMobileNavToggle');
        const mobileNavDrawer = document.getElementById('ecMobileNavDrawer');
        const mobileNavIcon = document.getElementById('ecMobileNavIcon');
        if (mobileNavToggle && mobileNavDrawer) {
            mobileNavToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !mobileNavDrawer.classList.contains('hidden');
                mobileNavDrawer.classList.toggle('hidden');
                mobileNavIcon.className = isOpen
                    ? 'fi fi-rr-menu-burger text-sm leading-none'
                    : 'fi fi-rr-cross text-sm leading-none';
            });
        }

        document.addEventListener('click', (e) => {
            if (mobileNavDrawer && mobileNavToggle &&
                !mobileNavDrawer.contains(e.target) && !mobileNavToggle.contains(e.target)) {
                mobileNavDrawer.classList.add('hidden');
                if (mobileNavIcon) mobileNavIcon.className = 'fi fi-rr-menu-burger text-sm leading-none';
            }

            if (notifDropdown && notifTrigger && !notifDropdown.contains(e.target) && !notifTrigger
                .contains(e.target)) {
                notifDropdown.classList.add('hidden');
            }
            if (accountDropdown && accountTrigger && !accountDropdown.contains(e.target) && !accountTrigger
                .contains(e.target)) {
                accountDropdown.classList.add('hidden');
            }

            const categoryDropdownEl = document.getElementById('ecCategoryDropdown');
            const categoryTriggerEl = document.getElementById('ecCategoryTrigger');
            if (categoryDropdownEl && categoryTriggerEl && !categoryDropdownEl.contains(e.target) && !
                categoryTriggerEl.contains(e.target)) {
                categoryDropdownEl.classList.add('hidden');
            }

            ['ecNavSearchDropdownDesktop', 'ecNavSearchDropdownMobile'].forEach((id) => {
                const box = document.getElementById(id);
                if (!box) return;
                const inputId = id.includes('Desktop') ? 'ecNavSearchDesktop' : 'ecNavSearchMobile';
                const input = document.getElementById(inputId);
                if (!input) return;
                if (!box.contains(e.target) && e.target !== input) {
                    box.classList.add('hidden');
                }
            });
        });
    })();
</script>
