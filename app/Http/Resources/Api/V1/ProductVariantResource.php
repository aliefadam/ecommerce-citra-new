<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProductVariant;
use App\Support\CatalogImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Varian produk. Harga = harga normal varian (tanpa flash sale).
 * Stok angka TIDAK diekspos — hanya `in_stock` boolean. Lihat PRD §4.
 *
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'sku' => $this->sku,
            'price' => $this->price !== null ? (int) $this->price : null,
            'in_stock' => (int) ($this->stock ?? 0) > 0,
            'image' => CatalogImageUrl::make($this->image),
            'weight_grams' => $this->weight_grams !== null ? (int) $this->weight_grams : null,
            'dimensions' => [
                'length_cm' => $this->length_cm !== null ? (float) $this->length_cm : null,
                'width_cm' => $this->width_cm !== null ? (float) $this->width_cm : null,
                'height_cm' => $this->height_cm !== null ? (float) $this->height_cm : null,
            ],
            'attributes' => $this->attributeValues
                ->filter(fn ($attr) => $attr->definition !== null)
                ->map(function ($attr) {
                    $value = $attr->value_text;
                    if (($value === null || $value === '') && $attr->value_number !== null) {
                        $value = rtrim(rtrim((string) $attr->value_number, '0'), '.');
                    }

                    return [
                        'code' => (string) $attr->definition->code,
                        'name' => (string) $attr->definition->name,
                        'value' => $value !== null && $value !== '' ? (string) $value : null,
                    ];
                })
                ->filter(fn ($a) => $a['value'] !== null)
                ->values(),
            'attribute_summary' => $this->attributeSummary(),
        ];
    }
}
