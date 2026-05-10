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
            'Baut' => ['Baut Hex', 'Baut L', 'Baut Roofing', 'Baut Stainless', 'Baut HTB'],
            'Mur' => ['Mur Hex', 'Mur Nyloc', 'Mur Flange', 'Mur Kuping', 'Mur Stainless'],
            'Ring & Washer' => ['Ring Plat', 'Ring Per', 'Washer Stainless', 'Washer Galvanis'],
            'Sekrup' => ['Sekrup Kayu', 'Sekrup Gypsum', 'Sekrup SDS', 'Sekrup Mesin'],
            'Dynabolt & Anchor' => ['Dynabolt', 'Fischer', 'Drop In Anchor', 'Chemical Anchor'],
            'Tools & Perkakas' => ['Kunci Pas', 'Kunci L', 'Mata Bor', 'Obeng', 'Tang'],
            'Paku' => ['Paku Beton', 'Paku Kayu', 'Paku Rivet', 'Paku Tembak'],
            'Klem & Bracket' => ['Klem Pipa', 'Bracket L', 'U Bolt', 'Clamp Stainless'],
            'Chemical & Lem' => ['Lem Besi', 'Sealant', 'Anti Karat', 'Threadlocker'],
            'Safety & Abrasive' => ['Sarung Tangan', 'Kacamata Safety', 'Mata Gerinda', 'Amplas'],
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
