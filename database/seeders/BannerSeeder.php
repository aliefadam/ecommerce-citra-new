<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Electronics Deals',
                'image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=elektronik'),
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Home Living',
                'image' => 'https://images.unsplash.com/photo-1484101403633-562f891dc89a?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=rumah-tangga'),
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Fashion Promo',
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=fashion-pria'),
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Banner::query()->updateOrCreate(
                ['sort_order' => $item['sort_order']],
                $item,
            );
        }
    }
}
