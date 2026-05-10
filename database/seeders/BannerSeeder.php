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
                'title' => 'Baut & Mur Lengkap',
                'type' => 'carousel',
                'image' => 'https://images.unsplash.com/photo-1609205807107-e8ec2120f9de?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=baut'),
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Tools Bengkel & Proyek',
                'type' => 'carousel',
                'image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=tools-perkakas'),
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Anchor & Dynabolt',
                'type' => 'carousel',
                'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=1600&h=700&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=dynabolt-anchor'),
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        // Side banners — panel kanan (maks 2)
        $sideItems = [
            [
                'title' => 'Promo Fastener',
                'type' => 'side',
                'image' => 'https://images.unsplash.com/photo-1530124566582-a618bc2615dc?w=600&h=300&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=mur'),
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Paket Perkakas',
                'type' => 'side',
                'image' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=600&h=300&fit=crop&crop=center',
                'target_url' => url('/kategori?parent=tools-perkakas'),
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
