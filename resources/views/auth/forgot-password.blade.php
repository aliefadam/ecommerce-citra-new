<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        @include('partials.pwa-meta')
        <title>{{ $appStoreName ?? config('app.name') }} - {{ __('storefront.forgot_password_title') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="h-screen overflow-hidden bg-slate-50 text-slate-800">
        <x-locale-switcher class="fixed right-4 top-4 z-20" />
        <div class="h-full flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-2xl font-bold text-slate-800">{{ __('storefront.forgot_password_title') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('storefront.forgot_password_instruction') }}</p>

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
                        {{ __('storefront.send_reset_link') }}
                    </button>
                </form>

                <p class="text-sm text-slate-600 mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">{{ __('storefront.back_to_sign_in') }}</a>
                </p>
            </div>
        </div>
    </body>

</html>
