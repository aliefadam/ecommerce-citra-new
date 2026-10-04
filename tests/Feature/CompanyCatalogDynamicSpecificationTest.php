<?php

namespace Tests\Feature;

use App\Models\AttributeDefinition;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyCatalogDynamicSpecificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_search_finds_generic_attribute_values_without_cross_company_leakage(): void
    {
        $company = $this->company('catalog-a', 'CTA');
        $otherCompany = $this->company('catalog-b', 'CTB');
        $definition = AttributeDefinition::query()->where('code', 'material')->firstOrFail();

        $expected = $this->productWithAttribute($company, 'Valve Alpha', 'API-A-001', $definition, 'Duplex 2205', 17);
        $this->productWithAttribute($company, 'Valve Unrelated', 'API-A-002', $definition, 'Carbon Steel', 9);
        $this->productWithAttribute($otherCompany, 'Valve Tenant B', 'API-B-001', $definition, 'Duplex 2205', 99);

        $response = $this->getJson('/api/v1/companies/'.$company->slug.'/products?search=duplex');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id)
            ->assertJsonMissing(['name' => 'Valve Tenant B']);
    }

    public function test_catalog_detail_exposes_generic_attributes_without_stock_or_admin_configuration(): void
    {
        $company = $this->company('catalog-detail', 'CTD');
        $definition = AttributeDefinition::query()->where('code', 'material')->firstOrFail();
        $product = $this->productWithAttribute($company, 'Pipe API Detail', 'API-D-001', $definition, 'SS316', 23);

        $response = $this->getJson('/api/v1/companies/'.$company->slug.'/products/'.$product->slug);

        $response->assertOk()
            ->assertJsonPath('data.variants.0.in_stock', true)
            ->assertJsonPath('data.variants.0.attributes.0.code', 'material')
            ->assertJsonPath('data.variants.0.attributes.0.value', 'SS316')
            ->assertJsonMissingPath('data.variants.0.stock')
            ->assertJsonMissingPath('data.company_id')
            ->assertJsonMissingPath('data.specification_template_id')
            ->assertJsonMissingPath('data.variants.0.attributes.0.is_filterable');
    }

    private function company(string $slug, string $prefix): Company
    {
        return Company::query()->create([
            'name' => strtoupper($slug),
            'slug' => $slug,
            'invoice_prefix' => $prefix,
            'is_active' => true,
        ]);
    }

    private function productWithAttribute(
        Company $company,
        string $name,
        string $sku,
        AttributeDefinition $definition,
        string $value,
        int $stock
    ): Product {
        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.str()->lower(str()->random(5)),
            'status' => 'active',
        ]);
        $variant = Variant::query()->firstOrCreate([
            'name' => 'Material',
            'value' => $value,
        ]);
        $productVariant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => $sku,
            'price' => 125000,
            'stock' => $stock,
        ]);
        ProductVariantAttribute::query()->create([
            'product_variant_id' => $productVariant->id,
            'attribute_definition_id' => $definition->id,
            'value_text' => $value,
        ]);

        return $product;
    }
}
