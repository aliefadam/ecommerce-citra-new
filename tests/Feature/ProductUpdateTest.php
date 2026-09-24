<?php

namespace Tests\Feature;

use App\Models\AttributeDefinition;
use App\Models\Cart;
use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_import_template_downloads_a_valid_xlsx_file(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('products.import-template'));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $content = $response->streamedContent();
        $this->assertStringStartsWith('PK', $content);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'product-template-');

        try {
            file_put_contents($temporaryFile, $content);
            $spreadsheet = IOFactory::load($temporaryFile);

            $this->assertSame('product_name', $spreadsheet->getActiveSheet()->getCell('A1')->getValue());
            $this->assertSame('Baut Hex M8 x 25mm Galvanis', $spreadsheet->getActiveSheet()->getCell('A2')->getValue());

            $spreadsheet->disconnectWorksheets();
        } finally {
            if (is_string($temporaryFile) && file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

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
            'category_detail_id' => $detail->id,
            'status' => 'active',
            'variants' => [
                [
                    'product_variant_id' => $existingProductVariant->id,
                    'variant_id' => $firstVariant->id,
                    'existing_image' => '',
                    'price' => '15000',
                    'stock' => '7',
                    'weight_grams' => '1000',
                    'attributes' => [
                        $this->diameterAttribute()->id => [
                            'attribute_definition_id' => $this->diameterAttribute()->id,
                            'value_text' => 'M8',
                        ],
                    ],
                ],
                [
                    'variant_id' => $secondVariant->id,
                    'existing_image' => '',
                    'price' => '17000',
                    'stock' => '3',
                    'weight_grams' => '1200',
                    'attributes' => [
                        $this->diameterAttribute()->id => [
                            'attribute_definition_id' => $this->diameterAttribute()->id,
                            'value_text' => 'M10',
                        ],
                    ],
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
        $newProductVariant = $product->fresh()->productVariants()
            ->where('price', 17000)
            ->where('stock', 3)
            ->first();

        $this->assertNotNull($newProductVariant);
        $this->assertDatabaseHas('variants', [
            'id' => $newProductVariant->variant_id,
            'name' => 'Varian SKU',
            'value' => 'M10',
        ]);
    }

    public function test_edit_product_replaces_existing_image_with_a_large_png(): void
    {
        Storage::fake('public');
        [$product, $detail, , , $productVariant] = $this->createProductFixture();
        $productVariant->update(['image' => 'product-variants/old.webp']);
        Storage::disk('public')->put('product-variants/old.webp', 'old-image');
        $this->actingAs($this->makeAdminUser());

        $payload = $this->singleVariantUpdatePayload($product, $detail, $productVariant);
        $payload['variants'][0]['image'] = UploadedFile::fake()
            ->image('replacement.png', 2400, 1800)
            ->size(7500);

        $this->put(route('products.update', $product), $payload)
            ->assertRedirect(route('products.index'))
            ->assertSessionHasNoErrors();

        $storedImage = $productVariant->fresh()->image;

        $this->assertNotSame('product-variants/old.webp', $storedImage);
        $this->assertStringEndsWith('.webp', $storedImage);
        Storage::disk('public')->assertExists($storedImage);
        Storage::disk('public')->assertMissing('product-variants/old.webp');
        $this->assertSame('image/webp', getimagesizefromstring(Storage::disk('public')->get($storedImage))['mime']);
    }

    public function test_edit_product_does_not_trust_the_existing_image_path_from_the_browser(): void
    {
        Storage::fake('public');
        [$product, $detail, , , $productVariant] = $this->createProductFixture();
        $productVariant->update(['image' => 'product-variants/original.webp']);
        Storage::disk('public')->put('product-variants/original.webp', 'original-image');
        $this->actingAs($this->makeAdminUser());

        $payload = $this->singleVariantUpdatePayload($product, $detail, $productVariant);
        $payload['variants'][0]['existing_image'] = 'product-variants/tampered.webp';

        $this->put(route('products.update', $product), $payload)
            ->assertRedirect(route('products.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('product-variants/original.webp', $productVariant->fresh()->image);
        Storage::disk('public')->assertExists('product-variants/original.webp');
    }

    public function test_edit_product_rejects_an_image_over_twelve_megabytes_and_preserves_the_existing_image(): void
    {
        Storage::fake('public');
        [$product, $detail, , , $productVariant] = $this->createProductFixture();
        $productVariant->update(['image' => 'product-variants/original.webp']);
        Storage::disk('public')->put('product-variants/original.webp', 'original-image');
        $this->actingAs($this->makeAdminUser());

        $payload = $this->singleVariantUpdatePayload($product, $detail, $productVariant);
        $payload['variants'][0]['image'] = UploadedFile::fake()
            ->image('too-large.png', 2400, 1800)
            ->size(13000);

        $this->from(route('products.edit', $product))
            ->put(route('products.update', $product), $payload)
            ->assertRedirect(route('products.edit', $product))
            ->assertSessionHasErrors([
                'variants.0.image' => 'Ukuran gambar varian maksimal 12 MB.',
            ]);

        $this->assertSame('product-variants/original.webp', $productVariant->fresh()->image);
        Storage::disk('public')->assertExists('product-variants/original.webp');
    }

    public function test_edit_product_exposes_storefront_shortcuts_in_a_new_tab(): void
    {
        [$product] = $this->createProductFixture();
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('products.edit', $product));

        $response->assertOk()
            ->assertSee('data-testid="view-product-link"', false)
            ->assertSee('href="'.route('frontend.detail-produk', ['slug' => $product->slug]).'"', false)
            ->assertSee('data-testid="view-website-link"', false)
            ->assertSee('href="'.route('frontend.index').'"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_inactive_product_edit_page_does_not_link_to_an_unavailable_storefront_page(): void
    {
        [$product] = $this->createProductFixture();
        $product->update(['status' => 'inactive']);
        $this->actingAs($this->makeAdminUser());

        $this->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('data-testid="view-product-disabled"', false)
            ->assertSee('Produk Belum Aktif')
            ->assertDontSee('data-testid="view-product-link"', false);
    }

    public function test_edit_product_does_not_multiply_untouched_or_localized_rupiah_prices(): void
    {
        [$product, $detail, , , $productVariant] = $this->createProductFixture();
        $this->actingAs($this->makeAdminUser());

        $basePayload = [
            'name' => $product->name,
            'category_detail_id' => $detail->id,
            'status' => 'active',
            'variants' => [[
                'product_variant_id' => $productVariant->id,
                'existing_image' => '',
                'stock' => '5',
                'weight_grams' => '1000',
                'attributes' => [
                    $this->diameterAttribute()->id => [
                        'attribute_definition_id' => $this->diameterAttribute()->id,
                        'value_text' => 'M8',
                    ],
                ],
            ]],
        ];

        $untouchedDecimalPayload = $basePayload;
        $untouchedDecimalPayload['variants'][0]['price'] = (string) $productVariant->price;
        $this->put(route('products.update', $product), $untouchedDecimalPayload)
            ->assertRedirect(route('products.index'))
            ->assertSessionHasNoErrors();
        $this->assertSame(10000.0, (float) $productVariant->fresh()->price);

        $localizedPayload = $basePayload;
        $localizedPayload['variants'][0]['price'] = '13.000';
        $this->put(route('products.update', $product), $localizedPayload)
            ->assertRedirect(route('products.index'))
            ->assertSessionHasNoErrors();
        $this->assertSame(13000.0, (float) $productVariant->fresh()->price);

        $localizedDecimalPayload = $basePayload;
        $localizedDecimalPayload['variants'][0]['price'] = '13.000,00';
        $this->put(route('products.update', $product), $localizedDecimalPayload)
            ->assertRedirect(route('products.index'))
            ->assertSessionHasNoErrors();
        $this->assertSame(13000.0, (float) $productVariant->fresh()->price);
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
                'category_detail_id' => $detail->id,
                'status' => 'active',
                'variants' => [
                    [
                        'variant_id' => $secondVariant->id,
                        'existing_image' => '',
                        'price' => '17000',
                        'stock' => '3',
                        'weight_grams' => '1200',
                        'attributes' => [
                            $this->diameterAttribute()->id => [
                                'attribute_definition_id' => $this->diameterAttribute()->id,
                                'value_text' => 'M10',
                            ],
                        ],
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
            'weight_grams' => 1000,
        ]);

        return [$product, $detail, $firstVariant, $secondVariant, $productVariant];
    }

    private function singleVariantUpdatePayload(Product $product, CategoryDetail $detail, ProductVariant $productVariant): array
    {
        return [
            'name' => $product->name,
            'category_detail_id' => $detail->id,
            'status' => 'active',
            'variants' => [[
                'product_variant_id' => $productVariant->id,
                'existing_image' => $productVariant->image ?? '',
                'price' => '10000',
                'stock' => '5',
                'weight_grams' => '1000',
                'attributes' => [
                    $this->diameterAttribute()->id => [
                        'attribute_definition_id' => $this->diameterAttribute()->id,
                        'value_text' => 'M8',
                    ],
                ],
            ]],
        ];
    }

    private function makeAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function diameterAttribute(): AttributeDefinition
    {
        return AttributeDefinition::query()->firstOrCreate(
            ['code' => 'diameter'],
            [
                'name' => 'Diameter',
                'data_type' => 'text',
                'sort_order' => 10,
            ]
        );
    }
}
