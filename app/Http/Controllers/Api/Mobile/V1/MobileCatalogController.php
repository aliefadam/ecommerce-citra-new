<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\V1\MobileProductDetailResource;
use App\Http\Resources\Api\Mobile\V1\MobileProductListResource;
use App\Models\Banner;
use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TransactionDetail;
use App\Support\CatalogImageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MobileCatalogController extends Controller
{
    private const SOLD_TRANSACTION_STATUSES = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];

    public function home(Request $request): JsonResponse
    {
        $products = $this->baseProductQuery()
            ->latest('id')
            ->limit(8)
            ->get();

        return response()->json([
            'data' => [
                'hero_banners' => $this->activeBanners('mobile_hero', 6),
                'banners' => $this->activeBanners('carousel', 5),
                'categories' => $this->categoryTree(8),
                'featured_products' => MobileProductListResource::collection($products)->resolve($request),
            ],
        ]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => $this->categoryTree()]);
    }

    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:160'],
            'seller' => ['nullable', 'string', 'max:160'],
            'in_stock' => ['nullable', 'boolean'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0', 'gte:min_price'],
            'sort' => ['nullable', Rule::in(['newest', 'best_selling', 'name_asc', 'name_desc', 'price_asc', 'price_desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        $query = $this->baseProductQuery();
        $this->applyFilters($query, $validated);
        $this->applySort($query, $validated['sort'] ?? 'newest');

        $paginator = $query->paginate((int) ($validated['per_page'] ?? 12))->withQueryString();

        return MobileProductListResource::collection($paginator)->response();
    }

    public function product(Request $request, string $slug): MobileProductDetailResource
    {
        $product = $this->baseProductQuery(true)
            ->where('slug', $slug)
            ->firstOrFail();

        return new MobileProductDetailResource($product);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $term = mb_strtolower(trim($validated['q']));
        $query = $this->baseProductQuery();
        $this->applySearch($query, $term);

        return response()->json([
            'data' => MobileProductListResource::collection($query->latest('id')->limit(8)->get())->resolve($request),
        ]);
    }

    private function baseProductQuery(bool $detail = false): Builder
    {
        $relations = [
            'company:id,name,slug',
            'mainCategory',
            'categoryDetail',
            'productVariants.variant',
            'productVariants.attributeValues.definition',
        ];

        if ($detail) {
            $relations[] = 'mainCategory.defaultSpecificationTemplate.fields.definition';
            $relations[] = 'categoryDetail.specificationTemplate.fields.definition';
            $relations[] = 'productVariants.product';
        }

        return Product::query()
            ->storefrontVisible()
            ->whereNotNull('slug')
            ->whereHas('productVariants')
            ->with($relations);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (filled($filters['q'] ?? null)) {
            $this->applySearch($query, mb_strtolower(trim($filters['q'])));
        }

        if (filled($filters['category'] ?? null)) {
            $slug = $filters['category'];
            $query->where(fn ($category) => $category
                ->whereHas('mainCategory', fn ($main) => $main->where('slug', $slug))
                ->orWhereHas('categoryDetail', fn ($detail) => $detail->where('slug', $slug)));
        }

        if (filled($filters['seller'] ?? null)) {
            $query->whereHas('company', fn ($company) => $company->where('slug', $filters['seller'])->where('is_active', true));
        }

        if (($filters['in_stock'] ?? false) === true || ($filters['in_stock'] ?? null) === '1') {
            $query->whereHas('productVariants', fn ($variant) => $variant->where('stock', '>', 0));
        }

        if (array_key_exists('min_price', $filters)) {
            $query->whereHas('productVariants', fn ($variant) => $variant->where('price', '>=', $filters['min_price']));
        }

        if (array_key_exists('max_price', $filters)) {
            $query->whereHas('productVariants', fn ($variant) => $variant->where('price', '<=', $filters['max_price']));
        }
    }

    private function applySearch(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';
        $query->where(function ($product) use ($like) {
            $product
                ->whereRaw('LOWER(name) like ?', [$like])
                ->orWhereRaw('LOWER(COALESCE(description, ?)) like ?', ['', $like])
                ->orWhereHas('company', fn ($company) => $company->whereRaw('LOWER(name) like ?', [$like]))
                ->orWhereHas('mainCategory', fn ($category) => $category->whereRaw('LOWER(name) like ?', [$like]))
                ->orWhereHas('categoryDetail', fn ($category) => $category->whereRaw('LOWER(name) like ?', [$like]))
                ->orWhereHas('productVariants', function ($variant) use ($like) {
                    $variant
                        ->whereRaw('LOWER(COALESCE(sku, ?)) like ?', ['', $like])
                        ->orWhereHas('variant', fn ($value) => $value
                            ->whereRaw('LOWER(name) like ?', [$like])
                            ->orWhereRaw('LOWER(value) like ?', [$like]))
                        ->orWhereHas('attributeValues', fn ($attribute) => $attribute
                            ->whereRaw('LOWER(COALESCE(value_text, ?)) like ?', ['', $like])
                            ->orWhereRaw('CAST(value_number AS CHAR) like ?', [$like]));
                });
        });
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'best_selling' => $query
                ->orderByDesc(TransactionDetail::query()
                    ->selectRaw('COALESCE(SUM(transaction_details.quantity), 0)')
                    ->whereColumn('transaction_details.product_id', 'products.id')
                    ->whereHas('transaction', fn ($transaction) => $transaction
                        ->whereIn(DB::raw('LOWER(status)'), self::SOLD_TRANSACTION_STATUSES)))
                ->latest('id'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy(ProductVariant::query()
                ->selectRaw('MIN(price)')
                ->whereColumn('product_variants.product_id', 'products.id')),
            'price_desc' => $query->orderByDesc(ProductVariant::query()
                ->selectRaw('MIN(price)')
                ->whereColumn('product_variants.product_id', 'products.id')),
            default => $query->latest('id'),
        };
    }

    private function categoryTree(?int $limit = null): array
    {
        $visibleProducts = Product::query()->storefrontVisible()->whereHas('productVariants');
        $mainCounts = (clone $visibleProducts)
            ->whereNotNull('main_category_id')
            ->selectRaw('main_category_id, COUNT(*) as aggregate')
            ->groupBy('main_category_id')
            ->pluck('aggregate', 'main_category_id');
        $detailCounts = (clone $visibleProducts)
            ->whereNotNull('category_detail_id')
            ->selectRaw('category_detail_id, COUNT(*) as aggregate')
            ->groupBy('category_detail_id')
            ->pluck('aggregate', 'category_detail_id');

        $query = MainCategory::query()
            ->whereKey($mainCounts->keys())
            ->with(['categoryDetails' => fn ($details) => $details->whereKey($detailCounts->keys())->orderBy('name')])
            ->orderBy('name');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()->map(fn (MainCategory $category) => [
            'id' => (int) $category->id,
            'name' => (string) $category->name,
            'slug' => (string) $category->slug,
            'image' => CatalogImageUrl::make($category->image),
            'products_count' => (int) ($mainCounts[$category->id] ?? 0),
            'children' => $category->categoryDetails->map(fn (CategoryDetail $detail) => [
                'id' => (int) $detail->id,
                'name' => (string) $detail->name,
                'slug' => (string) $detail->slug,
                'products_count' => (int) ($detailCounts[$detail->id] ?? 0),
            ])->values(),
        ])->values()->all();
    }

    private function activeBanners(string $type, int $limit)
    {
        return Banner::query()
            ->where('is_active', true)
            ->where('type', $type)
            ->where(fn ($query) => $query->whereNull('company_id')->orWhereHas('company', fn ($company) => $company->where('is_active', true)))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Banner $banner) => [
                'id' => (int) $banner->id,
                'title' => (string) ($banner->title ?? ''),
                'image' => CatalogImageUrl::make($banner->image),
                'target_url' => $this->safeTargetUrl($banner->target_url),
                'header_tone' => $banner->header_tone === 'dark' ? 'dark' : 'light',
            ])
            ->values();
    }

    private function safeTargetUrl(?string $target): ?string
    {
        $value = trim((string) $target);
        if ($value === '') {
            return null;
        }

        return str_starts_with($value, '/') && ! str_starts_with($value, '//') ? $value : null;
    }
}
