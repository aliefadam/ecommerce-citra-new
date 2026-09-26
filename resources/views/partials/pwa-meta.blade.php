<meta name="theme-color" content="#0a3268" />
<meta name="application-name" content="{{ config('app.name') }}" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
<meta name="mobile-web-app-capable" content="yes" />
<link rel="manifest" href="{{ route('pwa.manifest') }}?v=4" />
<link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any" />
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('pwa/favicon-32x32.png') }}?v=2" />
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('pwa/favicon-16x16.png') }}?v=2" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/boq-apple-touch-icon-v2.png') }}" />
