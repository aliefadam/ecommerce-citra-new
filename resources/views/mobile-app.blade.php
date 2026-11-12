<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
        <meta name="theme-color" content="#0a3268" />
        <meta name="robots" content="noindex, follow" />
        <meta name="description" content="Katalog mobile {{ config('app.name') }} untuk mencari produk, kategori, varian, dan spesifikasi kebutuhan proyek." />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="application-name" content="{{ config('app.name') }} Mobile" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
        <link rel="canonical" href="{{ route('frontend.index') }}" />
        <link rel="manifest" href="{{ route('mobile-app.manifest') }}?v={{ urlencode($mobileAppVersion) }}" />
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/boq-apple-touch-icon-v2.png') }}" />
        @if ($mobilePreloadImage)
            <link rel="preload" as="image" href="{{ $mobilePreloadImage }}" fetchpriority="high" />
        @endif
        <title>{{ config('app.name') }} Mobile</title>
        @vite('resources/js/mobile-app/main.js')
    </head>
    <body>
        <div
            id="mobile-app"
            data-app-name="{{ config('app.name') }}"
            data-logo-url="{{ !empty($appStoreLogoUrl) ? $appStoreLogoUrl : asset('logo/BOQ.CO.ID/BOQ.CO.ID-1.png') }}"
            data-boqi-mascot-url="{{ asset('imgs/boqi/boqi-mascot.webp') }}"
            data-support-whatsapp="{{ trim((string) ($appStoreSettings['social_whatsapp'] ?? '')) }}"
            data-app-version="{{ $mobileAppVersion }}"
            data-api-base="/api/mobile/v1"
            data-pilot="{{ config('mobile-spa.pilot') ? 'true' : 'false' }}"
            data-analytics-enabled="{{ config('mobile-spa.analytics_enabled') ? 'true' : 'false' }}"
        >
            <div class="mobile-app-boot" role="status">Menyiapkan aplikasi…</div>
        </div>
        <noscript>Aplikasi mobile membutuhkan JavaScript. Gunakan versi web jika JavaScript dinonaktifkan.</noscript>
    </body>
</html>
