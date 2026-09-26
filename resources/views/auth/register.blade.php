<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        @include('partials.pwa-meta')
        <script>
            (function() {
                const html = document.documentElement;
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) html.classList.add('dark');
            })();
        </script>
        <title>{{ $appStoreName ?? config('app.name') }} - Register</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="h-screen overflow-hidden bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
        <div class="min-h-screen grid lg:grid-cols-2">
            <section class="hidden lg:flex flex-col justify-between text-white relative overflow-hidden"
                style="background: url('{{ asset('imgs/auth/fastener-workshop-auth.png') }}') center center / cover no-repeat;">

                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-black/30"></div>

                <div class="relative flex items-center gap-3 z-10 p-10">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center overflow-hidden">
                        @if (!empty($appStoreLogoUrl))
                            <img src="{{ $appStoreLogoUrl }}" alt="{{ $appStoreName }}" class="w-full h-full object-contain bg-white p-1">
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                            </svg>
                        @endif
                    </div>
                    <span class="text-xl font-extrabold tracking-tight">{{ $appStoreName ?? config('app.name') }}</span>
                </div>

                <div class="relative z-10 p-10">
                    <h1 class="text-3xl font-bold leading-tight">Kebutuhan fastener <br>untuk proyek Anda</h1>
                    <p class="mt-2 text-sm text-white/70">Baut, mur, ring, anchor, dan perkakas teknik siap dikirim
                        ke seluruh Indonesia.</p>
                    <div class="flex items-center gap-2 mt-6">
                        <div class="w-7 h-2 rounded-full bg-white"></div>
                        <div class="w-2 h-2 rounded-full bg-white/40"></div>
                        <div class="w-2 h-2 rounded-full bg-white/40"></div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center p-6 sm:p-10">
                <div class="w-full max-w-md">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $checkoutTransaction ? 'Simpan Pesananmu' : 'Register' }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $checkoutTransaction ? 'Buat akun dan lanjutkan dari pesanan terakhir.' : 'Masukkan data akun Anda.' }}</p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div
                            class="mb-4 p-4 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/50">
                            <p class="text-sm font-semibold text-red-700 dark:text-red-300">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    @if ($checkoutTransaction)
                        <div class="mb-4 overflow-hidden rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/60 dark:bg-blue-950/30">
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 dark:text-blue-400">Pesanan siap dihubungkan</p>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <span class="font-mono text-sm font-bold text-slate-800 dark:text-slate-100">{{ $checkoutTransaction->order_id }}</span>
                                <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-emerald-700 shadow-sm dark:bg-slate-800 dark:text-emerald-400">Terverifikasi</span>
                            </div>
                        </div>
                    @endif

                    <div
                        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                        <form action="{{ route('register.attempt') }}" method="POST" class="space-y-4">
                            @csrf
                            @if ($checkoutTransaction)
                                <input type="hidden" name="checkout_order" value="{{ $checkoutTransaction->order_id }}">
                            @endif
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama</label>
                                <input type="text" name="name" value="{{ old('name', $checkoutTransaction?->manual_customer_name) }}"
                                    placeholder="Nama lengkap"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                                <input type="email" name="email" value="{{ old('email', $checkoutTransaction?->manual_customer_email) }}"
                                    @if ($checkoutTransaction) readonly @endif
                                    placeholder="you@example.com"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                                <input type="password" name="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Konfirmasi
                                    Password</label>
                                <input type="password" name="password_confirmation" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                            </div>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/40">
                                {{ $checkoutTransaction ? 'Buat Akun & Simpan Pesanan' : 'Register' }}
                            </button>
                        </form>

                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-4 text-center">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Sign
                                In</a>
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </body>

</html>
