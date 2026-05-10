<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_product_can_add_a_new_variant_without_replacing_existing_variant_ids(): void
    {
        [$product, $detail, $firstVariant, $secondVariant, $existingProductVariant] = $this->createProductFixture();
        $this->actingAs($this->makeAdminUser());

        $cart = Cart::create([
            'user_id' => User::factory()->create()->id,
            'product_variant_id' => $existingProductVariant->id,
            'quantity' => 2,
        ]);

        $response = $this->from(route('products.edit', $product))->put(route('products.update', $product), [
            'name' => 'Produk Update',
            'category_id' => $detail->id,
            'status' => 'active',
            'variants' => [
                [
                    'product_variant_id' => $existingProductVariant->id,
                    'variant_id' => $firstVariant->id,
                    'existing_image' => '',
                    'price' => '15000',
                    'stock' => '7',
                ],
                [
                    'variant_id' => $secondVariant->id,
                    'existing_image' => '',
                    'price' => '17000',
                    'stock' => '3',
                ],
            ],
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasNoErrors();

        $existingProductVariant->refresh();
        $cart->refresh();

        $this->assertSame(15000.0, (float) $existingProductVariant->price);
        $this->assertSame(7, $existingProductVariant->stock);
        $this->assertSame($existingProductVariant->id, $cart->product_variant_id);
        $this->assertCount(2, $product->fresh()->productVariants);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'variant_id' => $secondVariant->id,
            'price' => 17000,
            'stock' => 3,
        ]);
    }

    public function test_edit_product_cannot_remove_a_variant_that_is_still_in_use(): void
    {
        [$product, $detail, $firstVariant, $secondVariant, $existingProductVariant] = $this->createProductFixture();
        $this->actingAs($this->makeAdminUser());

        Cart::create([
            'user_id' => User::factory()->create()->id,
            'product_variant_id' => $existingProductVariant->id,
            'quantity' => 1,
        ]);

        $response = $this->from(route('products.edit', $product))
            ->put(route('products.update', $product), [
                'name' => 'Produk Update',
                'category_id' => $detail->id,
                'status' => 'active',
                'variants' => [
                    [
                        'product_variant_id' => $existingProductVariant->id,
                        'variant_id' => $secondVariant->id,
                        'existing_image' => '',
                        'price' => '17000',
                        'stock' => '3',
                    ],
                ],
            ]);

        $response->assertRedirect(route('products.edit', $product));
        $response->assertSessionHasErrors('variants');

        $this->assertDatabaseHas('product_variants', [
            'id' => $existingProductVariant->id,
            'product_id' => $product->id,
            'variant_id' => $firstVariant->id,
        ]);
        $this->assertDatabaseMissing('product_variants', [
            'product_id' => $product->id,
            'variant_id' => $secondVariant->id,
        ]);
    }

    private function createProductFixture(): array
    {
        $mainCategory = MainCategory::create([
            'name' => 'Baut',
            'slug' => 'baut',
        ]);

        $detail = CategoryDetail::create([
            'main_category_id' => $mainCategory->id,
            'name' => 'Hex Bolt',
            'slug' => 'hex-bolt',
        ]);

        $firstVariant = Variant::create([
            'name' => 'Diameter',
            'value' => 'M8',
        ]);

        $secondVariant = Variant::create([
            'name' => 'Diameter',
            'value' => 'M10',
        ]);

        $product = Product::create([
            'name' => 'Produk Lama',
            'slug' => Str::slug('Produk Lama'),
            'main_category_id' => $mainCategory->id,
            'category_detail_id' => $detail->id,
            'status' => 'active',
        ]);

        $productVariant = ProductVariant::create([
            'product_id' => $product->id,
            'variant_id' => $firstVariant->id,
            'sku' => 'PRODUK-LAMA-DIAMETER-M8',
            'price' => 10000,
            'stock' => 5,
        ]);

        return [$product, $detail, $firstVariant, $secondVariant, $productVariant];
    }

    private function makeAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }
}
