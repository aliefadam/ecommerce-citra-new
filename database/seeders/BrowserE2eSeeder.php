<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use Illuminate\Database\Seeder;

class BrowserE2eSeeder extends Seeder
{
    public function run(): void
    {
        $primaryCompany = Company::query()->orderBy('id')->firstOrFail();
        $secondaryCompany = Company::query()->where('slug', 'pt-dua-sejahtera')->firstOrFail();
        Company::query()
            ->where('slug', 'pt-empat-perkasa')
            ->update(['is_active' => false]);
        $sourceProduct = Product::query()
            ->with('productVariants')
            ->where('company_id', $primaryCompany->id)
            ->where('slug', 'baut-hex-m8-x-25mm-galvanis')
            ->firstOrFail();
        $sourceVariant = $sourceProduct->productVariants->firstOrFail();

        $product = Product::query()->updateOrCreate(
            ['slug' => 'mur-hex-m8-pt-dua-e2e'],
            [
                'company_id' => $secondaryCompany->id,
                'name' => 'Mur Hex M8 PT Dua E2E',
                'main_category_id' => $sourceProduct->main_category_id,
                'category_detail_id' => $sourceProduct->category_detail_id,
                'category_id' => $sourceProduct->category_id,
                'description' => 'Fixture browser E2E untuk checkout lintas perusahaan.',
                'is_redeem_product' => false,
                'redeem_points' => 0,
                'status' => 'active',
            ]
        );

        ProductVariant::query()->updateOrCreate(
            ['product_id' => $product->id, 'sku' => 'E2E-PTDUA-M8'],
            [
                'variant_id' => $sourceVariant->variant_id,
                'image' => $sourceVariant->image,
                'price' => 1250,
                'stock' => 100,
                'weight_grams' => $sourceVariant->weight_grams,
                'length_cm' => $sourceVariant->length_cm,
                'width_cm' => $sourceVariant->width_cm,
                'height_cm' => $sourceVariant->height_cm,
                'low_stock_threshold' => 5,
            ]
        );

        $outOfStockProduct = Product::query()->updateOrCreate(
            ['slug' => 'mur-hex-m10-pt-dua-e2e'],
            [
                'company_id' => $secondaryCompany->id,
                'name' => 'Mur Hex M10 PT Dua E2E',
                'main_category_id' => $sourceProduct->main_category_id,
                'category_detail_id' => $sourceProduct->category_detail_id,
                'category_id' => $sourceProduct->category_id,
                'description' => 'Fixture produk habis untuk kontrak API katalog.',
                'is_redeem_product' => false,
                'redeem_points' => 0,
                'status' => 'active',
            ]
        );

        ProductVariant::query()->updateOrCreate(
            ['product_id' => $outOfStockProduct->id, 'sku' => 'E2E-PTDUA-M10'],
            [
                'variant_id' => $sourceVariant->variant_id,
                'image' => $sourceVariant->image,
                'price' => 2250,
                'stock' => 0,
                'weight_grams' => $sourceVariant->weight_grams,
                'length_cm' => $sourceVariant->length_cm,
                'width_cm' => $sourceVariant->width_cm,
                'height_cm' => $sourceVariant->height_cm,
                'low_stock_threshold' => 5,
            ]
        );

        Product::query()->updateOrCreate(
            ['slug' => 'mur-hex-inactive-pt-dua-e2e'],
            [
                'company_id' => $secondaryCompany->id,
                'name' => 'Mur Hex Nonaktif PT Dua E2E',
                'main_category_id' => $sourceProduct->main_category_id,
                'category_detail_id' => $sourceProduct->category_detail_id,
                'category_id' => $sourceProduct->category_id,
                'description' => 'Fixture produk nonaktif untuk kontrak API katalog.',
                'is_redeem_product' => true,
                'redeem_points' => 999,
                'status' => 'inactive',
            ]
        );

        $sourceLocation = StoreLocation::query()
            ->where('company_id', $primaryCompany->id)
            ->where('is_active', true)
            ->firstOrFail();

        StoreLocation::query()->updateOrCreate(
            ['company_id' => $secondaryCompany->id, 'label' => 'Lokasi PT Dua E2E'],
            [
                'province_id' => $sourceLocation->province_id,
                'city_id' => $sourceLocation->city_id,
                'city_name' => $sourceLocation->city_name,
                'province_name' => $sourceLocation->province_name,
                'is_active' => true,
            ]
        );
    }
}
