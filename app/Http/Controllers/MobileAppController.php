<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RedirectMobileToSpa;
use App\Models\Banner;
use App\Support\CatalogImageUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class MobileAppController extends Controller
{
    public function show(Request $request): Response
    {
        // Membuka /app berarti memilih versi aplikasi: hapus preferensi "versi web".
        if ($request->cookie(RedirectMobileToSpa::PREFER_WEB_COOKIE) !== null) {
            Cookie::queue(Cookie::forget(RedirectMobileToSpa::PREFER_WEB_COOKIE));
        }

        return response()->view('mobile-app', [
            'mobileAppVersion' => (string) config('mobile-spa.version'),
            'mobilePreloadImage' => $this->heroPreloadImage(),
        ]);
    }

    private function heroPreloadImage(): string
    {
        $image = Banner::query()
            ->where('is_active', true)
            ->where('type', 'mobile_hero')
            ->where(fn ($query) => $query->whereNull('company_id')->orWhereHas('company', fn ($company) => $company->where('is_active', true)))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->value('image');

        return $image ? CatalogImageUrl::make($image) : asset('imgs/mobile-hero/hero-1.svg');
    }

    public function manifest(): JsonResponse
    {
        $name = (string) config('app.name');

        return response()->json([
            'id' => '/app',
            'name' => $name.' Mobile',
            'short_name' => $name,
            'description' => 'Storefront mobile untuk kebutuhan proyek dan teknik.',
            'lang' => 'id-ID',
            'dir' => 'ltr',
            'start_url' => '/app',
            'scope' => '/app/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#faf9f6',
            'theme_color' => '#0a3268',
            'icons' => [
                [
                    'src' => asset('pwa/boq-icon-192-v2.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/boq-icon-512-v2.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/boq-icon-maskable-512-v2.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }
}
