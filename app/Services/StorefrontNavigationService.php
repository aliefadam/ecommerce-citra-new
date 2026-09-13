<?php

namespace App\Services;

use App\Models\MainCategory;
use Illuminate\Support\Facades\Cache;

class StorefrontNavigationService
{
    private const CACHE_KEY = 'storefront.navigation.categories.v1';

    /**
     * @return array<int, array{key: string, name: string, url: string, columns: array<int, array{title: string, items: array<int, array{name: string, url: string}>}>}>
     */
    public function megaCategories(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(6), function (): array {
            return MainCategory::query()
                ->with(['categoryDetails' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get()
                ->map(function (MainCategory $parent): array {
                    $children = $parent->categoryDetails->values();
                    $chunkSize = max(1, (int) ceil(max(1, $children->count()) / 4));
                    $columns = $children
                        ->chunk($chunkSize)
                        ->take(4)
                        ->map(fn ($chunk): array => [
                            'title' => 'Kategori',
                            'items' => $chunk
                                ->map(fn ($child): array => [
                                    'name' => (string) $child->name,
                                    'url' => route('frontend.kategori', ['category' => $child->slug]),
                                ])
                                ->values()
                                ->all(),
                        ])
                        ->values()
                        ->all();

                    if ($columns === []) {
                        $columns[] = ['title' => 'Kategori', 'items' => []];
                    }

                    return [
                        'key' => (string) $parent->slug,
                        'name' => (string) $parent->name,
                        'url' => route('frontend.kategori', ['parent' => $parent->slug]),
                        'columns' => $columns,
                    ];
                })
                ->values()
                ->all();
        });
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
