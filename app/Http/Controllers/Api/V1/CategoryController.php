<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\CategoryDetail;
use App\Models\Company;
use App\Models\MainCategory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    /**
     * GET /api/v1/companies/{companySlug}/categories
     * Flat + parent_id. Hanya kategori yang punya produk aktif milik perusahaan.
     */
    public function index(Request $request, string $companySlug): JsonResponse
    {
        $company = $this->resolveCompany($companySlug);

        $payload = $this->cached('categories.index', $company, $request, function () use ($company, $request) {
            $withCounts = $request->boolean('with_counts');
            $items = $this->buildFlatCategories($company, $withCounts);

            return CategoryResource::collection($items)->response($request)->getData(true);
        });

        return response()->json($payload);
    }

    /**
     * GET /api/v1/companies/{companySlug}/categories/{identifier}
     * Detail satu kategori + anak-anaknya (untuk main category).
     */
    public function show(Request $request, string $companySlug, string $identifier): JsonResponse
    {
        $company = $this->resolveCompany($companySlug);

        $payload = $this->cached('categories.show:'.$identifier, $company, $request, function () use ($company, $identifier) {
            [$mainCounts, $detailCounts] = $this->categoryCounts($company);

            // Cari sebagai main category dulu, lalu category detail.
            $main = MainCategory::query()
                ->whereKey(array_keys($mainCounts->all()))
                ->where(fn ($q) => $this->matchIdentifier($q, $identifier))
                ->first();

            if ($main) {
                $children = CategoryDetail::query()
                    ->where('main_category_id', $main->id)
                    ->whereKey(array_keys($detailCounts->all()))
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($d) => $this->detailToArray($d, (int) ($detailCounts[$d->id] ?? 0)));

                return [
                    'data' => (new CategoryResource($this->mainToArray($main, (int) ($mainCounts[$main->id] ?? 0))))->resolve()
                        + ['children' => $children->values()->all()],
                ];
            }

            $detail = CategoryDetail::query()
                ->whereKey(array_keys($detailCounts->all()))
                ->where(fn ($q) => $this->matchIdentifier($q, $identifier))
                ->firstOrFail();

            return [
                'data' => (new CategoryResource($this->detailToArray($detail, (int) ($detailCounts[$detail->id] ?? 0))))->resolve()
                    + ['children' => []],
            ];
        });

        return response()->json($payload);
    }

    /**
     * Bangun daftar flat: main categories (parent_id null) + category details
     * (parent_id = main_category_id), hanya yang punya produk aktif.
     */
    private function buildFlatCategories(Company $company, bool $withCounts): \Illuminate\Support\Collection
    {
        [$mainCounts, $detailCounts] = $this->categoryCounts($company);

        $mains = MainCategory::query()
            ->whereKey(array_keys($mainCounts->all()))
            ->orderBy('name')
            ->get()
            ->map(fn ($m) => $this->mainToArray($m, (int) ($mainCounts[$m->id] ?? 0), $withCounts));

        $details = CategoryDetail::query()
            ->whereKey(array_keys($detailCounts->all()))
            ->orderBy('name')
            ->get()
            ->map(fn ($d) => $this->detailToArray($d, (int) ($detailCounts[$d->id] ?? 0), $withCounts));

        return $mains->concat($details)->values();
    }

    /**
     * @return array{0:\Illuminate\Support\Collection,1:\Illuminate\Support\Collection}
     */
    private function categoryCounts(Company $company): array
    {
        $base = fn () => Product::query()
            ->where('company_id', $company->id)
            ->where('status', 'active');

        $mainCounts = $base()
            ->whereNotNull('main_category_id')
            ->selectRaw('main_category_id, COUNT(*) as c')
            ->groupBy('main_category_id')
            ->pluck('c', 'main_category_id');

        $detailCounts = $base()
            ->whereNotNull('category_detail_id')
            ->selectRaw('category_detail_id, COUNT(*) as c')
            ->groupBy('category_detail_id')
            ->pluck('c', 'category_detail_id');

        return [$mainCounts, $detailCounts];
    }

    private function matchIdentifier($query, string $identifier): void
    {
        $query->where('slug', $identifier);
        if (ctype_digit($identifier)) {
            $query->orWhere('id', (int) $identifier);
        }
    }

    private function mainToArray(MainCategory $main, int $count, bool $withCounts = true): array
    {
        $data = [
            'id' => $main->id,
            'type' => 'main',
            'name' => $main->name,
            'slug' => $main->slug,
            'parent_id' => null,
            'image' => $main->image,
        ];

        if ($withCounts) {
            $data['products_count'] = $count;
        }

        return $data;
    }

    private function detailToArray(CategoryDetail $detail, int $count, bool $withCounts = true): array
    {
        $data = [
            'id' => $detail->id,
            'type' => 'detail',
            'name' => $detail->name,
            'slug' => $detail->slug,
            'parent_id' => $detail->main_category_id,
        ];

        if ($withCounts) {
            $data['products_count'] = $count;
        }

        return $data;
    }
}
