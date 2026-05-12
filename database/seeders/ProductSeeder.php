<?php

namespace Database\Seeders;

use App\Models\CategoryDetail;
use App\Models\AttributeDefinition;
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
            [
                'old_slug' => 'baut-mur-baja-10-9',
                'name' => 'Baut Mur Baja 10.9',
                'main_category' => 'Baut',
                'category_detail' => 'Baut HTB',
                'description' => 'Baut mur baja grade 10.9 untuk kebutuhan konstruksi dan fabrikasi, tersedia dalam banyak kombinasi diameter, panjang, dan tipe drat.',
                'variants' => $this->buildBautMurBaja109Variants(),
            ],
        ];

        $mainCategories = MainCategory::query()->get()->keyBy('name');
        $details = CategoryDetail::query()->get()->groupBy('main_category_id');
        $variants = Variant::query()
            ->get()
            ->keyBy(fn (Variant $v) => $v->name . '::' . $v->value);
        $attributeDefinitions = AttributeDefinition::query()
            ->get()
            ->keyBy('code');

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

            $productVariants = $product['variants'] ?? [
                [
                    'variant' => $product['variant'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'image' => $product['image'],
                    'weight_grams' => $product['weight_grams'] ?? 1000,
                    'length_cm' => $product['length_cm'] ?? 10,
                    'width_cm' => $product['width_cm'] ?? 10,
                    'height_cm' => $product['height_cm'] ?? 10,
                    'attributes' => $product['attributes'] ?? [],
                ],
            ];

            $keptVariantIds = [];

            foreach ($productVariants as $productVariant) {
                $variantKey = $productVariant['variant']['name'] . '::' . $productVariant['variant']['value'];
                $variant = $variants->get($variantKey);
                if (!$variant) {
                    continue;
                }

                $keptVariantIds[] = $variant->id;

                $savedVariant = ProductVariant::updateOrCreate(
                    [
                        'product_id' => $savedProduct->id,
                        'variant_id' => $variant->id,
                    ],
                    [
                        'sku' => $this->generateSku($product['name'], $productVariant['variant']['name'], $productVariant['variant']['value']),
                        'image' => $productVariant['image'],
                        'price' => $productVariant['price'],
                        'stock' => $productVariant['stock'],
                        'weight_grams' => $productVariant['weight_grams'] ?? 1000,
                        'length_cm' => $productVariant['length_cm'] ?? 10,
                        'width_cm' => $productVariant['width_cm'] ?? 10,
                        'height_cm' => $productVariant['height_cm'] ?? 10,
                    ]
                );

                $this->syncVariantAttributes($savedVariant, $productVariant['attributes'] ?? [], $attributeDefinitions);
            }

            if ($keptVariantIds !== []) {
                ProductVariant::query()
                    ->where('product_id', $savedProduct->id)
                    ->whereNotIn('variant_id', $keptVariantIds)
                    ->delete();
            }
        }
    }

    private function buildBautMurBaja109Variants(): array
    {
        $specs = [
            'M10' => [
                ['20mm', 'Full Drat'],
                ['25mm', 'Full Drat'],
                ['30mm', 'Full Drat'],
                ['35mm', 'Half Drat'],
                ['40mm', 'Half Drat'],
                ['45mm', 'Half Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
            ],
            'M12' => [
                ['25mm', 'Full Drat'],
                ['30mm', 'Full Drat'],
                ['35mm', 'Full Drat'],
                ['40mm', 'Half Drat'],
                ['45mm', 'Half Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
            ],
            'M14' => [
                ['30mm', 'Full Drat'],
                ['35mm', 'Full Drat'],
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
            ],
            'M16' => [
                ['30mm', 'Full Drat'],
                ['35mm', 'Full Drat'],
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
                ['110mm', 'Half Drat'],
                ['120mm', 'Half Drat'],
                ['125mm', 'Half Drat'],
                ['130mm', 'Half Drat'],
                ['140mm', 'Half Drat'],
                ['150mm', 'Half Drat'],
                ['160mm', 'Half Drat'],
                ['170mm', 'Half Drat'],
                ['180mm', 'Half Drat'],
            ],
            'M18' => [
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Full Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
                ['110mm', 'Half Drat'],
                ['120mm', 'Half Drat'],
                ['125mm', 'Half Drat'],
                ['130mm', 'Half Drat'],
                ['140mm', 'Half Drat'],
                ['150mm', 'Half Drat'],
            ],
            'M20' => [
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Full Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
                ['110mm', 'Half Drat'],
            ],
            'M22' => [
                ['50mm', 'Full Drat'],
                ['55mm', 'Full Drat'],
                ['60mm', 'Full Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
            ],
        ];

        $image = 'https://images.unsplash.com/photo-1609205807107-e8ec2120f9de?w=400&h=400&fit=crop';

        return collect($specs)
            ->flatMap(function (array $items, string $diameter) use ($image) {
                return collect($items)->map(function (array $item) use ($diameter, $image) {
                    [$panjang, $tipeDrat] = $item;

                    return [
                        'variant' => [
                            'name' => 'Varian SKU',
                            'value' => $diameter . ' x ' . $panjang . ' - ' . $tipeDrat,
                        ],
                        'price' => $this->estimateBautMurPrice($diameter, $panjang, $tipeDrat),
                        'stock' => $this->estimateBautMurStock($diameter, $panjang),
                        'image' => $image,
                        'weight_grams' => $this->estimateBautMurWeight($diameter, $panjang),
                        'length_cm' => $this->estimateBautMurLengthCm($panjang),
                        'width_cm' => $this->estimateBautMurWidthCm($diameter),
                        'height_cm' => $this->estimateBautMurHeightCm($diameter),
                        'attributes' => [
                            'diameter' => $diameter,
                            'length_mm' => (int) filter_var($panjang, FILTER_SANITIZE_NUMBER_INT),
                            'thread_type' => $tipeDrat,
                            'grade' => '10.9',
                            'material' => 'Baja',
                        ],
                    ];
                });
            })
            ->values()
            ->all();
    }

    private function estimateBautMurPrice(string $diameter, string $panjang, string $tipeDrat): int
    {
        $diameterBasePrice = [
            'M10' => 2800,
            'M12' => 4200,
            'M14' => 5800,
            'M16' => 7600,
            'M18' => 9800,
            'M20' => 12400,
            'M22' => 15300,
        ];

        $length = (int) filter_var($panjang, FILTER_SANITIZE_NUMBER_INT);
        $base = $diameterBasePrice[$diameter] ?? 2500;
        $extraLength = max(0, $length - 20) * 35;
        $threadAdjustment = $tipeDrat === 'Full Drat' ? 250 : 0;

        return $base + $extraLength + $threadAdjustment;
    }

    private function estimateBautMurStock(string $diameter, string $panjang): int
    {
        $diameterBaseStock = [
            'M10' => 1800,
            'M12' => 1600,
            'M14' => 1400,
            'M16' => 1200,
            'M18' => 950,
            'M20' => 800,
            'M22' => 650,
        ];

        $length = (int) filter_var($panjang, FILTER_SANITIZE_NUMBER_INT);
        $base = $diameterBaseStock[$diameter] ?? 1000;
        $reduction = (int) floor(max(0, $length - 20) / 5) * 25;

        return max(120, $base - $reduction);
    }

    private function estimateBautMurWeight(string $diameter, string $panjang): int
    {
        $diameterWeight = [
            'M10' => 40,
            'M12' => 65,
            'M14' => 95,
            'M16' => 135,
            'M18' => 180,
            'M20' => 230,
            'M22' => 290,
        ];

        $length = (int) filter_var($panjang, FILTER_SANITIZE_NUMBER_INT);
        $base = $diameterWeight[$diameter] ?? 50;

        return $base + max(0, $length - 20) * 3;
    }

    private function estimateBautMurLengthCm(string $panjang): float
    {
        $length = (int) filter_var($panjang, FILTER_SANITIZE_NUMBER_INT);

        return round(($length / 10) + 1.5, 2);
    }

    private function estimateBautMurWidthCm(string $diameter): float
    {
        $diameterNumber = (int) filter_var($diameter, FILTER_SANITIZE_NUMBER_INT);

        return round(max(1.5, ($diameterNumber / 10) + 1.2), 2);
    }

    private function estimateBautMurHeightCm(string $diameter): float
    {
        $diameterNumber = (int) filter_var($diameter, FILTER_SANITIZE_NUMBER_INT);

        return round(max(1.5, ($diameterNumber / 10) + 1.2), 2);
    }

    private function syncVariantAttributes(ProductVariant $productVariant, array $attributes, $attributeDefinitions): void
    {
        $definitionIds = [];

        foreach ($attributes as $code => $value) {
            $definition = $attributeDefinitions->get($code);
            if (!$definition) {
                continue;
            }

            $payload = [
                'value_text' => null,
                'value_number' => null,
            ];

            if ($definition->data_type === 'number') {
                $payload['value_number'] = is_numeric($value) ? $value : null;
            } else {
                $payload['value_text'] = filled($value) ? (string) $value : null;
            }

            if ($payload['value_text'] === null && $payload['value_number'] === null) {
                continue;
            }

            $definitionIds[] = $definition->id;

            $productVariant->attributeValues()->updateOrCreate(
                ['attribute_definition_id' => $definition->id],
                $payload
            );
        }

        $productVariant->attributeValues()
            ->when($definitionIds !== [], fn ($query) => $query->whereNotIn('attribute_definition_id', $definitionIds))
            ->when($definitionIds === [], fn ($query) => $query)
            ->delete();
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
