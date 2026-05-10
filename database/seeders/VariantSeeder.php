<?php

namespace Database\Seeders;

use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [
            ['name' => 'Diameter',      'value' => 'M4'],
            ['name' => 'Diameter',      'value' => 'M5'],
            ['name' => 'Diameter',      'value' => 'M6'],
            ['name' => 'Diameter',      'value' => 'M8'],
            ['name' => 'Diameter',      'value' => 'M10'],
            ['name' => 'Diameter',      'value' => 'M12'],
            ['name' => 'Panjang',       'value' => '16mm'],
            ['name' => 'Panjang',       'value' => '25mm'],
            ['name' => 'Panjang',       'value' => '50mm'],
            ['name' => 'Panjang',       'value' => '75mm'],
            ['name' => 'Panjang',       'value' => '100mm'],
            ['name' => 'Material',      'value' => 'Baja'],
            ['name' => 'Material',      'value' => 'Stainless 304'],
            ['name' => 'Material',      'value' => 'Galvanis'],
            ['name' => 'Grade',         'value' => '4.8'],
            ['name' => 'Grade',         'value' => '8.8'],
            ['name' => 'Grade',         'value' => '12.9'],
            ['name' => 'Kemasan',       'value' => 'Pcs'],
            ['name' => 'Kemasan',       'value' => 'Pack 100'],
            ['name' => 'Kemasan',       'value' => 'Dus'],
        ];

        foreach ($variants as $data) {
            Variant::updateOrCreate(
                ['name' => $data['name'], 'value' => $data['value']]
            );
        }

        $legacyVariantIds = Variant::whereNotIn('name', collect($variants)->pluck('name')->unique()->all())->pluck('id');

        DB::table('product_variants')->whereIn('variant_id', $legacyVariantIds)->delete();
        Variant::whereIn('id', $legacyVariantIds)->delete();
    }
}
