<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengarahkan pengunjung ponsel dari storefront Blade ke Mobile SPA (/app).
 *
 * - Hanya aktif bila MOBILE_SPA_ENABLED dan MOBILE_SPA_AUTO_REDIRECT bernilai true.
 * - Hanya GET/HEAD navigasi halaman yang punya padanan di SPA.
 * - Crawler/bot tidak pernah diarahkan (aman untuk SEO).
 * - `?web=1` menyimpan preferensi "versi web"; membuka /app menghapusnya kembali.
 */
class RedirectMobileToSpa
{
    public const PREFER_WEB_COOKIE = 'prefer_web';

    private const BOT_PATTERN = '/bot|crawl|spider|slurp|lighthouse|pagespeed|headless|facebookexternalhit|whatsapp|telegrambot|preview|curl|wget|python|okhttp/i';

    private const MOBILE_PATTERN = '/Mobi|iPhone|iPod|Android.+Mobile|Windows Phone|IEMobile|Opera Mini|BlackBerry/i';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->query('web') === '1') {
            Cookie::queue(Cookie::make(self::PREFER_WEB_COOKIE, '1', 60 * 24 * 30));

            return $next($request);
        }

        if (! $this->shouldRedirect($request)) {
            return $this->varyOnUserAgent($next($request));
        }

        $target = $this->spaPathFor($request);

        if ($target === null) {
            return $this->varyOnUserAgent($next($request));
        }

        return $this->varyOnUserAgent(
            new RedirectResponse(url($target), 302, ['Cache-Control' => 'private, no-store'])
        );
    }

    private function shouldRedirect(Request $request): bool
    {
        if (! config('mobile-spa.enabled') || ! config('mobile-spa.auto_redirect')) {
            return false;
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson() || $request->cookie(self::PREFER_WEB_COOKIE) === '1') {
            return false;
        }

        $userAgent = (string) $request->userAgent();

        return $userAgent !== ''
            && preg_match(self::MOBILE_PATTERN, $userAgent) === 1
            && preg_match(self::BOT_PATTERN, $userAgent) !== 1;
    }

    private function spaPathFor(Request $request): ?string
    {
        $route = $request->route();
        $name = $route?->getName();

        $path = match ($name) {
            'frontend.index' => '/app',
            'frontend.kategori' => $this->categoryPath($request),
            'frontend.search' => '/app/search'.$this->queryString($request, ['q']),
            'frontend.detail-produk' => $route->parameter('slug')
                ? '/app/products/'.rawurlencode((string) $route->parameter('slug'))
                : null,
            'frontend.cart' => '/app/cart',
            'frontend.wishlist.index' => '/app/wishlist',
            'frontend.notifications.index' => '/app/notifications',
            'frontend.profil' => '/app/account',
            'frontend.checkout.orders' => '/app/orders',
            'login' => '/app/auth/login',
            'register' => '/app/auth/register',
            default => null,
        };

        return $path;
    }

    private function categoryPath(Request $request): string
    {
        $slug = (string) ($request->query('category') ?: $request->query('parent', ''));

        return $slug !== '' ? '/app/c/'.rawurlencode($slug) : '/app/categories';
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function queryString(Request $request, array $keys): string
    {
        $query = array_filter($request->only($keys), fn ($value) => is_string($value) && $value !== '');

        return $query === [] ? '' : '?'.http_build_query($query);
    }

    private function varyOnUserAgent(Response $response): Response
    {
        $response->setVary('User-Agent', false);

        return $response;
    }
}
