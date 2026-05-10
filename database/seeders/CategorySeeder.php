<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $legacyMap = [
            'Rumah Tangga' => 'Baut',
            'Fashion Pria' => 'Mur',
            'Fashion Wanita' => 'Ring & Washer',
            'Elektronik' => 'Sekrup',
            'Kecantikan' => 'Dynabolt & Anchor',
            'Olahraga' => 'Tools & Perkakas',
            'Mainan & Anak' => 'Paku',
            'HP & Tablet' => 'Klem & Bracket',
            'Makanan & Minuman' => 'Chemical & Lem',
            'Ibu & Bayi' => 'Safety & Abrasive',
        ];

        foreach ($legacyMap as $oldName => $newName) {
            Category::query()->where('name', $oldName)->update(['name' => $newName]);
        }

        $categories = [
            'Baut',
            'Mur',
            'Ring & Washer',
            'Sekrup',
            'Dynabolt & Anchor',
            'Tools & Perkakas',
            'Paku',
            'Klem & Bracket',
            'Chemical & Lem',
            'Safety & Abrasive',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(['name' => $name]);
        }
    }
}
