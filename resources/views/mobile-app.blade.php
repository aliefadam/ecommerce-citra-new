<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
        <meta name="theme-color" content="#0a3268" />
        <meta name="robots" content="noindex, follow" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="application-name" content="{{ config('app.name') }} Mobile" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
        <link rel="canonical" href="{{ route('frontend.index') }}" />
        <link rel="manifest" href="{{ route('mobile-app.manifest') }}?v={{ urlencode($mobileAppVersion) }}" />
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/boq-apple-touch-icon-v2.png') }}" />
        <title>{{ config('app.name') }} Mobile</title>
        @vite('resources/js/mobile-app/main.js')
    </head>
    <body>
        <div
            id="mobile-app"
            data-app-name="{{ config('app.name') }}"
            data-app-version="{{ $mobileAppVersion }}"
            data-api-base="/api/mobile/v1"
        >
            <div class="mobile-app-boot" role="status">Menyiapkan aplikasi…</div>
        </div>
        <noscript>Aplikasi mobile membutuhkan JavaScript. Gunakan versi web jika JavaScript dinonaktifkan.</noscript>
    </body>
</html>
