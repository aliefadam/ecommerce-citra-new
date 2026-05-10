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
            ['old_name' => 'Rumah Tangga', 'name' => 'Baut', 'image' => 'https://images.unsplash.com/photo-1609205807107-e8ec2120f9de?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Fashion Pria', 'name' => 'Mur', 'image' => 'https://images.unsplash.com/photo-1530124566582-a618bc2615dc?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Fashion Wanita', 'name' => 'Ring & Washer', 'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Elektronik', 'name' => 'Sekrup', 'image' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Kecantikan', 'name' => 'Dynabolt & Anchor', 'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Olahraga', 'name' => 'Tools & Perkakas', 'image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Mainan & Anak', 'name' => 'Paku', 'image' => 'https://images.unsplash.com/photo-1581092334247-154d02272fb8?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'HP & Tablet', 'name' => 'Klem & Bracket', 'image' => 'https://images.unsplash.com/photo-1581092580497-e0d23cbdf1dc?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Makanan & Minuman', 'name' => 'Chemical & Lem', 'image' => 'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?w=160&h=160&fit=crop&crop=center'],
            ['old_name' => 'Ibu & Bayi', 'name' => 'Safety & Abrasive', 'image' => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=160&h=160&fit=crop&crop=center'],
        ];

        foreach ($items as $item) {
            $category = MainCategory::query()
                ->where('name', $item['old_name'])
                ->orWhere('name', $item['name'])
                ->first();

            if ($category) {
                $category->update([
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'image' => $item['image'],
                ]);
                continue;
            }

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
