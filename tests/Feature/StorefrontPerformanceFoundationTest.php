<?php

namespace Tests\Feature;

use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StorefrontNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StorefrontPerformanceFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_layout_uses_versioned_local_assets(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('/build/assets/app-', false)
            ->assertDontSee('cdn.tailwindcss.com', false)
            ->assertDontSee('@tailwindcss/browser', false)
            ->assertDontSee('fonts.googleapis.com', false)
            ->assertDontSee('cdn.jsdelivr.net/npm/@flaticon', false)
            ->assertDontSee('cdn.jsdelivr.net/npm/remixicon', false)
            ->assertDontSee('navbarSearchProducts', false);
    }

    public function test_async_suggestions_search_sku_and_return_compact_product_data(): void
    {
        $this->seed();

        $variant = ProductVariant::query()
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->with('product')
            ->firstOrFail();

        $response = $this->getJson(route('frontend.search.suggestions', ['q' => $variant->sku]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $variant->product->name)
            ->assertJsonPath('data.0.sku', $variant->sku)
            ->assertJsonStructure(['data' => [['name', 'sku', 'variant', 'category', 'company', 'price', 'price_label', 'image', 'url']]]);
    }

    public function test_suggestions_require_at_least_two_characters_and_hide_inactive_products(): void
    {
        $this->seed();

        $this->getJson(route('frontend.search.suggestions', ['q' => 'a']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');

        $product = Product::query()->whereNotNull('slug')->firstOrFail();
        $product->update(['name' => 'Produk Rahasia Sprint Satu', 'status' => 'inactive']);

        $this->getJson(route('frontend.search.suggestions', ['q' => 'Rahasia Sprint']))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_category_navigation_cache_is_invalidated_after_category_changes(): void
    {
        Cache::clear();
        $service = app(StorefrontNavigationService::class);

        $parent = MainCategory::query()->create(['name' => 'Perkakas', 'slug' => 'perkakas']);
        CategoryDetail::query()->create([
            'main_category_id' => $parent->id,
            'name' => 'Kunci',
            'slug' => 'kunci',
        ]);
        $this->assertSame('Kunci', $service->megaCategories()[0]['columns'][0]['items'][0]['name']);

        CategoryDetail::query()->create([
            'main_category_id' => $parent->id,
            'name' => 'Bor',
            'slug' => 'bor',
        ]);

        $items = collect($service->megaCategories()[0]['columns'])->pluck('items')->flatten(1)->pluck('name');
        $this->assertTrue($items->contains('Bor'));
    }
}
