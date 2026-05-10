<?php

namespace Database\Seeders;

use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Variant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'old_slug' => 'kemeja-oxford-slim-fit',
                'name' => 'Baut Hex M8 x 25mm Galvanis',
                'main_category' => 'Baut',
                'category_detail' => 'Baut Hex',
                'price' => 850,
                'stock' => 12450,
                'image' => 'https://images.unsplash.com/photo-1609205807107-e8ec2120f9de?w=400&h=400&fit=crop',
                'description' => 'Baut hex galvanis ukuran M8 x 25mm untuk kebutuhan konstruksi, rangka, dan perakitan umum.',
                'variant' => ['name' => 'Diameter', 'value' => 'M8'],
            ],
            [
                'old_slug' => 'sneakers-urban-street',
                'name' => 'Mur Hex M8 Baja Galvanis',
                'main_category' => 'Mur',
                'category_detail' => 'Mur Hex',
                'price' => 450,
                'stock' => 18000,
                'image' => 'https://images.unsplash.com/photo-1530124566582-a618bc2615dc?w=400&h=400&fit=crop',
                'description' => 'Mur hex M8 dengan lapisan galvanis, cocok dipasangkan dengan baut M8 untuk kebutuhan bengkel dan proyek.',
                'variant' => ['name' => 'Diameter', 'value' => 'M8'],
            ],
            [
                'old_slug' => 'smart-watch-series-5',
                'name' => 'Ring Plat M8 Galvanis',
                'main_category' => 'Ring & Washer',
                'category_detail' => 'Ring Plat',
                'price' => 250,
                'stock' => 22500,
                'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=400&h=400&fit=crop',
                'description' => 'Ring plat M8 untuk meratakan tekanan baut dan mur pada permukaan material.',
                'variant' => ['name' => 'Diameter', 'value' => 'M8'],
            ],
            [
                'old_slug' => 'tas-ransel-laptop-15',
                'name' => 'Sekrup SDS 12 x 20mm',
                'main_category' => 'Sekrup',
                'category_detail' => 'Sekrup SDS',
                'price' => 650,
                'stock' => 9600,
                'image' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=400&h=400&fit=crop',
                'description' => 'Sekrup self drilling untuk pemasangan baja ringan, atap, dan material metal tipis.',
                'variant' => ['name' => 'Panjang', 'value' => '25mm'],
            ],
            [
                'old_slug' => 'skincare-serum-vitamin-c',
                'name' => 'Dynabolt M10 x 75mm',
                'main_category' => 'Dynabolt & Anchor',
                'category_detail' => 'Dynabolt',
                'price' => 3500,
                'stock' => 4200,
                'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=400&h=400&fit=crop',
                'description' => 'Dynabolt M10 x 75mm untuk pemasangan bracket, rangka, dan dudukan pada beton.',
                'variant' => ['name' => 'Diameter', 'value' => 'M10'],
            ],
            [
                'old_slug' => 'celana-chino-slim',
                'name' => 'Baut L Stainless M6 x 30mm',
                'main_category' => 'Baut',
                'category_detail' => 'Baut L',
                'price' => 1200,
                'stock' => 7300,
                'image' => 'https://images.unsplash.com/photo-1581092580497-e0d23cbdf1dc?w=400&h=400&fit=crop',
                'description' => 'Baut L stainless untuk aplikasi yang membutuhkan kepala tanam dan tampilan rapi.',
                'variant' => ['name' => 'Material', 'value' => 'Stainless 304'],
            ],
            [
                'old_slug' => 'wireless-earbuds-pro',
                'name' => 'Kunci L Set 9 Pcs',
                'main_category' => 'Tools & Perkakas',
                'category_detail' => 'Kunci L',
                'price' => 45000,
                'stock' => 850,
                'image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=400&h=400&fit=crop',
                'description' => 'Set kunci L ukuran lengkap untuk instalasi baut socket dan pekerjaan bengkel.',
                'variant' => ['name' => 'Kemasan', 'value' => 'Pcs'],
            ],
            [
                'old_slug' => 'dress-floral-premium',
                'name' => 'Mur Nyloc M10',
                'main_category' => 'Mur',
                'category_detail' => 'Mur Nyloc',
                'price' => 950,
                'stock' => 6400,
                'image' => 'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=400&h=400&fit=crop',
                'description' => 'Mur nyloc M10 dengan pengunci nylon untuk mengurangi risiko kendor akibat getaran.',
                'variant' => ['name' => 'Diameter', 'value' => 'M10'],
            ],
            [
                'old_slug' => 'running-shoes-lite',
                'name' => 'Mata Bor Besi HSS 6mm',
                'main_category' => 'Tools & Perkakas',
                'category_detail' => 'Mata Bor',
                'price' => 12500,
                'stock' => 1500,
                'image' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=400&h=400&fit=crop',
                'description' => 'Mata bor HSS 6mm untuk pengeboran besi, aluminium, dan material metal ringan.',
                'variant' => ['name' => 'Diameter', 'value' => 'M6'],
            ],
            [
                'old_slug' => 'blender-portable-mini',
                'name' => 'Fischer S6 Nylon Anchor',
                'main_category' => 'Dynabolt & Anchor',
                'category_detail' => 'Fischer',
                'price' => 500,
                'stock' => 12000,
                'image' => 'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?w=400&h=400&fit=crop',
                'description' => 'Fischer nylon S6 untuk pemasangan sekrup pada dinding bata, beton ringan, dan panel.',
                'variant' => ['name' => 'Diameter', 'value' => 'M6'],
            ],
            [
                'old_slug' => 'hoodie-oversized-fleece',
                'name' => 'U Bolt M10 Galvanis',
                'main_category' => 'Klem & Bracket',
                'category_detail' => 'U Bolt',
                'price' => 8500,
                'stock' => 2200,
                'image' => 'https://images.unsplash.com/photo-1581092334247-154d02272fb8?w=400&h=400&fit=crop',
                'description' => 'U bolt galvanis untuk menjepit pipa, tiang, dan komponen rangka.',
                'variant' => ['name' => 'Diameter', 'value' => 'M10'],
            ],
            [
                'old_slug' => 'kamera-mirrorless-entry',
                'name' => 'Threadlocker Medium Strength',
                'main_category' => 'Chemical & Lem',
                'category_detail' => 'Threadlocker',
                'price' => 38000,
                'stock' => 540,
                'image' => 'https://images.unsplash.com/photo-1581092162384-8987c1d64718?w=400&h=400&fit=crop',
                'description' => 'Cairan pengunci ulir kekuatan sedang untuk mencegah baut dan mur mudah longgar.',
                'variant' => ['name' => 'Kemasan', 'value' => 'Pcs'],
            ],
        ];

        $mainCategories = MainCategory::query()->get()->keyBy('name');
        $details = CategoryDetail::query()->get()->groupBy('main_category_id');
        $variants = Variant::query()
            ->get()
            ->keyBy(fn (Variant $v) => $v->name . '::' . $v->value);

        foreach ($products as $product) {
            $slug = Str::slug($product['name']);
            $mainCategory = $mainCategories->get($product['main_category']);
            $detail = $mainCategory
                ? ($details->get($mainCategory->id)?->firstWhere('name', $product['category_detail']) ?? null)
                : null;
            if (!$detail && $mainCategory) {
                $detail = CategoryDetail::query()->firstOrCreate(
                    ['main_category_id' => $mainCategory->id, 'name' => $product['category_detail']],
                    ['slug' => Str::slug($mainCategory->name . '-' . $product['category_detail'])]
                );
            }

            $savedProduct = Product::query()
                ->where('slug', $product['old_slug'])
                ->orWhere('slug', $slug)
                ->first();

            if (!$savedProduct) {
                $savedProduct = new Product();
            }

            $savedProduct->fill([
                'name' => $product['name'],
                'slug' => $slug,
                'main_category_id' => $mainCategory?->id,
                'category_detail_id' => $detail?->id,
                'category_id' => null,
                'description' => $product['description'],
                'status' => 'active',
            ]);
            $savedProduct->save();

            $variantKey = $product['variant']['name'] . '::' . $product['variant']['value'];
            $variant = $variants->get($variantKey);
            if (!$variant) {
                continue;
            }

            ProductVariant::query()
                ->where('product_id', $savedProduct->id)
                ->where('variant_id', '!=', $variant->id)
                ->delete();

            ProductVariant::updateOrCreate(
                [
                    'product_id' => $savedProduct->id,
                    'variant_id' => $variant->id,
                ],
                [
                    'sku' => $this->generateSku($product['name'], $product['variant']['name'], $product['variant']['value']),
                    'image' => $product['image'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                ]
            );
        }
    }

    private function generateSku(string $productName, string $variantName, string $variantValue): string
    {
        $parts = [
            Str::upper(Str::slug($productName, '-')),
            Str::upper(Str::slug($variantName, '-')),
            Str::upper(Str::slug($variantValue, '-')),
        ];

        return implode('-', array_filter($parts));
    }
}
