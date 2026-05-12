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
            ['name' => 'Material',      'value' => 'Baja'],
            ['name' => 'Material',      'value' => 'Stainless 304'],
            ['name' => 'Material',      'value' => 'Galvanis'],
            ['name' => 'Grade',         'value' => '4.8'],
            ['name' => 'Grade',         'value' => '8.8'],
            ['name' => 'Grade',         'value' => '10.9'],
            ['name' => 'Grade',         'value' => '12.9'],
            ['name' => 'Kemasan',       'value' => 'Pcs'],
            ['name' => 'Kemasan',       'value' => 'Pack 100'],
            ['name' => 'Kemasan',       'value' => 'Dus'],
        ];

        $diameters = [
            'M1.6', 'M2', 'M2.5', 'M3', 'M4', 'M5', 'M6', 'M8', 'M10', 'M12',
            'M14', 'M16', 'M18', 'M20', 'M22', 'M24', 'M27', 'M30', 'M33', 'M36',
            'M39', 'M42', 'M45', 'M48', 'M52', 'M56', 'M60',
        ];

        $lengths = [
            '10mm', '12mm', '16mm', '20mm', '25mm', '30mm', '35mm', '40mm', '45mm', '50mm',
            '55mm', '60mm', '65mm', '70mm', '75mm', '80mm', '90mm', '100mm', '110mm', '120mm',
            '125mm', '130mm', '140mm', '150mm', '160mm', '170mm', '180mm', '190mm', '200mm',
            '220mm', '240mm', '260mm', '280mm', '300mm',
        ];

        $threadTypes = ['Full Drat', 'Half Drat'];

        foreach ($diameters as $diameter) {
            $variants[] = ['name' => 'Diameter', 'value' => $diameter];
        }

        foreach ($lengths as $length) {
            $variants[] = ['name' => 'Panjang', 'value' => $length];
        }

        foreach ($threadTypes as $threadType) {
            $variants[] = ['name' => 'Tipe Drat', 'value' => $threadType];
        }

        $bautMur109Specs = [
            'M10' => [
                ['20mm', 'Full Drat'],
                ['25mm', 'Full Drat'],
                ['30mm', 'Full Drat'],
                ['35mm', 'Half Drat'],
                ['40mm', 'Half Drat'],
                ['45mm', 'Half Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
            ],
            'M12' => [
                ['25mm', 'Full Drat'],
                ['30mm', 'Full Drat'],
                ['35mm', 'Full Drat'],
                ['40mm', 'Half Drat'],
                ['45mm', 'Half Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
            ],
            'M14' => [
                ['30mm', 'Full Drat'],
                ['35mm', 'Full Drat'],
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
            ],
            'M16' => [
                ['30mm', 'Full Drat'],
                ['35mm', 'Full Drat'],
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Half Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
                ['110mm', 'Half Drat'],
                ['120mm', 'Half Drat'],
                ['125mm', 'Half Drat'],
                ['130mm', 'Half Drat'],
                ['140mm', 'Half Drat'],
                ['150mm', 'Half Drat'],
                ['160mm', 'Half Drat'],
                ['170mm', 'Half Drat'],
                ['180mm', 'Half Drat'],
            ],
            'M18' => [
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Full Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
                ['110mm', 'Half Drat'],
                ['120mm', 'Half Drat'],
                ['125mm', 'Half Drat'],
                ['130mm', 'Half Drat'],
                ['140mm', 'Half Drat'],
                ['150mm', 'Half Drat'],
            ],
            'M20' => [
                ['40mm', 'Full Drat'],
                ['45mm', 'Full Drat'],
                ['50mm', 'Full Drat'],
                ['55mm', 'Half Drat'],
                ['60mm', 'Half Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
                ['90mm', 'Half Drat'],
                ['100mm', 'Half Drat'],
                ['110mm', 'Half Drat'],
            ],
            'M22' => [
                ['50mm', 'Full Drat'],
                ['55mm', 'Full Drat'],
                ['60mm', 'Full Drat'],
                ['65mm', 'Half Drat'],
                ['70mm', 'Half Drat'],
                ['75mm', 'Half Drat'],
                ['80mm', 'Half Drat'],
            ],
        ];

        foreach ($bautMur109Specs as $diameter => $items) {
            foreach ($items as [$panjang, $tipeDrat]) {
                $variants[] = [
                    'name' => 'Varian SKU',
                    'value' => trim($diameter . ' x ' . $panjang . ' - ' . $tipeDrat),
                ];
            }
        }

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
