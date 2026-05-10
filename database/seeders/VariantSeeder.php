<?php

namespace Database\Seeders;

use App\Models\Variant;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [
            ['name' => 'Warna',         'value' => 'Putih'],
            ['name' => 'Warna',         'value' => 'Hitam'],
            ['name' => 'Warna',         'value' => 'Merah'],
            ['name' => 'Warna',         'value' => 'Biru'],
            ['name' => 'Warna',         'value' => 'Hijau'],
            ['name' => 'Warna',         'value' => 'Kuning'],
            ['name' => 'Warna',         'value' => 'Abu-abu'],
            ['name' => 'Warna',         'value' => 'Pink'],
            ['name' => 'Warna',         'value' => 'Ungu'],
            ['name' => 'Warna',         'value' => 'Orange'],
            ['name' => 'Ukuran',        'value' => 'XS'],
            ['name' => 'Ukuran',        'value' => 'S'],
            ['name' => 'Ukuran',        'value' => 'M'],
            ['name' => 'Ukuran',        'value' => 'L'],
            ['name' => 'Ukuran',        'value' => 'XL'],
            ['name' => 'Ukuran',        'value' => 'XXL'],
            ['name' => 'Ukuran',        'value' => 'XXXL'],
            ['name' => 'Ukuran Sepatu', 'value' => '36'],
            ['name' => 'Ukuran Sepatu', 'value' => '37'],
            ['name' => 'Ukuran Sepatu', 'value' => '38'],
            ['name' => 'Ukuran Sepatu', 'value' => '39'],
            ['name' => 'Ukuran Sepatu', 'value' => '40'],
            ['name' => 'Ukuran Sepatu', 'value' => '41'],
            ['name' => 'Ukuran Sepatu', 'value' => '42'],
            ['name' => 'Ukuran Sepatu', 'value' => '43'],
            ['name' => 'Ukuran Sepatu', 'value' => '44'],
            ['name' => 'Kapasitas',     'value' => '32GB'],
            ['name' => 'Kapasitas',     'value' => '64GB'],
            ['name' => 'Kapasitas',     'value' => '128GB'],
            ['name' => 'Kapasitas',     'value' => '256GB'],
            ['name' => 'Kapasitas',     'value' => '512GB'],
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
    }
}
