<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
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
