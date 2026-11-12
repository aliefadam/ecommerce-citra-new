<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MobileHelpController extends Controller
{
    /**
     * FAQ untuk aplikasi mobile. Sumber datanya sama dengan halaman Pusat Bantuan
     * website (lang/{locale}/help.php) agar isi keduanya selalu sinkron.
     */
    public function faqs(): JsonResponse
    {
        $help = trans('help');

        $categories = collect($help['categories'] ?? [])
            ->map(fn ($label, $key) => ['key' => (string) $key, 'label' => (string) $label])
            ->values();

        $faqs = collect($help['faqs'] ?? [])
            ->values()
            ->map(fn (array $faq, int $index) => [
                'id' => (string) ($faq['id'] ?? 'faq-'.$index),
                'category' => (string) $faq['category'],
                'question' => (string) $faq['question'],
                'answer' => (string) $faq['answer'],
                'platforms' => collect($faq['platforms'] ?? [])
                    ->map(fn ($steps, $platform) => ['name' => (string) $platform, 'steps' => array_values((array) $steps)])
                    ->values(),
            ]);

        return response()->json([
            'data' => [
                'title' => (string) ($help['faq_title'] ?? 'FAQ'),
                'intro' => (string) ($help['faq_intro'] ?? ''),
                'categories' => $categories,
                'faqs' => $faqs,
                'help_center_url' => route('frontend.pages.show', ['slug' => 'pusat-bantuan']),
            ],
        ]);
    }
}
