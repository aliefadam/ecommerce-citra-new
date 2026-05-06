<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Carousel banners — slider utama (kiri)
        $carouselItems = [
            [
                'title' => 'Electronics Deals',
                'type' => 'carousel',
                'image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=elektronik'),
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Home Living',
                'type' => 'carousel',
                'image' => 'https://images.unsplash.com/photo-1484101403633-562f891dc89a?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=rumah-tangga'),
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Fashion Promo',
                'type' => 'carousel',
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=fashion-pria'),
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        // Side banners — panel kanan (maks 2)
        $sideItems = [
            [
                'title' => 'Promo Spesial',
                'type' => 'side',
                'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&h=300&fit=crop&crop=center',
                'target_url' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Flash Sale',
                'type' => 'side',
                'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=300&fit=crop&crop=center',
                'target_url' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($carouselItems as $item) {
            Banner::query()->updateOrCreate(
                ['type' => 'carousel', 'sort_order' => $item['sort_order']],
                $item,
            );
        }

        foreach ($sideItems as $item) {
            Banner::query()->updateOrCreate(
                ['type' => 'side', 'sort_order' => $item['sort_order']],
                $item,
            );
        }
    }
}
