<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ $status }} — {{ $title }}</title>
        <style>
            :root {
                color-scheme: light;
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                --graphite-950: #111820;
                --graphite-900: #18222d;
                --steel-700: #445466;
                --steel-300: #b7c1ca;
                --steel-200: #d5dce2;
                --warm-50: #faf9f6;
                --primary-900: #0a3268;
                --primary-700: #174d91;
                --primary-100: #dceafe;
                --secondary-600: #cf4f0b;
                --secondary-500: #ed6718;
            }

            * { box-sizing: border-box; }
            html, body { min-width: 320px; min-height: 100%; margin: 0; }
            body { background: var(--warm-50); color: var(--graphite-900); }
            a:focus-visible { outline: 3px solid #1d5db8; outline-offset: 3px; }

            .error-page {
                position: relative;
                display: grid;
                min-height: 100vh;
                min-height: 100dvh;
                place-items: center;
                overflow: hidden;
                padding: 1.5rem;
            }

            .error-page::before,
            .error-page::after {
                position: absolute;
                border-radius: 999px;
                content: "";
                pointer-events: none;
            }

            .error-page::before {
                top: -9rem;
                right: -8rem;
                width: 24rem;
                height: 24rem;
                background: #dceafe;
                opacity: .72;
            }

            .error-page::after {
                bottom: -10rem;
                left: -8rem;
                width: 22rem;
                height: 22rem;
                background: #ffeadb;
                opacity: .65;
            }

            .error-card {
                position: relative;
                z-index: 1;
                width: min(100%, 52rem);
                overflow: hidden;
                border: 1px solid var(--steel-200);
                border-radius: 1rem;
                background: #fff;
                box-shadow: 0 18px 50px rgb(17 24 32 / .12);
            }

            .error-card__bar {
                height: .3rem;
                background: linear-gradient(90deg, var(--secondary-500) 0 18%, var(--primary-700) 18% 100%);
            }

            .error-card__body {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: center;
                gap: 3rem;
                padding: clamp(2rem, 6vw, 4.5rem);
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: .7rem;
                color: var(--primary-900);
                font-size: .8rem;
                font-weight: 800;
                letter-spacing: .08em;
                text-decoration: none;
                text-transform: uppercase;
            }


            .error-kicker {
                margin: 2rem 0 .65rem;
                color: var(--secondary-600);
                font-size: .75rem;
                font-weight: 800;
                letter-spacing: .14em;
                text-transform: uppercase;
            }

            h1 {
                max-width: 34rem;
                margin: 0;
                color: var(--graphite-950);
                font-size: clamp(2rem, 5vw, 3.25rem);
                letter-spacing: -.035em;
                line-height: 1.05;
            }

            .error-description {
                max-width: 36rem;
                margin: 1rem 0 0;
                color: var(--steel-700);
                font-size: 1rem;
                line-height: 1.75;
            }

            .error-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.75rem; }
            .button {
                display: inline-flex;
                min-height: 44px;
                align-items: center;
                justify-content: center;
                border: 1px solid transparent;
                border-radius: .5rem;
                padding: .7rem 1rem;
                font-size: .875rem;
                font-weight: 750;
                text-decoration: none;
                transition: transform 160ms, background-color 160ms, border-color 160ms;
            }

            .button:hover { transform: translateY(-1px); }
            .button--primary { background: var(--secondary-500); color: #fff; box-shadow: inset 0 -2px 0 rgb(0 0 0 / .14); }
            .button--primary:hover { background: var(--secondary-600); }
            .button--outline { border-color: var(--steel-300); color: var(--graphite-900); }
            .button--outline:hover { border-color: var(--primary-700); color: var(--primary-700); }

            .error-code {
                color: var(--primary-100);
                font-size: clamp(6rem, 18vw, 10rem);
                font-weight: 900;
                letter-spacing: -.09em;
                line-height: .8;
                text-shadow: 1px 1px 0 #c5dcfb;
                user-select: none;
            }

            @media (max-width: 700px) {
                .error-page { padding: 1rem; }
                .error-card__body { grid-template-columns: 1fr; gap: 2rem; padding: 2rem 1.5rem; }
                .error-code { grid-row: 1; font-size: 5.5rem; }
                .error-kicker { margin-top: 1.5rem; }
                .error-actions { flex-direction: column; }
                .button { width: 100%; }
            }

            @media (prefers-reduced-motion: reduce) {
                .button { transition: none; }
            }
        </style>
    </head>
    <body>
        <main class="error-page">
            <section class="error-card" aria-labelledby="error-title">
                <div class="error-card__bar" aria-hidden="true"></div>
                <div class="error-card__body">
                    <div>
                        <a class="brand" href="{{ url('/') }}" aria-label="{{ config('app.name') }}, beranda">
                            <span>{{ config('app.name') }}</span>
                        </a>
                        <p class="error-kicker">Kode error {{ $status }}</p>
                        <h1 id="error-title">{{ $title }}</h1>
                        <p class="error-description">{{ $description }}</p>
                        <div class="error-actions">
                            <a class="button button--primary" href="{{ url('/') }}">Kembali ke Beranda</a>
                            @if ($showRetry ?? false)
                                <a class="button button--outline" href="{{ request()->fullUrl() }}">Coba Lagi</a>
                            @endif
                        </div>
                    </div>
                    <div class="error-code" aria-hidden="true">{{ $status }}</div>
                </div>
            </section>
        </main>
    </body>
</html>
