<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\Product;
use App\Models\PromoPage;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /profil',
            'Disallow: /checkout',
            'Disallow: /cart',
            'Disallow: /notifications',
            'Sitemap: '.route('seo.sitemap'),
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('frontend.index'), 'lastmod' => null, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('frontend.kategori'), 'lastmod' => null, 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('frontend.flash-sale'), 'lastmod' => null, 'changefreq' => 'hourly', 'priority' => '0.8'],
            ['loc' => route('frontend.blog.index'), 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.7'],
        ]);

        Product::query()
            ->storefrontVisible()
            ->whereNotNull('slug')
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->chunkById(500, function ($products) use ($urls) {
                foreach ($products as $product) {
                    $urls->push([
                        'loc' => route('frontend.detail-produk', ['slug' => $product->slug]),
                        'lastmod' => $product->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ]);
                }
            });

        ContentPage::query()
            ->published()
            ->select(['type', 'slug', 'updated_at'])
            ->orderBy('id')
            ->each(function ($page) use ($urls) {
                $routeName = $page->type === ContentPage::TYPE_POST ? 'frontend.blog.show' : 'frontend.pages.show';
                $urls->push([
                    'loc' => route($routeName, ['slug' => $page->slug]),
                    'lastmod' => $page->updated_at?->toAtomString(),
                    'changefreq' => $page->type === ContentPage::TYPE_POST ? 'monthly' : 'yearly',
                    'priority' => $page->type === ContentPage::TYPE_POST ? '0.7' : '0.5',
                ]);
            });

        PromoPage::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->each(fn ($promo) => $urls->push([
                'loc' => route('frontend.promo', ['slug' => $promo->slug]),
                'lastmod' => $promo->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]));

        return response()
            ->view('seo.sitemap', ['urls' => $urls], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
