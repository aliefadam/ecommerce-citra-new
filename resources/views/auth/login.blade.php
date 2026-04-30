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
        <title>AdminKit - Login</title>
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

    <body class="min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
        <div class="min-h-screen grid lg:grid-cols-2">
            <section
                class="hidden lg:flex flex-col justify-between p-10 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 text-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight">Admin<span
                            class="text-blue-200">Kit</span></span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold leading-tight">Welcome back</h1>
                    <p class="mt-3 text-blue-100">Sign in untuk mengakses dashboard, datatables, dan product management.
                    </p>
                </div>
                <p class="text-sm text-blue-200">Template Admin Panel</p>
            </section>

            <section class="flex items-center justify-center p-6 sm:p-10">
                <div class="w-full max-w-md">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Sign In</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Masukkan akun Anda untuk
                                melanjutkan.</p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div
                            class="mb-4 p-4 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/50">
                            <p class="text-sm font-semibold text-red-700 dark:text-red-300">{{ $errors->first() }}</p>
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

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/40">
                                Sign In
                            </button>
                        </form>
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
        </script>
    </body>

</html>
