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
        ];

        foreach ($variants as $data) {
            Variant::updateOrCreate(
                ['name' => $data['name'], 'value' => $data['value']]
            );
        }
    }
}
