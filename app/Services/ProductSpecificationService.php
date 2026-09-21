<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\SpecificationTemplate;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductSpecificationService
{
    public function resolve(?int $categoryDetailId, ?int $mainCategoryId, ?int $legacyCategoryId = null): ?SpecificationTemplate
    {
        $templateId = $categoryDetailId
            ? CategoryDetail::query()->whereKey($categoryDetailId)->value('specification_template_id')
            : null;

        if (! $templateId && $mainCategoryId) {
            $templateId = MainCategory::query()->whereKey($mainCategoryId)->value('default_specification_template_id');
        }

        if (! $templateId && $legacyCategoryId) {
            $templateId = Category::query()->whereKey($legacyCategoryId)->value('specification_template_id');
        }

        return $templateId
            ? SpecificationTemplate::query()
                ->whereKey($templateId)
                ->where('is_active', true)
                ->with(['fields' => fn ($query) => $query->where('is_active', true)->with('definition'), 'options' => fn ($query) => $query->where('is_active', true)])
                ->first()
            : null;
    }

    public function resolveForProduct(Product $product): ?SpecificationTemplate
    {
        return $this->resolve($product->category_detail_id, $product->main_category_id, $product->category_id);
    }

    public function templatePayload(): array
    {
        return SpecificationTemplate::query()
            ->where('is_active', true)
            ->with(['fields' => fn ($query) => $query->where('is_active', true)->with('definition'), 'options' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (SpecificationTemplate $template) => [(string) $template->id => $this->serialize($template)])
            ->all();
    }

    public function serialize(SpecificationTemplate $template): array
    {
        $options = $template->options->groupBy('attribute_definition_id');

        return [
            'id' => (int) $template->id,
            'code' => (string) $template->code,
            'name' => (string) $template->name,
            'fields' => $template->fields->map(function ($field) use ($options) {
                $definition = $field->definition;

                return [
                    'id' => (int) $definition->id,
                    'code' => (string) $definition->code,
                    'name' => $field->label(),
                    'dataType' => (string) $definition->data_type,
                    'inputType' => (string) $field->input_type,
                    'unit' => $definition->unit,
                    'required' => (bool) $field->is_required,
                    'filterable' => (bool) $field->is_filterable,
                    'affectsVariant' => (bool) $field->affects_variant,
                    'allowCustomValue' => (bool) $field->allow_custom_value,
                    'sortOrder' => (int) $field->sort_order,
                    'options' => $options->get($definition->id, collect())->pluck('value')->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    public function validateVariants(?SpecificationTemplate $template, array $variants): void
    {
        if (! $template) {
            return;
        }

        $fields = $template->fields->keyBy('attribute_definition_id');
        $options = $template->options->groupBy('attribute_definition_id')
            ->map(fn (Collection $items) => $items->pluck('value')->map(fn ($value) => mb_strtolower(trim((string) $value)))->all());
        $seen = [];
        $errors = [];

        foreach ($variants as $variantIndex => $variant) {
            $submitted = collect($variant['attributes'] ?? [])
                ->filter(fn ($attribute) => is_array($attribute) && ! empty($attribute['attribute_definition_id']))
                ->keyBy(fn ($attribute) => (int) $attribute['attribute_definition_id']);

            foreach ($submitted as $definitionId => $attribute) {
                $hasValue = trim((string) ($attribute['value_text'] ?? '')) !== ''
                    || ($attribute['value_number'] ?? '') !== '';
                if ($hasValue && ! $fields->has((int) $definitionId)) {
                    $errors["variants.{$variantIndex}.attributes.{$definitionId}"] = 'Atribut tidak diizinkan untuk template '.$template->name.'.';
                }
            }

            $identity = [];
            foreach ($fields as $definitionId => $field) {
                $attribute = $submitted->get((int) $definitionId, []);
                $value = $this->attributeValue($attribute, $field->definition?->data_type === 'number');

                if ($field->is_required && $value === '') {
                    $errors["variants.{$variantIndex}.attributes.{$definitionId}"] = $field->label().' wajib diisi.';

                    continue;
                }

                if ($value !== '' && $field->input_type === 'select' && ! $field->allow_custom_value) {
                    $allowed = $options->get((int) $definitionId, []);
                    if (! in_array(mb_strtolower($value), $allowed, true)) {
                        $errors["variants.{$variantIndex}.attributes.{$definitionId}"] = $field->label().' harus dipilih dari daftar yang tersedia.';
                    }
                }

                if ($field->affects_variant) {
                    $identity[] = mb_strtolower($value);
                }
            }

            $fingerprint = implode('|', $identity);
            if ($fingerprint !== '' && isset($seen[$fingerprint])) {
                $errors["variants.{$variantIndex}"] = 'Kombinasi spesifikasi sama dengan varian '.($seen[$fingerprint] + 1).'.';
            }
            $seen[$fingerprint] = $variantIndex;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function buildLabel(array $attributes, Collection $definitions, ?SpecificationTemplate $template): string
    {
        $submitted = collect($attributes)
            ->filter(fn ($attribute) => is_array($attribute) && ! empty($attribute['attribute_definition_id']))
            ->keyBy(fn ($attribute) => (int) $attribute['attribute_definition_id']);

        $orderedDefinitions = $template
            ? $template->fields->where('affects_variant', true)->map(fn ($field) => $field->definition)->filter()
            : $definitions->sortBy('sort_order');
        $segments = [];

        foreach ($orderedDefinitions as $definition) {
            $attribute = $submitted->get((int) $definition->id);
            if (! $attribute) {
                continue;
            }
            $value = $this->attributeValue($attribute, $definition->data_type === 'number');
            if ($value === '') {
                continue;
            }
            $unit = trim((string) $definition->unit);
            $segments[] = $unit !== '' && ! str_ends_with(mb_strtolower($value), mb_strtolower($unit))
                ? $value.$unit
                : $value;
        }

        if ($segments === []) {
            return 'Standar';
        }

        return implode(' - ', $segments);
    }

    private function attributeValue(array $attribute, bool $isNumber): string
    {
        $raw = $isNumber ? ($attribute['value_number'] ?? '') : ($attribute['value_text'] ?? '');
        $value = trim((string) $raw);
        if (! $isNumber || $value === '') {
            return $value;
        }

        $normalized = str_replace(',', '.', $value);

        return is_numeric($normalized)
            ? rtrim(rtrim(number_format((float) $normalized, 3, '.', ''), '0'), '.')
            : $value;
    }
}
