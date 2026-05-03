<?php

namespace Database\Seeders;

use App\Models\CategoryDetail;
use App\Models\MainCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $map = [
            'Rumah Tangga' => ['Dekorasi', 'Kamar Mandi', 'Kebutuhan Rumah', 'Tempat Penyimpanan'],
            'Fashion Pria' => ['Kemeja', 'Kaos', 'Celana Chino', 'Sepatu Pria', 'Tas Pria'],
            'Fashion Wanita' => ['Blouse', 'Dress', 'Rok', 'Tas Wanita'],
            'Elektronik' => ['Laptop', 'Audio', 'Wearable', 'Kamera'],
            'Kecantikan' => ['Skincare', 'Body Care', 'Makeup'],
            'Olahraga' => ['Sepatu Lari', 'Peralatan Gym', 'Aksesoris Olahraga'],
        ];

        foreach ($map as $mainName => $details) {
            $main = MainCategory::query()->where('name', $mainName)->first();
            if (!$main) {
                continue;
            }
            foreach ($details as $detailName) {
                CategoryDetail::updateOrCreate(
                    ['main_category_id' => $main->id, 'name' => $detailName],
                    ['slug' => Str::slug($mainName . '-' . $detailName)]
                );
            }
        }
    }
}
