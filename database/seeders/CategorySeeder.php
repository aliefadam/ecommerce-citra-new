<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
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

        foreach ($categories as $name) {
            Category::updateOrCreate(['name' => $name]);
        }
    }
}
