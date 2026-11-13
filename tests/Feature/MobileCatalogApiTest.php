<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mobile-spa.enabled' => true]);
        $this->seed();
    }

    public function test_home_and_categories_expose_only_storefront_visible_catalog_data(): void
    {
        $hiddenProduct = Product::query()->storefrontVisible()->whereHas('productVariants')->firstOrFail();
        $hiddenProduct->update(['status' => 'inactive']);

        $response = $this->getJson('/api/mobile/v1/home')
            ->assertOk()
            ->assertHeader('Cache-Control')
            ->assertJsonStructure([
                'data' => [
                    'hero_banners',
                    'banners',
                    'categories' => [['id', 'name', 'slug', 'products_count', 'children']],
                    'featured_products',
                ],
            ]);

        $slugs = collect($response->json('data.featured_products'))->pluck('slug');
        $this->assertFalse($slugs->contains($hiddenProduct->slug));

        $this->getJson('/api/mobile/v1/categories')
            ->assertOk()
            ->assertJsonStructure(['data' => [['name', 'slug', 'products_count', 'children']]]);
    }

    public function test_product_listing_supports_search_category_stock_sort_and_bounded_pagination(): void
    {
        $product = Product::query()
            ->storefrontVisible()
            ->whereHas('productVariants', fn ($query) => $query->where('stock', '>', 0))
            ->with(['mainCategory', 'productVariants'])
            ->firstOrFail();
        $sku = (string) $product->productVariants->first()->sku;

        $this->getJson('/api/mobile/v1/products?'.http_build_query([
            'q' => $sku,
            'category' => $product->mainCategory->slug,
            'in_stock' => 1,
            'sort' => 'price_asc',
            'per_page' => 6,
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.slug', $product->slug)
            ->assertJsonPath('data.0.in_stock', true)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'image', 'price_min', 'price_max', 'seller', 'category']],
                'links',
                'meta',
            ])
            ->assertJsonMissingPath('data.0.company_id')
            ->assertJsonMissingPath('data.0.stock');

        $this->getJson('/api/mobile/v1/products?per_page=25')
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonStructure(['message', 'errors', 'request_id']);
        $this->getJson('/api/mobile/v1/products?sort=unsafe')->assertUnprocessable();
    }

    public function test_product_detail_whitelists_variants_and_rejects_hidden_products(): void
    {
        $product = Product::query()->storefrontVisible()->whereHas('productVariants')->firstOrFail();

        $this->getJson('/api/mobile/v1/products/'.$product->slug)
            ->assertOk()
            ->assertJsonPath('data.slug', $product->slug)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'slug', 'description', 'images', 'seller', 'category',
                    'specifications', 'variants' => [['id', 'sku', 'price', 'in_stock', 'attributes']],
                ],
            ])
            ->assertJsonMissingPath('data.company_id')
            ->assertJsonMissingPath('data.variants.0.stock');

        $product->update(['status' => 'inactive']);

        $this->getJson('/api/mobile/v1/products/'.$product->slug)->assertNotFound();

        $product->update(['status' => 'active']);
        $product->company()->update(['is_active' => false]);

        $this->getJson('/api/mobile/v1/products/'.$product->slug)->assertNotFound();
    }

    public function test_related_products_exclude_current_and_hidden_products_and_prioritize_same_category(): void
    {
        $product = Product::query()->storefrontVisible()->whereHas('productVariants')->whereNotNull('main_category_id')->firstOrFail();
        $sibling = Product::query()->storefrontVisible()->whereHas('productVariants')->whereKeyNot($product->id)->firstOrFail();
        $sibling->update([
            'main_category_id' => $product->main_category_id,
            'category_detail_id' => $product->category_detail_id,
        ]);
        $hidden = Product::query()->storefrontVisible()->whereHas('productVariants')->whereKeyNot([$product->id, $sibling->id])->first();
        $hidden?->update([
            'status' => 'inactive',
            'main_category_id' => $product->main_category_id,
            'category_detail_id' => $product->category_detail_id,
        ]);

        $response = $this->getJson('/api/mobile/v1/products/'.$product->slug.'/related')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'image', 'price_min', 'price_max', 'seller', 'category']]])
            ->assertJsonMissingPath('data.0.company_id')
            ->assertJsonMissingPath('data.0.stock');

        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertLessThanOrEqual(8, $slugs->count());
        $this->assertSame($slugs->unique()->count(), $slugs->count());
        $this->assertFalse($slugs->contains($product->slug));
        $this->assertTrue($slugs->contains($sibling->slug));
        if ($hidden) {
            $this->assertFalse($slugs->contains($hidden->slug));
        }

        $product->update(['status' => 'inactive']);
        $this->getJson('/api/mobile/v1/products/'.$product->slug.'/related')->assertNotFound();
    }

    public function test_suggestions_require_two_characters_and_match_sku_without_exposing_stock_count(): void
    {
        $product = Product::query()->storefrontVisible()->whereHas('productVariants')->with('productVariants')->firstOrFail();
        $sku = (string) $product->productVariants->first()->sku;

        $this->getJson('/api/mobile/v1/search/suggestions?q=a')->assertUnprocessable();

        $this->getJson('/api/mobile/v1/search/suggestions?q='.urlencode($sku))
            ->assertOk()
            ->assertJsonPath('data.0.slug', $product->slug)
            ->assertJsonMissingPath('data.0.stock');
    }

    public function test_home_exposes_mobile_hero_banners_separately_from_desktop_carousel(): void
    {
        \App\Models\Banner::query()->create(['type' => 'carousel', 'image' => 'banners/desktop.webp', 'sort_order' => 1, 'is_active' => true]);
        \App\Models\Banner::query()->create(['type' => 'mobile_hero', 'title' => 'Hero', 'header_tone' => 'dark', 'image' => 'banners/hero.webp', 'target_url' => '/app/categories', 'sort_order' => 1, 'is_active' => true]);
        \App\Models\Banner::query()->create(['type' => 'mobile_hero', 'image' => 'banners/off.webp', 'sort_order' => 2, 'is_active' => false]);

        $response = $this->getJson('/api/mobile/v1/home')->assertOk();

        $heroes = $response->json('data.hero_banners');
        $this->assertCount(1, $heroes);
        $this->assertSame('Hero', $heroes[0]['title']);
        $this->assertSame('/app/categories', $heroes[0]['target_url']);
        $this->assertSame('dark', $heroes[0]['header_tone']);
        $this->assertStringContainsString('banners/hero.webp', $heroes[0]['image']);
        $this->assertFalse(collect($response->json('data.banners'))->pluck('image')->contains(fn ($image) => str_contains($image, 'hero.webp')));
    }
}
