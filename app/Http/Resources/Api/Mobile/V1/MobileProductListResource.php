<?php

namespace App\Http\Resources\Api\Mobile\V1;

use App\Models\Product;
use App\Support\CatalogImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class MobileProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variants = $this->productVariants;
        $prices = $variants->pluck('price')->filter(fn ($price) => $price !== null)->map(fn ($price) => (int) $price);
        $firstVariant = $variants->sortBy('price')->first();

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'slug' => (string) $this->slug,
            'image' => CatalogImageUrl::make($this->firstAvailableImagePath()),
            'price_min' => $prices->isNotEmpty() ? $prices->min() : null,
            'price_max' => $prices->isNotEmpty() ? $prices->max() : null,
            'in_stock' => $variants->contains(fn ($variant) => (int) ($variant->stock ?? 0) > 0),
            'sku' => $firstVariant?->sku,
            'variant_summary' => $firstVariant?->attributeSummary(),
            'seller' => [
                'name' => (string) ($this->company?->name ?? ''),
                'slug' => (string) ($this->company?->slug ?? ''),
            ],
            'category' => [
                'name' => (string) ($this->categoryDetail?->name ?? $this->mainCategory?->name ?? ''),
                'slug' => (string) ($this->categoryDetail?->slug ?? $this->mainCategory?->slug ?? ''),
                'parent_name' => (string) ($this->mainCategory?->name ?? ''),
                'parent_slug' => (string) ($this->mainCategory?->slug ?? ''),
            ],
        ];
    }
}
