<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Variant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Kemeja Oxford Slim Fit',
                'category' => 'Fashion Pria',
                'price' => 189000,
                'stock' => 1245,
                'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400&h=400&fit=crop',
                'description' => 'Kemeja pria slim fit untuk gaya kasual dan semi formal.',
                'variant' => ['name' => 'Ukuran', 'value' => 'M'],
            ],
            [
                'name' => 'Sneakers Urban Street',
                'category' => 'Olahraga',
                'price' => 459000,
                'stock' => 3421,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop',
                'description' => 'Sneakers ringan dan nyaman untuk aktivitas harian.',
                'variant' => ['name' => 'Ukuran Sepatu', 'value' => '42'],
            ],
            [
                'name' => 'Smart Watch Series 5',
                'category' => 'Elektronik',
                'price' => 1299000,
                'stock' => 892,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop',
                'description' => 'Smart watch modern dengan fitur kesehatan dan notifikasi pintar.',
                'variant' => ['name' => 'Warna', 'value' => 'Hitam'],
            ],
            [
                'name' => 'Tas Ransel Laptop 15',
                'category' => 'Fashion Pria',
                'price' => 345000,
                'stock' => 2134,
                'image' => 'https://images.unsplash.com/photo-1491637639811-60e2756cc1c7?w=400&h=400&fit=crop',
                'description' => 'Tas ransel multifungsi untuk laptop hingga 15 inci.',
                'variant' => ['name' => 'Warna', 'value' => 'Biru'],
            ],
            [
                'name' => 'Skincare Serum Vitamin C',
                'category' => 'Kecantikan',
                'price' => 189000,
                'stock' => 5678,
                'image' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop',
                'description' => 'Serum wajah vitamin C untuk mencerahkan dan meratakan warna kulit.',
                'variant' => ['name' => 'Kapasitas', 'value' => '32GB'],
            ],
            [
                'name' => 'Celana Chino Slim',
                'category' => 'Fashion Pria',
                'price' => 229000,
                'stock' => 987,
                'image' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=400&h=400&fit=crop',
                'description' => 'Celana chino slim fit dengan bahan nyaman dipakai seharian.',
                'variant' => ['name' => 'Ukuran', 'value' => 'L'],
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category' => 'Elektronik',
                'price' => 599000,
                'stock' => 3210,
                'image' => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=400&h=400&fit=crop',
                'description' => 'Earbuds nirkabel dengan suara jernih dan baterai tahan lama.',
                'variant' => ['name' => 'Warna', 'value' => 'Hitam'],
            ],
            [
                'name' => 'Dress Floral Premium',
                'category' => 'Fashion Wanita',
                'price' => 279000,
                'stock' => 1567,
                'image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=400&h=400&fit=crop',
                'description' => 'Dress floral premium dengan detail elegan dan bahan ringan.',
                'variant' => ['name' => 'Ukuran', 'value' => 'M'],
            ],
            [
                'name' => 'Running Shoes Lite',
                'category' => 'Olahraga',
                'price' => 539000,
                'stock' => 2345,
                'image' => 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=400&h=400&fit=crop',
                'description' => 'Sepatu lari ringan untuk performa harian.',
                'variant' => ['name' => 'Ukuran Sepatu', 'value' => '41'],
            ],
            [
                'name' => 'Blender Portable Mini',
                'category' => 'Rumah Tangga',
                'price' => 149000,
                'stock' => 789,
                'image' => 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=400&h=400&fit=crop',
                'description' => 'Blender mini portabel untuk kebutuhan minuman praktis.',
                'variant' => ['name' => 'Warna', 'value' => 'Putih'],
            ],
            [
                'name' => 'Hoodie Oversized Fleece',
                'category' => 'Fashion Pria',
                'price' => 299000,
                'stock' => 4321,
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop',
                'description' => 'Hoodie oversized berbahan fleece lembut dan hangat.',
                'variant' => ['name' => 'Ukuran', 'value' => 'L'],
            ],
            [
                'name' => 'Kamera Mirrorless Entry',
                'category' => 'Elektronik',
                'price' => 5499000,
                'stock' => 234,
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&h=400&fit=crop',
                'description' => 'Kamera mirrorless entry-level untuk foto dan video berkualitas.',
                'variant' => ['name' => 'Warna', 'value' => 'Hitam'],
            ],
        ];

        $categories = Category::query()->get()->keyBy('name');
        $variants = Variant::query()
            ->get()
            ->keyBy(fn (Variant $v) => $v->name . '::' . $v->value);

        foreach ($products as $product) {
            $slug = Str::slug($product['name']);
            $category = $categories->get($product['category']);

            $savedProduct = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $product['name'],
                    'slug' => $slug,
                    'category_id' => $category?->id,
                    'description' => $product['description'],
                    'status' => 'active',
                ]
            );

            $variantKey = $product['variant']['name'] . '::' . $product['variant']['value'];
            $variant = $variants->get($variantKey);
            if (!$variant) {
                continue;
            }

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
