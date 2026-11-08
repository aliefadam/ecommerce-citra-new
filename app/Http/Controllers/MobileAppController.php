<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MobileAppController extends Controller
{
    public function show(): View
    {
        return view('mobile-app', [
            'mobileAppVersion' => (string) config('mobile-spa.version'),
        ]);
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
