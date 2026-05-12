<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ProductImportTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            [
                'product_name',
                'category_detail',
                'status',
                'description',
                'is_redeem_product',
                'redeem_points',
                'price',
                'stock',
                'weight_grams',
                'length_cm',
                'width_cm',
                'height_cm',
                'diameter',
                'length_mm',
                'thread_type',
                'grade',
                'material',
            ],
            [
                'Baut Hex M8 x 25mm Galvanis',
                'Baut Hex',
                'active',
                'Contoh produk dari template import',
                '0',
                '',
                '2500',
                '100',
                '150',
                '2',
                '2',
                '5',
                'M8',
                '25',
                'Full Thread',
                '8.8',
                'Baja Galvanis',
            ],
        ];
    }
}
