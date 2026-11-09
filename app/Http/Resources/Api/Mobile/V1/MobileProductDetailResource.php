<?php

namespace App\Http\Resources\Api\Mobile\V1;

use App\Http\Resources\Api\V1\ProductVariantResource;
use App\Models\Product;
use App\Services\ProductSpecificationService;
use App\Support\CatalogImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class MobileProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variants = $this->productVariants;
        $prices = $variants->pluck('price')->filter(fn ($price) => $price !== null)->map(fn ($price) => (int) $price);
        $images = $variants
            ->map(fn ($variant) => CatalogImageUrl::make($variant->image ?: $this->firstAvailableImagePath()))
            ->filter()
            ->unique()
            ->values();

        $specifications = app(ProductSpecificationService::class)
            ->resolveForProduct($this->resource)
            ?->fields
            ->map(function ($field) use ($variants) {
                $definition = $field->definition;
                $values = $variants
                    ->flatMap(fn ($variant) => $variant->attributeValues
                        ->where('attribute_definition_id', $definition->id)
                        ->map(function ($attribute) {
                            $value = $attribute->value_text;
                            if (($value === null || $value === '') && $attribute->value_number !== null) {
                                $value = rtrim(rtrim((string) $attribute->value_number, '0'), '.');
                            }

                            return $value;
                        }))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->unique()
                    ->values();

                return [
                    'code' => (string) $definition->code,
                    'name' => $field->label(),
                    'unit' => $definition->unit,
                    'values' => $values,
                ];
            })
            ->filter(fn ($specification) => $specification['values']->isNotEmpty())
            ->values() ?? collect();

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'slug' => (string) $this->slug,
            'description' => (string) ($this->description ?? ''),
            'image' => CatalogImageUrl::make($this->firstAvailableImagePath()),
            'images' => $images,
            'specification_image' => CatalogImageUrl::make($this->specification_image),
            'price_min' => $prices->isNotEmpty() ? $prices->min() : null,
            'price_max' => $prices->isNotEmpty() ? $prices->max() : null,
            'in_stock' => $variants->contains(fn ($variant) => (int) ($variant->stock ?? 0) > 0),
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
            'specifications' => $specifications,
            'variants' => ProductVariantResource::collection($variants),
        ];
    }
}
