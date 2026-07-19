<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Product;
use App\Support\CatalogImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detail produk lengkap dengan varian. TIDAK memuat stok (angka),
 * is_redeem_product/redeem_points, atau company_id mentah. Lihat PRD §4.
 *
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variants = $this->productVariants;
        $prices = $variants->pluck('price')->filter(fn ($p) => $p !== null)->map(fn ($p) => (int) $p);

        $images = $variants
            ->pluck('image')
            ->map(fn ($img) => CatalogImageUrl::make($img))
            ->filter()
            ->unique()
            ->values();

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'slug' => (string) $this->slug,
            'description' => $this->description,
            'category' => [
                'main_category_id' => $this->main_category_id !== null ? (int) $this->main_category_id : null,
                'main_category' => $this->mainCategory?->name,
                'category_detail_id' => $this->category_detail_id !== null ? (int) $this->category_detail_id : null,
                'category_detail' => $this->categoryDetail?->name,
            ],
            'price_min' => $prices->isNotEmpty() ? $prices->min() : null,
            'price_max' => $prices->isNotEmpty() ? $prices->max() : null,
            'in_stock' => $variants->contains(fn ($v) => (int) ($v->stock ?? 0) > 0),
            'image' => CatalogImageUrl::make($this->firstAvailableImagePath()),
            'images' => $images,
            'variants' => ProductVariantResource::collection($variants),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
