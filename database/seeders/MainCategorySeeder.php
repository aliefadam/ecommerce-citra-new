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
            'Rumah Tangga',
            'Fashion Pria',
            'Fashion Wanita',
            'Elektronik',
            'Kecantikan',
            'Olahraga',
            'Mainan & Anak',
            'HP & Tablet',
            'Makanan & Minuman',
            'Ibu & Bayi',
        ];

        foreach ($items as $name) {
            MainCategory::updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }
    }
}
