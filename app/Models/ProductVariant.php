<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'variant_id', 'sku', 'image', 'price', 'stock', 'weight_grams', 'length_cm', 'width_cm', 'height_cm', 'low_stock_threshold'];

    protected $casts = [
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function flashSaleItems()
    {
        return $this->hasMany(FlashSaleItem::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    public function attributeValue(string $code): ?string
    {
        $items = $this->relationLoaded('attributeValues')
            ? $this->attributeValues
            : $this->attributeValues()->with('definition')->get();

        $attribute = $items->first(function ($item) use ($code) {
            return strtolower((string) ($item->definition?->code ?? '')) === strtolower($code);
        });

        if (!$attribute) {
            return null;
        }

        if ($attribute->value_text !== null && $attribute->value_text !== '') {
            return (string) $attribute->value_text;
        }

        if ($attribute->value_number !== null && $attribute->value_number !== '') {
            $number = (string) $attribute->value_number;
            $trimmed = rtrim(rtrim($number, '0'), '.');

            return $trimmed !== '' ? $trimmed : $number;
        }

        return null;
    }

    public function attributeSummary(): string
    {
        $items = $this->relationLoaded('attributeValues')
            ? $this->attributeValues
            : $this->attributeValues()->with('definition')->get();

        $priority = array_flip(['diameter', 'length_mm', 'thread_type', 'grade', 'material']);
        $segments = $items
            ->filter(fn ($item) => $item->definition)
            ->sortBy(function ($item) use ($priority) {
                $code = strtolower((string) ($item->definition?->code ?? ''));

                return $priority[$code] ?? 999;
            })
            ->map(function ($item) {
                $code = strtolower((string) ($item->definition?->code ?? ''));
                $name = (string) ($item->definition?->name ?? 'Atribut');
                $value = $item->value_text;

                if (($value === null || $value === '') && $item->value_number !== null && $item->value_number !== '') {
                    $value = rtrim(rtrim((string) $item->value_number, '0'), '.');
                }

                $value = trim((string) $value);
                if ($value === '') {
                    return null;
                }

                if ($code === 'length_mm' && !str_ends_with(strtolower($value), 'mm')) {
                    $value .= 'mm';
                }

                return $name . ': ' . $value;
            })
            ->filter()
            ->values()
            ->all();

        if ($segments !== []) {
            return implode(' | ', $segments);
        }

        $fallback = trim(
            ((string) ($this->variant?->name ?? '')) .
            (((string) ($this->variant?->value ?? '')) !== '' ? ': ' . (string) $this->variant?->value : ''),
            ': '
        );

        return $fallback !== '' ? $fallback : 'Varian';
    }

    public function skuLabel(): string
    {
        $diameter = $this->attributeValue('diameter');
        $length = $this->attributeValue('length_mm');
        $threadType = $this->attributeValue('thread_type');

        $segments = array_filter([
            $diameter,
            $length ? $length . 'mm' : null,
            $threadType,
        ]);

        return $segments !== [] ? implode(' - ', $segments) : $this->attributeSummary();
    }
}
