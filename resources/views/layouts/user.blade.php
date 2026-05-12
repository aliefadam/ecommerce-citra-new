<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title', 'TokoKu')</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons/css/all/all.css" rel="stylesheet" />
        <style>
            * {
                font-family: 'Poppins', sans-serif;
            }
        </style>
        @yield('style')
    </head>

    <body class="@yield('body_class', 'bg-slate-50 text-slate-800 pb-20 md:pb-0')">
        <div class="min-h-screen">
            @yield('content')
        </div>
        @include('partials.footer-user')

        <!-- Mobile Bottom Bar (Floating Pill) -->
        @php
            $mobileCartCount = auth()->check() ? (int) auth()->user()->carts()->sum('quantity') : 0;
            $navIsHome     = request()->routeIs('frontend.index');
            $navIsKategori = request()->routeIs('frontend.kategori');
            $navIsPromo    = request()->routeIs('frontend.flash-sale');
            $navIsCart     = request()->routeIs('frontend.cart');
            $navIsAkun     = request()->routeIs('frontend.profil');
        @endphp
        <nav class="md:hidden fixed bottom-4 inset-x-3 z-50">
            <div class="bg-white rounded-full shadow-xl shadow-slate-300/50 border border-slate-100 flex items-center justify-around px-3 py-2">

                {{-- Home --}}
                <a href="{{ route('frontend.index') }}"
                    class="flex items-center justify-center gap-1.5 h-10 rounded-full transition-all duration-300
                           {{ $navIsHome ? 'bg-slate-800 text-white px-4' : 'text-slate-400 w-10 hover:text-slate-600' }}">
                    <i class="fi fi-rr-home text-[1.1rem] leading-none flex-shrink-0"></i>
                    @if($navIsHome)<span class="text-[11px] font-semibold whitespace-nowrap">Home</span>@endif
                </a>

                {{-- Kategori --}}
                <a href="{{ route('frontend.kategori') }}"
                    class="flex items-center justify-center gap-1.5 h-10 rounded-full transition-all duration-300
                           {{ $navIsKategori ? 'bg-slate-800 text-white px-4' : 'text-slate-400 w-10 hover:text-slate-600' }}">
                    <i class="fi fi-rr-apps text-[1.1rem] leading-none flex-shrink-0"></i>
                    @if($navIsKategori)<span class="text-[11px] font-semibold whitespace-nowrap">Kategori</span>@endif
                </a>

                {{-- Promo --}}
                <a href="{{ route('frontend.flash-sale') }}"
                    class="flex items-center justify-center gap-1.5 h-10 rounded-full transition-all duration-300
                           {{ $navIsPromo ? 'bg-slate-800 text-white px-4' : 'text-slate-400 w-10 hover:text-slate-600' }}">
                    <i class="fi fi-rr-bolt text-[1.1rem] leading-none flex-shrink-0"></i>
                    @if($navIsPromo)<span class="text-[11px] font-semibold whitespace-nowrap">Promo</span>@endif
                </a>

                {{-- Keranjang --}}
                <a href="{{ route('frontend.cart') }}"
                    class="flex items-center justify-center gap-1.5 h-10 rounded-full transition-all duration-300 relative
                           {{ $navIsCart ? 'bg-slate-800 text-white px-4' : 'text-slate-400 w-10 hover:text-slate-600' }}">
                    <span class="relative flex-shrink-0">
                        <i class="fi fi-rr-shopping-cart text-[1.1rem] leading-none"></i>
                        <span id="mobileCartBadge"
                            class="absolute -top-1.5 -right-1.5 min-w-[14px] h-3.5 rounded-full bg-blue-500 text-white text-[9px] font-bold items-center justify-center px-0.5 {{ $mobileCartCount > 0 ? 'flex' : 'hidden' }}">{{ $mobileCartCount }}</span>
                    </span>
                    @if($navIsCart)<span class="text-[11px] font-semibold whitespace-nowrap">Keranjang</span>@endif
                </a>

                {{-- Akun --}}
                <a href="{{ route('frontend.profil') }}"
                    class="flex items-center justify-center gap-1.5 h-10 rounded-full transition-all duration-300
                           {{ $navIsAkun ? 'bg-slate-800 text-white px-4' : 'text-slate-400 w-10 hover:text-slate-600' }}">
                    <i class="fi fi-rr-user text-[1.1rem] leading-none flex-shrink-0"></i>
                    @if($navIsAkun)<span class="text-[11px] font-semibold whitespace-nowrap">Akun</span>@endif
                </a>

            </div>
        </nav>

        <!-- WhatsApp Floating Button -->
        @if (!empty($appStoreSettings['social_whatsapp']))
            <a href="{{ $appStoreSettings['social_whatsapp'] }}" target="_blank" rel="noopener noreferrer"
                class="hidden md:flex fixed bottom-24 right-4 md:bottom-8 md:right-6 z-40 items-center justify-center w-14 h-14 bg-green-500 hover:bg-green-600 active:scale-95 text-white rounded-full shadow-lg shadow-green-500/40 transition-all duration-200"
                aria-label="Chat via WhatsApp">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </a>
        @endif

        @yield('script')
    </body>

</html>
