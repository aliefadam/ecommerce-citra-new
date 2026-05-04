<?php

namespace Database\Seeders;

use App\Models\MainCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MainCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Rumah Tangga', 'image' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Fashion Pria', 'image' => 'https://images.unsplash.com/photo-1516826957135-700dedea698c?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Fashion Wanita', 'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Elektronik', 'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Kecantikan', 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Olahraga', 'image' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Mainan & Anak', 'image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'HP & Tablet', 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Makanan & Minuman', 'image' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=160&h=160&fit=crop&crop=center'],
            ['name' => 'Ibu & Bayi', 'image' => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?w=160&h=160&fit=crop&crop=center'],
        ];

        foreach ($items as $item) {
            MainCategory::updateOrCreate(
                ['name' => $item['name']],
                [
                    'slug' => Str::slug($item['name']),
                    'image' => $item['image'],
                ]
            );
        }
    }
}
