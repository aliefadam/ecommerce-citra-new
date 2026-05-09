<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <script>
            (function() {
                const html = document.documentElement;
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) html.classList.add('dark');
            })();
        </script>
        <title>{{ $appStoreName ?? 'Ecommerce Citra' }} - Login</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet" />
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            jakarta: ['Plus Jakarta Sans', 'sans-serif']
                        }
                    }
                }
            }
        </script>
        <style>
            * {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>
    </head>

    <body class="h-screen overflow-hidden bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
        <div class="min-h-screen grid lg:grid-cols-2">
            <section class="hidden lg:flex flex-col justify-between text-white relative overflow-hidden"
                style="background: url('https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=900&q=80') center center / cover no-repeat;">

                <!-- Dark overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-black/30"></div>

                <!-- Logo -->
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
                    <span class="text-xl font-extrabold tracking-tight">{{ $appStoreName ?? 'Ecommerce Citra' }}</span>
                </div>

                <!-- Bottom text + dots -->
                <div class="relative z-10 p-10">
                    <h1 class="text-3xl font-bold leading-tight">Temukan produk <br>terbaik untuk Anda</h1>
                    <p class="mt-2 text-sm text-white/70">Belanja mudah, cepat, dan terpercaya — <br>ribuan produk siap
                        dikirim ke seluruh Indonesia.</p>
                    <!-- Slider dots -->
                    <div class="flex items-center gap-2 mt-6">
                        <div class="w-7 h-2 rounded-full bg-white"></div>
                        <div class="w-2 h-2 rounded-full bg-white/40"></div>
                        <div class="w-2 h-2 rounded-full bg-white/40"></div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center p-6 sm:p-10">
                <div class="w-full max-w-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Sign In</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Masukkan akun Anda untuk
                                melanjutkan.</p>
                        </div>
                        <div
                            class="mb-4 p-4 rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-900/50 flex flex-col justify-end items-end">
                            <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-2">Demo Admin</p>

                            <button type="button" onclick="fillDemo()"
                                class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-0.5 rounded font-semibold transition-colors">
                                Isi Otomatis
                            </button>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div
                            class="mb-4 p-4 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/50">
                            <p class="text-sm font-semibold text-red-700 dark:text-red-300">{{ $errors->first() }}</p>
                        </div>
                    @endif
                    @if (session('status'))
                        <div
                            class="mb-4 p-4 rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-900/50">
                            <p class="text-sm font-semibold text-green-700 dark:text-green-300">{{ session('status') }}
                            </p>
                        </div>
                    @endif
                    <div
                        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">

                        <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    placeholder="you@example.com"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                                <input type="password" name="password" placeholder="••••••••"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                            </div>
                            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input type="checkbox" name="remember"
                                    class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
                                Remember me
                            </label>
                            <div class="text-right -mt-2">
                                <a href="{{ route('password.request') }}"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-semibold">Lupa password?</a>
                            </div>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/40">
                                Sign In
                            </button>
                        </form>

                        <div class="flex items-center gap-3 my-4">
                            <div class="flex-1 h-px bg-slate-200 dark:bg-slate-600"></div>
                            <span class="text-xs font-medium text-slate-400 dark:text-slate-500 whitespace-nowrap">atau
                                masuk dengan</span>
                            <div class="flex-1 h-px bg-slate-200 dark:bg-slate-600"></div>
                        </div>

                        <a href="{{ route('auth.google.redirect') }}"
                            class="w-full inline-flex items-center justify-center gap-2 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M21.805 10.023h-9.56v3.957h5.478c-.236 1.272-.952 2.35-2.025 3.072v2.55h3.283c1.922-1.77 3.024-4.378 3.024-7.463 0-.704-.063-1.38-.2-2.116z"
                                    fill="#4285F4" />
                                <path
                                    d="M12.245 22c2.735 0 5.029-.907 6.706-2.448l-3.283-2.55c-.906.61-2.066.972-3.423.972-2.64 0-4.88-1.782-5.68-4.185H3.174v2.625A10.13 10.13 0 0012.245 22z"
                                    fill="#34A853" />
                                <path
                                    d="M6.565 13.79a6.082 6.082 0 010-3.58V7.586H3.174a10.13 10.13 0 000 9.829l3.391-2.625z"
                                    fill="#FBBC05" />
                                <path
                                    d="M12.245 6.026c1.486 0 2.825.51 3.874 1.511l2.907-2.907C17.27 2.999 14.976 2 12.245 2A10.13 10.13 0 003.174 7.586l3.391 2.625c.8-2.404 3.04-4.185 5.68-4.185z"
                                    fill="#EA4335" />
                            </svg>
                            Login dengan Google
                        </a>

                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-4 text-center">
                            Belum punya akun?
                            <a href="{{ route('register') }}"
                                class="text-blue-600 hover:text-blue-700 font-semibold">Register</a>
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <script>
            function toggleDark() {
                const html = document.documentElement;
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            }

            function fillDemo() {
                document.querySelector('input[name="email"]').value = 'admin@citra.com';
                document.querySelector('input[name="password"]').value = '123123';
            }
        </script>
    </body>

</html>
