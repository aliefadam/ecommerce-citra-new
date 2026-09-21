<?php

namespace App\Services;

use App\Models\SpecificationTemplate;
use Illuminate\Support\Str;

class CategorySpecificationTemplateService
{
    public function createFor(string $categoryName, string $categoryLevel): SpecificationTemplate
    {
        $categoryName = trim($categoryName);
        $baseCode = Str::slug($categoryName, '_') ?: 'kategori';
        $baseCode = Str::limit($baseCode, 90, '');
        $code = $baseCode;
        $suffix = 2;

        while (SpecificationTemplate::query()->where('code', $code)->exists()) {
            $suffixText = '_'.$suffix++;
            $code = Str::limit($baseCode, 100 - strlen($suffixText), '').$suffixText;
        }

        return SpecificationTemplate::create([
            'name' => Str::limit($categoryName.' - Spesifikasi', 100, ''),
            'code' => $code,
            'description' => "Template spesifikasi otomatis untuk {$categoryLevel} {$categoryName}.",
            'is_active' => true,
        ]);
    }
}
