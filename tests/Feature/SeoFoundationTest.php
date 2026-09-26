<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_file_points_crawlers_to_the_dynamic_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin/', false)
            ->assertSee('Sitemap: '.route('seo.sitemap'), false);
    }

    public function test_sitemap_contains_public_routes_and_published_content(): void
    {
        $post = ContentPage::query()->create([
            'type' => ContentPage::TYPE_POST,
            'title' => 'Panduan Memilih Baut',
            'slug' => 'panduan-memilih-baut',
            'content' => 'Isi panduan.',
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        ContentPage::query()->create([
            'type' => ContentPage::TYPE_POST,
            'title' => 'Artikel Belum Terbit',
            'slug' => 'artikel-belum-terbit',
            'content' => 'Belum boleh diindeks.',
            'is_active' => true,
            'published_at' => now()->addDay(),
        ]);

        ContentPage::query()->create([
            'type' => ContentPage::TYPE_PAGE,
            'title' => 'Tentang Kami',
            'slug' => 'tentang-kami',
            'content' => 'Profil perusahaan.',
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('frontend.index'), false)
            ->assertSee(route('frontend.blog.show', $post->slug), false)
            ->assertDontSee('artikel-belum-terbit', false)
            ->assertDontSee('tentang-kami', false);
    }

    public function test_public_layout_outputs_indexable_metadata(): void
    {
        $response = $this->get('/blog');

        $response->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical" href="'.route('frontend.blog.index').'"', false)
            ->assertSee('content="index, follow, max-image-preview:large"', false)
            ->assertSee('<meta property="og:title"', false);
    }

    public function test_search_results_are_not_indexed(): void
    {
        $this->get('/pencarian?q=baut')
            ->assertOk()
            ->assertSee('content="noindex, follow"', false);
    }
}
