<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ProductDetailResource;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    /**
     * GET /api/v1/companies/{companySlug}/products
     */
    public function index(Request $request, string $companySlug): JsonResponse
    {
        $company = $this->resolveCompany($companySlug);

        $payload = $this->cached('products.index', $company, $request, function () use ($company, $request) {
            $query = Product::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->with(['mainCategory', 'categoryDetail', 'productVariants']);

            $this->applyFilters($query, $request);
            $this->applySort($query, $request);

            $paginator = $query->paginate($this->perPage($request))->withQueryString();

            return ProductListResource::collection($paginator)
                ->response($request)
                ->getData(true);
        });

        return response()->json($payload);
    }

    /**
     * GET /api/v1/companies/{companySlug}/products/{identifier}
     * `identifier` boleh id numerik atau slug.
     */
    public function show(Request $request, string $companySlug, string $identifier): JsonResponse
    {
        $company = $this->resolveCompany($companySlug);

        $payload = $this->cached('products.show:'.$identifier, $company, $request, function () use ($company, $identifier, $request) {
            $product = Product::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->with([
                    'mainCategory',
                    'categoryDetail',
                    'productVariants.variant',
                    'productVariants.attributeValues.definition',
                ])
                ->where(function ($q) use ($identifier) {
                    $q->where('slug', $identifier);
                    if (ctype_digit($identifier)) {
                        $q->orWhere('id', (int) $identifier);
                    }
                })
                ->firstOrFail();

            return (new ProductDetailResource($product))->response($request)->getData(true);
        });

        return response()->json($payload);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('main_category_id')) {
            $query->where('main_category_id', (int) $request->query('main_category_id'));
        }

        if ($request->filled('category_detail_id')) {
            $query->where('category_detail_id', (int) $request->query('category_detail_id'));
        }

        // category_id/category_slug menargetkan taksonomi utama (main / detail).
        if ($request->filled('category_id')) {
            $id = (int) $request->query('category_id');
            $query->where(function ($q) use ($id) {
                $q->where('main_category_id', $id)->orWhere('category_detail_id', $id);
            });
        }

        if ($request->filled('category_slug')) {
            $slug = (string) $request->query('category_slug');
            $query->where(function ($q) use ($slug) {
                $q->whereHas('mainCategory', fn ($mq) => $mq->where('slug', $slug))
                    ->orWhereHas('categoryDetail', fn ($cq) => $cq->where('slug', $slug));
            });
        }

        if ($request->filled('search')) {
            $term = strtolower(trim((string) $request->query('search')));
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) like ?', ['%'.$term.'%'])
                    ->orWhereHas('productVariants', fn ($vq) => $vq->whereRaw('LOWER(sku) like ?', ['%'.$term.'%']));
            });
        }

        if ($request->boolean('in_stock')) {
            $query->whereHas('productVariants', fn ($vq) => $vq->where('stock', '>', 0));
        }
    }

    private function applySort($query, Request $request): void
    {
        match ((string) $request->query('sort', 'newest')) {
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy(
                Product::query()
                    ->from('product_variants')
                    ->selectRaw('MIN(price)')
                    ->whereColumn('product_variants.product_id', 'products.id')
            ),
            'price_desc' => $query->orderByDesc(
                Product::query()
                    ->from('product_variants')
                    ->selectRaw('MIN(price)')
                    ->whereColumn('product_variants.product_id', 'products.id')
            ),
            default => $query->orderByDesc('id'),
        };
    }
}
