<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Mouse Pro', 'sku' => 'PRD-001', 'price' => 249000, 'stock' => 35, 'status' => 'active', 'description' => 'Ergonomic wireless mouse with silent click and adjustable DPI.'],
            ['name' => 'Mechanical Keyboard TKL', 'sku' => 'PRD-002', 'price' => 899000, 'stock' => 18, 'status' => 'active', 'description' => 'Tenkeyless mechanical keyboard with RGB backlight and hot-swappable switches.'],
            ['name' => 'USB-C Hub 7 in 1', 'sku' => 'PRD-003', 'price' => 459000, 'stock' => 0, 'status' => 'inactive', 'description' => 'Multiport USB-C hub with HDMI, USB-A, PD charging, and card reader.'],
            ['name' => 'Laptop Stand Aluminum', 'sku' => 'PRD-004', 'price' => 299000, 'stock' => 42, 'status' => 'active', 'description' => 'Foldable aluminum stand for laptops up to 16 inches.'],
            ['name' => 'Noise Cancelling Headset', 'sku' => 'PRD-005', 'price' => 1299000, 'stock' => 12, 'status' => 'active', 'description' => 'Over-ear headset with active noise cancellation and long battery life.'],
            ['name' => '1080p Web Camera', 'sku' => 'PRD-006', 'price' => 549000, 'stock' => 25, 'status' => 'active', 'description' => 'Full HD webcam with built-in microphone and auto light correction.'],
            ['name' => 'External SSD 1TB', 'sku' => 'PRD-007', 'price' => 1749000, 'stock' => 9, 'status' => 'active', 'description' => 'Portable NVMe SSD with USB 3.2 Gen2 performance.'],
            ['name' => 'Gaming Mouse Pad XL', 'sku' => 'PRD-008', 'price' => 189000, 'stock' => 60, 'status' => 'active', 'description' => 'Large anti-slip mouse pad with smooth micro-textured surface.'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
