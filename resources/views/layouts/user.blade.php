<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title', 'TokoKu')</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet" />
        <style>
            * {
                font-family: 'Poppins', sans-serif;
            }
        </style>
        @yield('style')
    </head>

    <body class="@yield('body_class', 'bg-slate-50 text-slate-800 pb-20 md:pb-0')">
        @yield('content')

        <!-- Mobile Bottom Bar -->
        <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-slate-200">
            <div class="grid grid-cols-5">
                <a href="{{ route('frontend.index') }}"
                    class="py-2 flex flex-col items-center justify-center gap-1 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[11px] font-medium">Home</span>
                </a>
                <a href="{{ route('frontend.kategori') }}"
                    class="py-2 flex flex-col items-center justify-center gap-1 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="text-[11px] font-medium">Kategori</span>
                </a>
                <a href="{{ route('frontend.flash-sale') }}"
                    class="py-2 flex flex-col items-center justify-center gap-1 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-[11px] font-medium">Promo</span>
                </a>
                <a href="{{ route('frontend.checkout') }}"
                    class="py-2 flex flex-col items-center justify-center gap-1 text-slate-500 relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="absolute top-1.5 right-6 w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] flex items-center justify-center">3</span>
                    <span class="text-[11px] font-medium">Keranjang</span>
                </a>
                <a href="{{ route('frontend.profil') }}"
                    class="py-2 flex flex-col items-center justify-center gap-1 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A9.969 9.969 0 0112 15c2.5 0 4.787.918 6.879 2.435M15 11a3 3 0 11-6 0 3 3 0 016 0zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[11px] font-medium">Akun</span>
                </a>
            </div>
        </nav>

        @yield('script')
    </body>

</html>
