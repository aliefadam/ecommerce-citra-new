<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use App\Models\Variant;
use App\Services\CheckoutPricingService;
use App\Services\ShippingQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShippingQuoteSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_options_use_database_weight_and_return_a_bound_quote_token(): void
    {
        Config::set('services.rajaongkir.api_key', 'test-key');
        Config::set('services.rajaongkir.base_url', 'https://rajaongkir.test');
        Http::fake(['*' => Http::response([
            'meta' => ['status' => 'success'],
            'data' => [[
                'code' => 'jne',
                'name' => 'Jalur Nugraha Ekakurir',
                'service' => 'REG',
                'cost' => 17000,
                'etd' => '2-3 day',
            ]],
        ])]);

        $company = Company::query()->firstOrFail();
        StoreLocation::query()->create([
            'company_id' => $company->id,
            'label' => 'Gudang Test',
            'city_id' => 10,
            'city_name' => 'Jakarta',
            'province_name' => 'DKI Jakarta',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Produk Quote',
            'slug' => 'produk-quote-'.Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
        $variant = Variant::query()->create(['name' => 'Ukuran', 'value' => 'Test']);
        $productVariant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'QUOTE-'.Str::upper(Str::random(6)),
            'price' => 100000,
            'stock' => 5,
            'weight_grams' => 1500,
        ]);
        $items = [['productVariantId' => $productVariant->id, 'qty' => 2]];

        $response = $this->withSession(['checkout' => ['source' => 'buy_now', 'items' => $items]])
            ->getJson(route('frontend.rajaongkir.shipping-options', [
                'destination_id' => 99,
                'company_id' => $company->id,
                'items' => json_encode($items, JSON_THROW_ON_ERROR),
                'weight' => 1,
            ]))
            ->assertOk()
            ->assertJsonPath('data.0.cost', 17000)
            ->assertJsonStructure(['data' => [['quote_token']]]);

        Http::assertSent(fn ($request) => (int) $request['origin'] === 10
            && (int) $request['destination'] === 99
            && (int) $request['weight'] === 3000);

        $pricing = app(CheckoutPricingService::class)->resolve($items, $company->id);
        $quote = app(ShippingQuoteService::class)->verify(
            (string) $response->json('data.0.quote_token'),
            $company->id,
            99,
            $pricing['fingerprint'],
        );

        $this->assertSame(17000, $quote['cost']);
        $this->assertSame('JALUR NUGRAHA EKAKURIR REG', $quote['label']);
    }
}
