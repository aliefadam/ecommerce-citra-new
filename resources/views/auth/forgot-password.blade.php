<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $appStoreName ?? 'Ecommerce Citra' }} - Lupa Password</title>
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
                <h2 class="text-2xl font-bold text-slate-800">Lupa Password</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan email akun kamu untuk menerima link reset password.</p>

                @if ($errors->any())
                    <div class="mt-4 p-4 rounded-xl border border-red-200 bg-red-50">
                        <p class="text-sm font-semibold text-red-700">{{ $errors->first() }}</p>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mt-4 p-4 rounded-xl border border-green-200 bg-green-50">
                        <p class="text-sm font-semibold text-green-700">{{ session('status') }}</p>
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-400" />
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-blue-200">
                        Kirim Link Reset
                    </button>
                </form>

                <p class="text-sm text-slate-600 mt-4 text-center">
                    Kembali ke
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Login</a>
                </p>
            </div>
        </div>
    </body>

</html>
