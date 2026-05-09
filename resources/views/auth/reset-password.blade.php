<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $appStoreName ?? 'Ecommerce Citra' }} - Reset Password</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet" />
        <style>
            * {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>
    </head>

    <body class="h-screen overflow-hidden bg-slate-50 text-slate-800">
        <div class="h-full flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-2xl font-bold text-slate-800">Reset Password</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan password baru untuk akun kamu.</p>

                @if ($errors->any())
                    <div class="mt-4 p-4 rounded-xl border border-red-200 bg-red-50">
                        <p class="text-sm font-semibold text-red-700">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}" />

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $email) }}"
                            placeholder="you@example.com"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-400" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password Baru</label>
                        <input type="password" name="password" placeholder="••••••••"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-400" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konfirmasi Password
                            Baru</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-400" />
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200">
                        Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>
    </body>

</html>
