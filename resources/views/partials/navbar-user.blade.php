{{-- Announcement bar --}}
<div
    class="relative overflow-hidden bg-gradient-to-r from-violet-700 via-indigo-600 to-sky-600 text-white text-center text-xs py-2 px-4">
    <div class="relative z-10 flex items-center justify-center gap-2">
        <span class="text-yellow-300 text-sm">★</span>
        <span class="font-medium tracking-wide">Belanja lebih hemat dengan promo spesial hari ini</span>
        <span class="hidden sm:inline text-indigo-200">—</span>
        <a href="{{ route('frontend.flash-sale') }}"
            class="hidden sm:inline-flex items-center gap-1 font-bold text-yellow-300 hover:text-yellow-100 transition-colors ml-1 border-b border-yellow-400/50 pb-px">
            Lihat Promo
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
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
                <div
                    class="w-10 h-10 bg-gradient-to-br from-sky-400 via-blue-500 to-violet-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-sky-200 group-hover:scale-105 transition-all duration-200 overflow-hidden">
                    @if (!empty($appStoreLogoUrl))
                        <img src="{{ $appStoreLogoUrl }}" alt="{{ $appStoreName }}" class="w-full h-full object-contain bg-white p-1">
                    @else
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                            <path d="M16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                        </svg>
                    @endif
                </div>
                <div class="hidden sm:flex flex-col leading-none">
                    <span class="text-[10px] font-medium text-slate-400 tracking-widest uppercase">Official Store</span>
                    <span
                        class="text-lg font-extrabold bg-gradient-to-r from-sky-500 to-violet-600 bg-clip-text text-transparent leading-tight max-w-[180px] truncate">{{ $appStoreName }}</span>
                </div>
            </a>

            {{-- Search bar (desktop) --}}
            <div class="hidden md:flex flex-1 max-w-xl mx-auto relative items-center">
                <div class="relative flex-1">
                    <form action="{{ route('frontend.search') }}" method="GET"
                        class="flex rounded-full overflow-hidden border-2 border-slate-200 focus-within:border-sky-400 focus-within:ring-4 focus-within:ring-sky-100 transition-all bg-slate-50 focus-within:bg-white">
                        <div class="flex items-center pl-4 text-slate-400 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
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
            <div class="flex items-center gap-0.5 ml-auto">
                {{-- Mobile search toggle --}}
                <button id="ecMobileSearchToggle"
                    class="md:hidden p-2.5 rounded-xl hover:bg-slate-100 transition-colors text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                {{-- Wishlist --}}
                <a href="{{ route('frontend.profil') }}?tab=wishlist"
                    class="hidden sm:flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl hover:bg-slate-50 transition-all group"
                    title="Wishlist">
                    <div class="relative">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-rose-500 transition-colors duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <span
                        class="text-[10px] text-slate-400 group-hover:text-rose-400 leading-none font-medium">Wishlist</span>
                </a>

                {{-- Notification (auth only) --}}
                @auth
                    <div class="relative">
                        <button id="ecNotifTrigger" type="button"
                            class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl hover:bg-slate-50 transition-all group relative"
                            title="Notifikasi">
                            <div class="relative">
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-sky-500 transition-colors duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span id="ecNotifBadge"
                                    class="absolute -top-1 -right-1.5 bg-rose-500 text-white text-[9px] min-w-4 h-4 px-1 rounded-full hidden items-center justify-center leading-none font-bold ring-2 ring-white"></span>
                            </div>
                            <span
                                class="text-[10px] text-slate-400 group-hover:text-sky-500 leading-none font-medium hidden lg:block">Notifikasi</span>
                        </button>
                        <div id="ecNotifDropdown"
                            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                            <div
                                class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                                <span class="font-semibold text-sm text-slate-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
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
                                    class="text-sm text-sky-600 hover:text-sky-700 font-semibold">Lihat Semua Notifikasi
                                    →</a>
                            </div>
                        </div>
                    </div>
                @endauth

                {{-- Cart --}}
                <a href="{{ route('frontend.cart') }}"
                    class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl hover:bg-slate-50 transition-all group relative"
                    title="Keranjang">
                    <div class="relative">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-sky-500 transition-colors duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span id="cartCount"
                            class="absolute -top-1 -right-1.5 bg-sky-500 text-white text-[9px] min-w-4 h-4 px-1 rounded-full {{ $cartCount > 0 ? 'flex' : 'hidden' }} items-center justify-center leading-none font-bold ring-2 ring-white">{{ $cartCount }}</span>
                    </div>
                    <span
                        class="text-[10px] text-slate-400 group-hover:text-sky-500 leading-none font-medium">Keranjang</span>
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
                            <svg class="hidden sm:block w-3.5 h-3.5 text-slate-400 ml-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
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
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                Profil Saya
                            </a>
                            <a href="{{ route('frontend.profil') }}?tab=pesanan"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                Riwayat Pesanan
                            </a>
                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-rose-500 hover:bg-rose-50 transition-colors">
                                        <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
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
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
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
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Jelajahi Kategori
                        <svg class="w-3 h-3 text-slate-400 group-hover:rotate-180 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Category mega dropdown --}}
                    <div id="ecCategoryDropdown"
                        class="hidden fixed bg-white rounded-2xl shadow-2xl border border-slate-100 z-[70] overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-sky-400 via-violet-500 to-fuchsia-400"></div>
                        <div class="grid grid-cols-5" style="min-height:380px">
                            <div class="col-span-1 bg-slate-50/60 border-r border-slate-100 flex flex-col">
                                <div class="px-4 pt-4 pb-2">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kategori
                                    </p>
                                </div>
                                <div id="ecMegaCategoryMenu" class="flex-1 overflow-y-auto px-2 pb-2 space-y-0.5">
                                </div>
                                <div class="border-t border-slate-100 p-3">
                                    <a href="{{ route('frontend.kategori') }}"
                                        class="flex items-center justify-center gap-1.5 text-xs text-sky-600 hover:text-sky-700 font-semibold py-1.5 px-3 rounded-lg hover:bg-sky-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                        </svg>
                                        Semua Kategori
                                    </a>
                                </div>
                            </div>
                            <div id="ecMegaCategoryContent" class="col-span-4 p-6 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>

                <div class="w-px h-5 bg-slate-200 mx-3 flex-shrink-0"></div>

                {{-- Nav links --}}
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
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
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
</nav>

{{-- Mobile search row --}}
<div id="ecMobileSearch" class="hidden md:hidden px-4 pb-3 border-b border-slate-100 pt-3 bg-white">
    <form action="{{ route('frontend.search') }}" method="GET"
        class="flex border-2 border-slate-200 rounded-full overflow-hidden focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100 transition-all bg-slate-50">
        <div class="flex items-center pl-3 text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input type="text" id="ecNavSearchMobile" name="q" value="{{ trim(request('q', $query ?? '')) }}"
            placeholder="Cari produk..." class="flex-1 px-3 py-2.5 text-sm outline-none bg-transparent"
            autocomplete="off" />
        <button type="submit"
            class="bg-sky-500 hover:bg-sky-600 text-white px-4 flex items-center gap-1.5 font-medium text-sm transition-colors rounded-full my-1 mr-1">
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

        function renderSearchResults(container, keyword) {
            const val = keyword.trim().toLowerCase();
            if (!val) {
                container.classList.add('hidden');
                container.innerHTML = '';
                return;
            }

            container.classList.remove('hidden');
            container.innerHTML = skeletonHtml();

            setTimeout(() => {
                const filtered = products.filter((p) =>
                    p.name.toLowerCase().includes(val) || p.meta.toLowerCase().includes(val)
                ).slice(0, 4);

                if (!filtered.length) {
                    container.innerHTML = `
                        <div class="px-4 py-5 text-center text-sm text-slate-500">
                            Produk tidak ditemukan
                        </div>`;
                    return;
                }

                container.innerHTML = filtered.map((p) => `
                    <a href="${p.url}" class="flex items-center gap-3 px-3 py-3 border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition-colors">
                        <img src="${p.image}" alt="${p.name}" class="w-12 h-12 rounded-lg object-cover border border-slate-100" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">${p.name}</p>
                            <p class="text-xs text-slate-500 truncate">${p.meta}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                `).join('');
            }, 320);
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
            if (!badge) return;
            if (!isAuthenticated || !cartCountUrl) {
                badge.textContent = '0';
                badge.classList.add('hidden');
                return;
            }

            fetch(cartCountUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then((res) => res.ok ? res.json() : {
                    count: 0
                })
                .then((data) => {
                    const totalQty = Number(data?.count || 0);
                    badge.textContent = String(totalQty);
                    badge.classList.toggle('hidden', totalQty <= 0);
                    badge.classList.toggle('flex', totalQty > 0);
                })
                .catch(() => {
                    badge.textContent = '0';
                    badge.classList.add('hidden');
                });
        }
        syncCartBadge();
        window.addEventListener('cart:updated', syncCartBadge);

        // ── Notification dropdown ──
        const notifTrigger = document.getElementById('ecNotifTrigger');
        const notifDropdown = document.getElementById('ecNotifDropdown');
        const notifBadge = document.getElementById('ecNotifBadge');
        const notifList = document.getElementById('ecNotifList');
        const notifReadAllBtn = document.getElementById('ecNotifReadAll');
        let notifLoaded = false;

        const notifTypeIcon = {
            transaction_created: {
                bg: 'bg-sky-100',
                icon: `<svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>`
            },
            payment_received: {
                bg: 'bg-green-100',
                icon: `<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
            },
            order_processed: {
                bg: 'bg-indigo-100',
                icon: `<svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>`
            },
            order_shipped: {
                bg: 'bg-purple-100',
                icon: `<svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 13h12l1-13"/></svg>`
            },
        };

        function syncNotifBadge(unread) {
            if (!notifBadge) return;
            if (unread > 0) {
                notifBadge.textContent = unread > 9 ? '9+' : String(unread);
                notifBadge.classList.remove('hidden');
                notifBadge.classList.add('flex');
            } else {
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
                    icon: `<svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>`
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
            } catch {}
        }

        if (notifTrigger && notifDropdown && isAuthenticated) {
            notifTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown.classList.toggle('hidden');
                if (!notifDropdown.classList.contains('hidden') && !notifLoaded) {
                    loadNotifications();
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
                'elektronik': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                'fashion pria': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'fashion wanita': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'hp & tablet': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                'ibu & bayi': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
                'kecantikan': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>',
                'mainan & anak': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'makanan & minuman': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                'olahraga': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
                'rumah tangga': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
            };
            const getCatIcon = (name) => catIcons[name.toLowerCase()] ||
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';

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
                       class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm transition-all group
                              ${isActive ? 'bg-sky-500 text-white font-semibold shadow-sm shadow-sky-200' : 'text-slate-600 hover:bg-white hover:text-slate-800 hover:shadow-sm'}">
                        <span class="${isActive ? 'text-sky-100' : 'text-slate-400 group-hover:text-sky-500 transition-colors'}">${getCatIcon(cat.name)}</span>
                        <span class="flex-1 leading-tight">${cat.name}</span>
                        ${isActive ? '<svg class="w-3 h-3 text-sky-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>' : ''}
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
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">${found.name}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">${allItems.length} sub-kategori tersedia</p>
                        </div>
                        <a href="${found.url}"
                           class="flex items-center gap-1.5 text-xs font-semibold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-lg transition-colors">
                            Lihat Semua
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="h-px bg-gradient-to-r from-sky-100 via-slate-100 to-transparent mb-5"></div>
                    <div class="grid grid-cols-4 gap-x-6 gap-y-1">
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
                                                    <svg class="w-3 h-3 text-sky-300 opacity-0 group-hover:opacity-100 flex-shrink-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
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

        document.addEventListener('click', (e) => {
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
