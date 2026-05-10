<?php

namespace App\Http\Controllers;

use App\Models\FlashSaleItem;
use App\Models\MainCategory;
use App\Models\CategoryDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TransactionDetail;
use App\Models\TransactionProductReview;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\Wishlist;
use App\Models\Banner;
use App\Services\MembershipTierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    public function index()
    {
        $products = $this->buildFrontendProducts();
        $flashSaleData = $this->buildActiveFlashSaleData();
        $mainCategories = MainCategory::query()
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'slug' => (string) $c->slug,
                'name' => (string) $c->name,
                'icon' => $this->homeCategoryIcon((string) $c->name),
                'image' => $this->resolveMainCategoryImage($c->image),
            ])
            ->values()
            ->all();
        $homeCategories = collect($mainCategories)
            ->map(function ($cat) use ($products) {
                $count = collect($products['home'])->where('parentCategorySlug', $cat['slug'])->count();
                return [
                    'slug' => (string) $cat['slug'],
                    'name' => (string) $cat['name'],
                    'count' => (int) $count,
                ];
            })
            ->values()
            ->all();
        $allBanners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $normalizeBanner = function ($banner) {
            $image = (string) $banner->image;
            $imageUrl = (
                str_starts_with($image, 'http://') ||
                str_starts_with($image, 'https://') ||
                str_starts_with($image, '//') ||
                str_starts_with($image, 'data:')
            ) ? $image : asset('storage/' . ltrim($image, '/'));

            return [
                'image' => $imageUrl,
                'target_url' => (string) ($banner->target_url ?? ''),
                'sort_order' => (int) $banner->sort_order,
            ];
        };

        $banners = $allBanners->where('type', 'carousel')->map($normalizeBanner)->values()->all();
        $sideBanners = $allBanners->where('type', 'side')->map($normalizeBanner)->values()->all();

        return view('frontend.index', [
            'productsJson' => $products['home'],
            'flashSale' => $flashSaleData['featured'],
            'homeFilterCategories' => $homeCategories,
            'homeMainCategories' => $mainCategories,
            'bannersJson' => $banners,
            'sideBannersJson' => $sideBanners,
        ]);
    }

    public function kategori()
    {
        $products = $this->buildFrontendProducts();
        $categoryTree = MainCategory::query()
            ->with(['categoryDetails' => fn($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();
        $selectedParentSlug = (string) request()->query('parent', '');
        $selectedCategorySlug = (string) request()->query('category', '');

        $selectedCategory = $selectedCategorySlug !== ''
            ? CategoryDetail::query()->where('slug', $selectedCategorySlug)->first()
            : null;
        $selectedParent = $selectedParentSlug !== ''
            ? MainCategory::query()->where('slug', $selectedParentSlug)->first()
            : null;
        $selectedLabel = $selectedCategory?->name
            ?? $selectedParent?->name
            ?? 'Semua Kategori';

        return view('frontend.kategori', [
            'productsJson' => $products['category'],
            'categoryTree' => $categoryTree,
            'selectedLabel' => $selectedLabel,
            'selectedParentSlug' => $selectedParentSlug,
            'selectedCategorySlug' => $selectedCategorySlug,
        ]);
    }

    public function flashSale()
    {
        return view('frontend.flash-sale', [
            'flashSaleCampaigns' => $this->buildActiveFlashSaleData()['campaigns'],
        ]);
    }

    public function redeemPoint()
    {
        return view('frontend.redeem-point', [
            'redeemProducts' => $this->buildRedeemProducts(),
            'userPointBalance' => auth()->check() ? (int) (auth()->user()->point_balance ?? 0) : null,
        ]);
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        $productsQuery = Product::query()
            ->with([
                'mainCategory',
                'categoryDetail',
                'productVariants.variant',
                'flashSaleItems.flashSale',
            ])
            ->where('status', 'active');

        if ($query !== '') {
            $q = strtolower($query);
            $productsQuery->where(function ($builder) use ($q) {
                $builder
                    ->whereRaw('LOWER(name) like ?', ['%' . $q . '%'])
                    ->orWhereRaw('LOWER(description) like ?', ['%' . $q . '%'])
                    ->orWhereHas('mainCategory', fn($mq) => $mq->whereRaw('LOWER(name) like ?', ['%' . $q . '%']))
                    ->orWhereHas('categoryDetail', fn($cq) => $cq->whereRaw('LOWER(name) like ?', ['%' . $q . '%']))
                    ->orWhereHas('productVariants.variant', function ($vq) use ($q) {
                        $vq->whereRaw('LOWER(name) like ?', ['%' . $q . '%'])
                            ->orWhereRaw('LOWER(value) like ?', ['%' . $q . '%']);
                    });
            });
        }

        $products = $productsQuery->orderByDesc('id')->get();

        $soldByProduct = TransactionDetail::query()
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereIn('product_id', $products->pluck('id'))
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        $reviewsByProduct = TransactionProductReview::query()
            ->selectRaw('transaction_details.product_id as product_id, AVG(transaction_product_reviews.rating) as avg_rating, COUNT(*) as total_reviews')
            ->join('transaction_details', 'transaction_details.id', '=', 'transaction_product_reviews.transaction_detail_id')
            ->where('transaction_product_reviews.is_hidden', false)
            ->whereIn('transaction_details.product_id', $products->pluck('id'))
            ->groupBy('transaction_details.product_id')
            ->get()
            ->keyBy('product_id');

        $items = $products->map(function ($product) use ($soldByProduct, $reviewsByProduct) {
            $variant = $product->productVariants->first();
            if (!$variant) {
                return null;
            }

            $basePrice = (int) $variant->price;
            $flashItem = $product->flashSaleItems->first(function ($item) {
                $sale = $item->flashSale;
                if (!$sale || !$item->is_active || $sale->status !== 'active') {
                    return false;
                }
                $now = now();
                return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
            });

            $price = $flashItem ? (int) $flashItem->discount_price : $basePrice;
            $reviewAgg = $reviewsByProduct->get($product->id);
            $rating = $reviewAgg ? (float) $reviewAgg->avg_rating : 0;
            $reviews = $reviewAgg ? (int) $reviewAgg->total_reviews : 0;
            $sold = (int) ($soldByProduct[$product->id] ?? 0);

            return [
                'id' => (int) $product->id,
                'slug' => (string) $product->slug,
                'name' => (string) $product->name,
                'category' => (string) ($product->mainCategory?->name ?? '-'),
                'category_detail' => (string) ($product->categoryDetail?->name ?? ''),
                'variant' => trim(((string) ($variant->variant?->name ?? '')) . ': ' . ((string) ($variant->variant?->value ?? '')), ': '),
                'price' => $price,
                'originalPrice' => $basePrice,
                'sold' => $sold,
                'rating' => round($rating, 1),
                'reviews' => $reviews,
                'image' => $this->resolveProductVariantImageUrl($product, $variant, '400x400'),
            ];
        })->filter()->values()->all();

        return view('frontend.search-results', [
            'query' => $query,
            'results' => $items,
        ]);
    }

    public function detailProduk(?string $slug = null)
    {
        if (!$slug) {
            $firstProduct = Product::query()
                ->where('status', 'active')
                ->whereNotNull('slug')
                ->orderByDesc('id')
                ->first();

            abort_if(!$firstProduct, 404);

            return redirect()->route('frontend.detail-produk', ['slug' => $firstProduct->slug]);
        }

        $product = Product::with([
            'mainCategory',
            'categoryDetail',
            'productVariants.variant',
            'flashSaleItems.flashSale',
        ])
            ->where('status', 'active')
            ->where('slug', $slug)
            ->firstOrFail();

        $variant = $product->productVariants->first();
        abort_if(!$variant, 404);

        $variantGroups = $product->productVariants
            ->filter(fn($pv) => $pv->variant && filled($pv->variant->name) && filled($pv->variant->value))
            ->groupBy(fn($pv) => strtolower(trim($pv->variant->name)))
            ->map(function ($items, $groupKey) {
                return [
                    'key' => $groupKey,
                    'label' => $items->first()->variant->name,
                    'values' => $items
                        ->pluck('variant.value')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $galleryImages = $product->productVariants
            ->pluck('image')
            ->filter(fn($img) => filled($img))
            ->map(fn($img) => $this->normalizeImageUrl((string) $img, '700x700'))
            ->unique()
            ->values()
            ->all();

        if (empty($galleryImages)) {
            $galleryImages = ['https://via.placeholder.com/700x700?text=No+Image'];
        }

        $image = $galleryImages[0];

        $price = (int) $variant->price;
        $sold = (int) TransactionDetail::query()
            ->where('product_id', $product->id)
            ->whereHas('transaction', function ($q) {
                $q->whereIn(DB::raw('LOWER(status)'), [
                    'paid',
                    'settlement',
                    'capture',
                    'process',
                    'processing',
                    'kirim',
                    'shipping',
                    'shipped',
                    'selesai',
                    'completed',
                    'delivered',
                ]);
            })
            ->sum('quantity');

        $reviewBaseQuery = TransactionProductReview::query()
            ->join('transaction_details', 'transaction_details.id', '=', 'transaction_product_reviews.transaction_detail_id')
            ->where('transaction_product_reviews.is_hidden', false)
            ->where('transaction_details.product_id', $product->id);

        $reviewStats = (clone $reviewBaseQuery)
            ->selectRaw('AVG(transaction_product_reviews.rating) as avg_rating, COUNT(transaction_product_reviews.id) as total_reviews')
            ->first();

        $rating = $reviewStats && $reviewStats->total_reviews > 0 ? round((float) $reviewStats->avg_rating, 1) : 0.0;
        $reviews = $reviewStats ? (int) $reviewStats->total_reviews : 0;

        $ratingCounts = (clone $reviewBaseQuery)
            ->selectRaw('transaction_product_reviews.rating as rating, COUNT(transaction_product_reviews.id) as total')
            ->groupBy('transaction_product_reviews.rating')
            ->pluck('total', 'rating');

        $ratingDistribution = collect([5, 4, 3, 2, 1])->map(function ($star) use ($ratingCounts, $reviews) {
            $count = (int) ($ratingCounts[$star] ?? 0);
            $percent = $reviews > 0 ? (int) round(($count / $reviews) * 100) : 0;
            return [
                'star' => $star,
                'count' => $count,
                'percent' => $percent,
            ];
        })->values()->all();

        $reviewItems = TransactionProductReview::query()
            ->with(['user:id,name', 'transactionDetail:id,variant_name'])
            ->where('is_hidden', false)
            ->whereHas('transactionDetail', fn($q) => $q->where('product_id', $product->id))
            ->latest()
            ->get()
            ->map(function ($review) {
                $photos = collect((array) ($review->photos ?? []))
                    ->map(function ($photo) {
                        $photo = (string) $photo;
                        if ($photo === '') {
                            return null;
                        }
                        if (
                            str_starts_with($photo, 'http://') ||
                            str_starts_with($photo, 'https://') ||
                            str_starts_with($photo, '//') ||
                            str_starts_with($photo, 'data:') ||
                            str_starts_with($photo, '/')
                        ) {
                            return $photo;
                        }
                        return asset(ltrim($photo, '/'));
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'name' => (string) ($review->user->name ?? 'User'),
                    'rating' => (int) $review->rating,
                    'date' => $review->created_at ? $review->created_at->translatedFormat('d M Y') : '-',
                    'variant' => (string) ($review->transactionDetail->variant_name ?? ''),
                    'text' => (string) ($review->message ?? ''),
                    'photos' => $photos,
                ];
            })
            ->values()
            ->all();

        $activeFlashSaleItem = $product->flashSaleItems
            ->first(function ($item) {
                $sale = $item->flashSale;
                if (!$sale || !$item->is_active) {
                    return false;
                }

                if ($sale->status !== 'active') {
                    return false;
                }

                $now = now();
                return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
            });

        $isFlashSale = (bool) $activeFlashSaleItem;
        $flashSalePrice = $isFlashSale ? (int) $activeFlashSaleItem->discount_price : null;
        $origPrice = $price;
        $deliveredStatuses = [
            'paid',
            'settlement',
            'capture',
            'process',
            'processing',
            'kirim',
            'shipping',
            'shipped',
            'selesai',
            'completed',
            'delivered',
        ];

        $recommendedCandidatesQuery = Product::query()
            ->with([
                'mainCategory',
                'categoryDetail',
                'productVariants.variant',
                'flashSaleItems.flashSale',
            ])
            ->where('status', 'active')
            ->where('id', '!=', $product->id);

        if ($product->category_detail_id || $product->main_category_id) {
            $recommendedCandidatesQuery->where(function ($q) use ($product) {
                if ($product->category_detail_id) {
                    $q->orWhere('category_detail_id', $product->category_detail_id);
                }
                if ($product->main_category_id) {
                    $q->orWhere('main_category_id', $product->main_category_id);
                }
            });
        }

        $recommendedCandidates = $recommendedCandidatesQuery
            ->limit(30)
            ->get();

        if ($recommendedCandidates->count() < 5) {
            $fallback = Product::query()
                ->with([
                    'mainCategory',
                    'categoryDetail',
                    'productVariants.variant',
                    'flashSaleItems.flashSale',
                ])
                ->where('status', 'active')
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $recommendedCandidates->pluck('id'))
                ->latest('id')
                ->limit(20)
                ->get();
            $recommendedCandidates = $recommendedCandidates->concat($fallback);
        }

        $candidateIds = $recommendedCandidates->pluck('id')->values();
        $soldCounts = TransactionDetail::query()
            ->selectRaw('product_id, SUM(quantity) as sold_total')
            ->whereIn('product_id', $candidateIds)
            ->whereHas('transaction', function ($q) use ($deliveredStatuses) {
                $q->whereIn(DB::raw('LOWER(status)'), $deliveredStatuses);
            })
            ->groupBy('product_id')
            ->pluck('sold_total', 'product_id');

        $reviewStatsByProduct = TransactionProductReview::query()
            ->join('transaction_details', 'transaction_details.id', '=', 'transaction_product_reviews.transaction_detail_id')
            ->selectRaw('transaction_details.product_id as product_id, AVG(transaction_product_reviews.rating) as avg_rating, COUNT(transaction_product_reviews.id) as total_reviews')
            ->where('transaction_product_reviews.is_hidden', false)
            ->whereIn('transaction_details.product_id', $candidateIds)
            ->groupBy('transaction_details.product_id')
            ->get()
            ->keyBy('product_id');

        $recommendedProducts = $recommendedCandidates
            ->map(function ($recProduct) use ($soldCounts, $reviewStatsByProduct) {
                $recVariant = $recProduct->productVariants->first();
                if (!$recVariant) {
                    return null;
                }

                $recPrice = (int) $recVariant->price;
                $recActiveFlashSaleItem = $recProduct->flashSaleItems
                    ->first(function ($item) {
                        $sale = $item->flashSale;
                        if (!$sale || !$item->is_active || $sale->status !== 'active') {
                            return false;
                        }
                        $now = now();
                        return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
                    });

                $recDisplayPrice = $recActiveFlashSaleItem ? (int) $recActiveFlashSaleItem->discount_price : $recPrice;
                $stats = $reviewStatsByProduct->get($recProduct->id);
                $rating = $stats ? round((float) $stats->avg_rating, 1) : 0.0;
                $reviewsCount = $stats ? (int) $stats->total_reviews : 0;
                $soldCount = (int) ($soldCounts[$recProduct->id] ?? 0);
                $score = ($soldCount * 0.5) + ($rating * 20) + ($reviewsCount * 1.5);

                return [
                    'name' => (string) $recProduct->name,
                    'price' => $recDisplayPrice,
                    'image' => $this->resolveProductVariantImageUrl($recProduct, $recVariant, '300x300'),
                    'rating' => round($rating, 1),
                    'url' => route('frontend.detail-produk', ['slug' => $recProduct->slug]),
                    '_score' => $score,
                ];
            })
            ->filter()
            ->sortByDesc('_score')
            ->take(5)
            ->values()
            ->map(function ($item) {
                unset($item['_score']);
                return $item;
            })
            ->all();

        $recentIds = collect(session('recently_viewed_products', []))
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0 && $id !== (int) $product->id)
            ->unique()
            ->take(8)
            ->values();
        $recentlyViewedProducts = $this->buildProductCardsByIds($recentIds->all());

        session([
            'recently_viewed_products' => collect([(int) $product->id])
                ->merge(session('recently_viewed_products', []))
                ->map(fn($id) => (int) $id)
                ->unique()
                ->take(12)
                ->values()
                ->all(),
        ]);

        $relatedProducts = $this->buildRelatedProducts($product->id, $product->main_category_id);

        return view('frontend.detail-produk', [
            'productData' => [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'categoryName' => $product->categoryDetail?->name ?? ($product->mainCategory?->name ?? 'Produk'),
                'categoryUrlName' => $this->mapCategoryPageCategory($product->categoryDetail?->name ?? $product->mainCategory?->name),
                'image' => $image,
                'images' => $galleryImages,
                'price' => $price,
                'origPrice' => $origPrice,
                'sold' => $sold,
                'rating' => $rating,
                'reviews' => $reviews,
                'stock' => (int) $variant->stock,
                'description' => $product->description,
                'isFlashSale' => $isFlashSale,
                'flashSalePrice' => $flashSalePrice,
                'isRedeemProduct' => (bool) $product->is_redeem_product,
                'redeemPoints' => (int) ($product->redeem_points ?? 0),
                'variantName' => $variant->variant?->name ?? 'Varian',
                'variantValue' => $variant->variant?->value ?? '-',
                'variantGroups' => $variantGroups,
                'productVariantId' => (int) $variant->id,
                'isWishlisted' => auth()->check()
                    ? Wishlist::query()
                    ->where('user_id', auth()->id())
                    ->where('product_id', $product->id)
                    ->exists()
                    : false,
                'variantOptions' => $product->productVariants
                    ->filter(fn($pv) => $pv->variant && filled($pv->variant->value))
                    ->map(function ($pv) use ($product, $isFlashSale, $flashSalePrice) {
                        $basePrice = (int) $pv->price;
                        $displayPrice = $isFlashSale && $flashSalePrice ? (int) $flashSalePrice : $basePrice;

                        return [
                            'id' => (int) $pv->id,
                            'name' => strtolower((string) ($pv->variant->name ?? '')),
                            'value' => (string) ($pv->variant->value ?? ''),
                            'image' => $this->resolveProductVariantImageUrl($product, $pv, '700x700'),
                            'price' => $basePrice,
                            'displayPrice' => $displayPrice,
                            'stock' => (int) $pv->stock,
                        ];
                    })
                    ->values()
                    ->all(),
                'reviewItems' => $reviewItems,
                'reviewDistribution' => $ratingDistribution,
            ],
            'relatedProductsJson' => $relatedProducts,
            'recentlyViewedProductsJson' => $recentlyViewedProducts,
        ]);
    }

    private function buildProductCardsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $products = Product::query()
            ->with(['productVariants.variant', 'flashSaleItems.flashSale'])
            ->where('status', 'active')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn($product) => array_search((int) $product->id, $ids, true));

        return $products->map(function ($product) {
            $variant = $product->productVariants->first();
            if (!$variant) {
                return null;
            }

            $basePrice = (int) $variant->price;
            $activeFlashSaleItem = $product->flashSaleItems->first(function ($item) {
                $sale = $item->flashSale;
                return $sale && $item->is_active && $sale->status === 'active' && $sale->start_at && $sale->end_at && now()->between($sale->start_at, $sale->end_at);
            });

            return [
                'name' => (string) $product->name,
                'price' => $activeFlashSaleItem ? (int) $activeFlashSaleItem->discount_price : $basePrice,
                'image' => $this->resolveProductVariantImageUrl($product, $variant, '300x300'),
                'rating' => 0,
                'url' => route('frontend.detail-produk', ['slug' => $product->slug]),
            ];
        })->filter()->values()->all();
    }

    private function buildRelatedProducts(int $excludeProductId, ?int $mainCategoryId): array
    {
        $deliveredStatuses = ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];

        $query = Product::with(['mainCategory', 'categoryDetail', 'productVariants.variant', 'flashSaleItems.flashSale'])
            ->where('status', 'active')
            ->where('id', '!=', $excludeProductId);

        if ($mainCategoryId) {
            $query->where('main_category_id', $mainCategoryId);
        }

        $products = $query->latest()->limit(20)->get();

        if ($products->isEmpty()) {
            $products = Product::with(['mainCategory', 'categoryDetail', 'productVariants.variant', 'flashSaleItems.flashSale'])
                ->where('status', 'active')
                ->where('id', '!=', $excludeProductId)
                ->latest()
                ->limit(10)
                ->get();
        }

        $productIds = $products->pluck('id')->filter()->all();

        $soldMap = TransactionDetail::query()
            ->selectRaw('product_id, SUM(quantity) as sold_qty')
            ->whereIn('product_id', $productIds)
            ->whereHas('transaction', fn($q) => $q->whereIn(DB::raw('LOWER(status)'), $deliveredStatuses))
            ->groupBy('product_id')
            ->pluck('sold_qty', 'product_id');

        $reviewStats = TransactionProductReview::query()
            ->join('transaction_details', 'transaction_details.id', '=', 'transaction_product_reviews.transaction_detail_id')
            ->selectRaw('transaction_details.product_id as product_id, AVG(transaction_product_reviews.rating) as avg_rating, COUNT(transaction_product_reviews.id) as total_reviews')
            ->where('transaction_product_reviews.is_hidden', false)
            ->whereIn('transaction_details.product_id', $productIds)
            ->groupBy('transaction_details.product_id')
            ->get()
            ->keyBy('product_id');

        return $products->take(10)->map(function ($product) use ($soldMap, $reviewStats) {
            $variant = $product->productVariants->first();
            if (!$variant) return null;

            $price = (int) $variant->price;
            $stat = $reviewStats->get($product->id);
            $rating = $stat ? round((float) $stat->avg_rating, 1) : 0.0;
            $reviews = $stat ? (int) $stat->total_reviews : 0;

            $activeFlashSaleItem = $product->flashSaleItems->first(function ($item) {
                $sale = $item->flashSale;
                if (!$sale || !$item->is_active || $sale->status !== 'active') return false;
                $now = now();
                return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
            });
            $isFlashSale = (bool) $activeFlashSaleItem;
            $flashSalePrice = $isFlashSale ? (int) $activeFlashSaleItem->discount_price : null;
            $displayPrice = $isFlashSale ? $flashSalePrice : $price;

            return [
                'id' => (int) $product->id,
                'slug' => (string) $product->slug,
                'name' => (string) $product->name,
                'image' => $this->resolveProductVariantImageUrl($product, $variant, '400x400'),
                'price' => $displayPrice,
                'originalPrice' => $price,
                'rating' => $rating,
                'reviews' => $reviews,
                'sold' => (int) ($soldMap[$product->id] ?? 0),
                'isFlashSale' => $isFlashSale,
            ];
        })->filter()->values()->all();
    }

    public function checkout()
    {
        abort_unless(auth()->check(), 403);

        $addresses = auth()->check()
            ? auth()->user()->addresses()->orderByDesc('is_primary')->latest()->get()
            : collect();

        $checkout = session('checkout', []);
        $source = (string) ($checkout['source'] ?? '');
        if ($source === 'buy_now') {
            $checkoutItems = collect($checkout['items'] ?? [])->values()->all();
        } elseif ($source === 'redeem_point') {
            $checkoutItems = collect($checkout['items'] ?? [])->values()->all();
        } elseif ($source === 'cart_selected') {
            $selectedIds = collect($checkout['cart_ids'] ?? [])
                ->map(fn($id) => (int) $id)
                ->filter()
                ->values()
                ->all();
            $checkoutItems = $this->buildCheckoutItems((int) auth()->id(), $selectedIds);
        } else {
            $checkoutItems = $this->buildCheckoutItems((int) auth()->id());
        }

        return view('frontend.checkout', [
            'addresses' => $addresses,
            'checkoutItems' => $checkoutItems,
            'checkoutSource' => $source ?: 'cart_all',
        ]);
    }

    public function profil(MembershipTierService $membershipTierService)
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();
        $addresses = $user->addresses()->orderByDesc('is_primary')->latest()->get();
        $transactions = Transaction::query()
            ->with([
                'details.productReviews' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
                'details.returnRequestItems.returnRequest',
                'statusHistories.user',
                'returnRequests.items',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $notifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $wishlists = Wishlist::query()
            ->with(['product.productVariants.variant', 'product.flashSaleItems.flashSale'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $profileStats = [
            'orders' => (int) $transactions->count(),
            'reviews' => (int) TransactionProductReview::query()
                ->where('user_id', $user->id)
                ->count(),
            'wishlists' => (int) $wishlists->count(),
        ];

        $membershipSummary = $membershipTierService->resolveForUser($user);

        return view('frontend.profil', compact('user', 'addresses', 'transactions', 'notifications', 'wishlists', 'profileStats', 'membershipSummary'));
    }

    private function buildFrontendProducts(): array
    {
        $deliveredStatuses = [
            'paid',
            'settlement',
            'capture',
            'process',
            'processing',
            'kirim',
            'shipping',
            'shipped',
            'selesai',
            'completed',
            'delivered',
        ];

        $products = Product::with([
            'mainCategory',
            'categoryDetail',
            'productVariants.variant',
            'flashSaleItems.flashSale',
        ])
            ->where('status', 'active')
            ->latest()
            ->get();
        $productIds = $products->pluck('id')->filter()->values()->all();

        $soldMap = TransactionDetail::query()
            ->selectRaw('product_id, SUM(quantity) as sold_qty')
            ->whereIn('product_id', $productIds)
            ->whereHas('transaction', function ($q) use ($deliveredStatuses) {
                $q->whereIn(DB::raw('LOWER(status)'), $deliveredStatuses);
            })
            ->groupBy('product_id')
            ->pluck('sold_qty', 'product_id');

        $reviewStats = TransactionProductReview::query()
            ->join('transaction_details', 'transaction_details.id', '=', 'transaction_product_reviews.transaction_detail_id')
            ->selectRaw('transaction_details.product_id as product_id, AVG(transaction_product_reviews.rating) as avg_rating, COUNT(transaction_product_reviews.id) as total_reviews')
            ->where('transaction_product_reviews.is_hidden', false)
            ->whereIn('transaction_details.product_id', $productIds)
            ->groupBy('transaction_details.product_id')
            ->get()
            ->keyBy('product_id');
        $wishedProductIds = auth()->check()
            ? Wishlist::query()
            ->where('user_id', auth()->id())
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->map(fn($id) => (int) $id)
            ->all()
            : [];
        $wishedLookup = array_flip($wishedProductIds);

        $home = [];
        $category = [];

        foreach ($products as $idx => $product) {
            $variant = $product->productVariants->first();
            if (!$variant) {
                continue;
            }

            $image = $this->resolveProductVariantImageUrl($product, $variant, '400x400');
            $variantFilters = $product->productVariants
                ->filter(fn($pv) => $pv->variant && filled($pv->variant->name) && filled($pv->variant->value))
                ->map(fn($pv) => [
                    'name' => (string) $pv->variant->name,
                    'value' => (string) $pv->variant->value,
                ])
                ->unique(fn($item) => strtolower($item['name'] . '|' . $item['value']))
                ->values()
                ->all();

            $variantPrices = $product->productVariants
                ->pluck('price')
                ->map(fn($v) => (int) $v)
                ->filter(fn($v) => $v > 0)
                ->values();
            $priceMin = $variantPrices->isNotEmpty() ? (int) $variantPrices->min() : (int) $variant->price;
            $priceMax = $variantPrices->isNotEmpty() ? (int) $variantPrices->max() : (int) $variant->price;
            $price = (int) $variant->price;
            $sold = (int) ($soldMap[$product->id] ?? 0);
            $stat = $reviewStats->get($product->id);
            $rating = $stat ? round((float) $stat->avg_rating, 1) : 0.0;
            $reviews = $stat ? (int) $stat->total_reviews : 0;
            $activeFlashSaleItem = $product->flashSaleItems
                ->first(function ($item) {
                    $sale = $item->flashSale;
                    if (!$sale || !$item->is_active) {
                        return false;
                    }

                    if ($sale->status !== 'active') {
                        return false;
                    }

                    $now = now();
                    return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
                });
            $isFlashSale = (bool) $activeFlashSaleItem;
            $flashSalePrice = $isFlashSale ? (int) $activeFlashSaleItem->discount_price : null;
            $displayPrice = $isFlashSale ? $flashSalePrice : $priceMin;
            $displayPriceMax = $isFlashSale ? $flashSalePrice : $priceMax;
            $originalPrice = $isFlashSale ? $priceMin : $priceMin;
            $originalPriceMax = $isFlashSale ? $priceMax : $priceMax;

            $badge = null;
            if ($isFlashSale) {
                $badge = 'promo';
            } elseif ($idx % 5 === 1) {
                $badge = 'best';
            } elseif ($idx % 5 === 2) {
                $badge = 'new';
            }

            $home[] = [
                'id' => $product->id,
                'productVariantId' => (int) $variant->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => $displayPrice,
                'priceMax' => $displayPriceMax,
                'originalPrice' => $originalPrice,
                'originalPriceMax' => $originalPriceMax,
                'category' => $this->mapHomeCategory($product->mainCategory?->name),
                'categorySlug' => (string) ($product->categoryDetail?->slug ?? ''),
                'parentCategorySlug' => (string) ($product->mainCategory?->slug ?? ''),
                'rating' => round($rating, 1),
                'reviews' => $reviews,
                'image' => $image,
                'variants' => $variantFilters,
                'badge' => $badge,
                'sold' => $sold,
                'isNew' => $badge === 'new',
                'isFlashSale' => $isFlashSale,
                'isWishlisted' => isset($wishedLookup[(int) $product->id]),
            ];

            $category[] = [
                'id' => $product->id,
                'productVariantId' => (int) $variant->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => $displayPrice,
                'priceMax' => $displayPriceMax,
                'origPrice' => $originalPrice,
                'origPriceMax' => $originalPriceMax,
                'cat' => $this->mapCategoryPageCategory($product->mainCategory?->name),
                'categorySlug' => (string) ($product->categoryDetail?->slug ?? ''),
                'parentCategorySlug' => (string) ($product->mainCategory?->slug ?? ''),
                'rating' => round($rating, 1),
                'reviews' => $reviews,
                'image' => $image,
                'variants' => $variantFilters,
                'badge' => $badge,
                'sold' => $sold,
                'isNew' => $badge === 'new',
                'isFlashSale' => $isFlashSale,
                'isWishlisted' => isset($wishedLookup[(int) $product->id]),
            ];
        }

        return [
            'home' => array_values($home),
            'category' => array_values($category),
        ];
    }

    private function buildRedeemProducts(): array
    {
        $productIds = Product::query()
            ->where('status', 'active')
            ->where('is_redeem_product', true)
            ->pluck('id')
            ->all();

        if (empty($productIds)) {
            return [];
        }

        $soldMap = TransactionDetail::query()
            ->selectRaw('product_id, SUM(quantity) as sold_qty')
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->pluck('sold_qty', 'product_id');

        $reviewStats = TransactionProductReview::query()
            ->join('transaction_details', 'transaction_details.id', '=', 'transaction_product_reviews.transaction_detail_id')
            ->selectRaw('transaction_details.product_id as product_id, AVG(transaction_product_reviews.rating) as avg_rating, COUNT(transaction_product_reviews.id) as total_reviews')
            ->where('transaction_product_reviews.is_hidden', false)
            ->whereIn('transaction_details.product_id', $productIds)
            ->groupBy('transaction_details.product_id')
            ->get()
            ->keyBy('product_id');

        return Product::query()
            ->with(['mainCategory', 'categoryDetail', 'productVariants.variant'])
            ->where('status', 'active')
            ->where('is_redeem_product', true)
            ->latest()
            ->get()
            ->map(function ($product) use ($soldMap, $reviewStats) {
                $variant = $product->productVariants->first();
                if (!$variant) {
                    return null;
                }

                $stats = $reviewStats->get($product->id);

                return [
                    'id' => (int) $product->id,
                    'productVariantId' => (int) $variant->id,
                    'slug' => (string) $product->slug,
                    'name' => (string) $product->name,
                    'category' => (string) ($product->categoryDetail?->name ?? $product->mainCategory?->name ?? 'Produk Redeem'),
                    'image' => $this->resolveProductVariantImageUrl($product, $variant, '400x400'),
                    'redeemPoints' => (int) ($product->redeem_points ?? 0),
                    'stock' => (int) $variant->stock,
                    'variant' => trim(($variant->variant?->name ? $variant->variant->name . ': ' : '') . ($variant->variant?->value ?? '-')),
                    'sold' => (int) ($soldMap[$product->id] ?? 0),
                    'rating' => $stats ? round((float) $stats->avg_rating, 1) : 0.0,
                    'reviews' => $stats ? (int) $stats->total_reviews : 0,
                    'detailUrl' => route('frontend.detail-produk', ['slug' => $product->slug]),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function mapHomeCategory(?string $categoryName): string
    {
        $name = strtolower((string) $categoryName);

        return match (true) {
            str_contains($name, 'baut') => 'baut',
            str_contains($name, 'mur') => 'mur',
            str_contains($name, 'ring') || str_contains($name, 'washer') => 'ring-washer',
            str_contains($name, 'sekrup') => 'sekrup',
            str_contains($name, 'dynabolt') || str_contains($name, 'anchor') => 'anchor',
            str_contains($name, 'tool') || str_contains($name, 'perkakas') => 'tools',
            default => 'fastener',
        };
    }

    private function homeCategoryLabel(string $key): string
    {
        return match ($key) {
            'baut' => 'Baut',
            'mur' => 'Mur',
            'ring-washer' => 'Ring & Washer',
            'sekrup' => 'Sekrup',
            'anchor' => 'Dynabolt & Anchor',
            'tools' => 'Tools & Perkakas',
            'fastener' => 'Fastener',
            default => ucfirst($key),
        };
    }

    private function homeCategoryIcon(string $name): string
    {
        $n = strtolower($name);
        return match (true) {
            str_contains($n, 'baut') => 'ri-screwdriver-line',
            str_contains($n, 'mur') => 'ri-settings-3-line',
            str_contains($n, 'ring') || str_contains($n, 'washer') => 'ri-record-circle-line',
            str_contains($n, 'sekrup') => 'ri-tools-line',
            str_contains($n, 'dynabolt') || str_contains($n, 'anchor') => 'ri-anchor-line',
            str_contains($n, 'tool') || str_contains($n, 'perkakas') => 'ri-hammer-line',
            str_contains($n, 'paku') => 'ri-pushpin-line',
            str_contains($n, 'klem') || str_contains($n, 'bracket') => 'ri-braces-line',
            str_contains($n, 'chemical') || str_contains($n, 'lem') => 'ri-flask-line',
            str_contains($n, 'safety') || str_contains($n, 'abrasive') => 'ri-shield-check-line',
            default => 'ri-price-tag-3-line',
        };
    }

    private function resolveMainCategoryImage(?string $image): string
    {
        $value = trim((string) $image);
        if ($value === '') {
            return '';
        }

        if (
            str_starts_with($value, 'http://') ||
            str_starts_with($value, 'https://') ||
            str_starts_with($value, '//') ||
            str_starts_with($value, 'data:')
        ) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    }

    private function mapCategoryPageCategory(?string $categoryName): string
    {
        $name = strtolower((string) $categoryName);

        return match (true) {
            str_contains($name, 'baut') => 'baut',
            str_contains($name, 'mur') => 'mur',
            str_contains($name, 'ring') || str_contains($name, 'washer') => 'ring-washer',
            str_contains($name, 'sekrup') => 'sekrup',
            str_contains($name, 'dynabolt') || str_contains($name, 'anchor') => 'anchor',
            str_contains($name, 'tool') || str_contains($name, 'perkakas') => 'tools',
            str_contains($name, 'paku') => 'paku',
            str_contains($name, 'klem') || str_contains($name, 'bracket') => 'klem-bracket',
            default => 'fastener',
        };
    }

    private function normalizeImageUrl(string $image, string $fallbackSize = '400x400'): string
    {
        if ($image === '') {
            return "https://via.placeholder.com/{$fallbackSize}?text=No+Image";
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
    }

    private function resolveProductVariantImageUrl(Product $product, ?ProductVariant $variant, string $fallbackSize = '400x400'): string
    {
        $image = trim((string) (($variant?->image) ?: ($product->firstAvailableImagePath() ?? '')));

        return $this->normalizeImageUrl($image, $fallbackSize);
    }

    private function buildActiveFlashSaleData(): array
    {
        $now = now();

        $items = FlashSaleItem::with([
            'flashSale',
            'productVariant.product',
        ])
            ->where('is_active', true)
            ->whereHas('flashSale', function ($q) use ($now) {
                $q->where('status', 'active')
                    ->where('start_at', '<=', $now)
                    ->where('end_at', '>=', $now);
            })
            ->orderByDesc('flash_sale_id')
            ->orderByDesc('id')
            ->get()
            ->filter(fn($item) => $item->productVariant && $item->productVariant->product)
            ->values();

        $mapped = $items->map(function ($item) {
            $variant = $item->productVariant;
            $product = $variant->product;

            $basePrice = (float) $variant->price;
            $salePrice = (float) $item->discount_price;
            $quota = max(1, (int) $item->quota);
            $sold = max(0, (int) $item->sold);
            $remaining = max(0, $quota - $sold);
            $remainingPercent = (int) round(($remaining / $quota) * 100);
            $discountPercent = $basePrice > 0 ? (int) round((1 - ($salePrice / $basePrice)) * 100) : 0;

            return [
                'id' => $item->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'image' => $this->resolveProductVariantImageUrl($product, $variant, '400x400'),
                'price' => (int) round($salePrice),
                'originalPrice' => (int) round($basePrice),
                'discountPercent' => max(0, $discountPercent),
                'quota' => $quota,
                'sold' => $sold,
                'remaining' => $remaining,
                'remainingPercent' => max(0, min(100, $remainingPercent)),
                'flashSaleName' => (string) ($item->flashSale?->name ?? 'Flash Sale'),
                'flashSaleId' => (int) ($item->flash_sale_id),
                'flashSaleEndAt' => $item->flashSale?->end_at?->toIso8601String(),
            ];
        })->values();

        $campaigns = $mapped
            ->groupBy('flashSaleId')
            ->map(function ($campaignItems) {
                $first = $campaignItems->first();
                return [
                    'id' => $first['flashSaleId'],
                    'name' => $first['flashSaleName'] ?: 'Flash Sale',
                    'end_at' => $first['flashSaleEndAt'],
                    'items' => $campaignItems->values()->all(),
                ];
            })
            ->sortBy('end_at')
            ->values()
            ->all();

        $featured = $campaigns[0] ?? [
            'id' => null,
            'name' => 'Flash Sale',
            'end_at' => null,
            'items' => [],
        ];

        return compact('campaigns', 'featured');
    }

    private function buildCheckoutItems(int $userId, ?array $cartIds = null): array
    {
        $query = \App\Models\Cart::query()
            ->with(['productVariant.product', 'productVariant.variant', 'productVariant.flashSaleItems.flashSale'])
            ->where('user_id', $userId)
            ->latest();
        if (is_array($cartIds) && !empty($cartIds)) {
            $query->whereIn('id', $cartIds);
        }
        $rows = $query->get();

        return $rows->map(function ($row) {
            /** @var ProductVariant|null $variant */
            $variant = $row->productVariant;
            $product = $variant?->product;
            if (!$variant || !$product || $product->status !== 'active' || (int) $variant->stock < 1) {
                return null;
            }

            $qty = min((int) $row->quantity, max(0, (int) $variant->stock));
            if ($qty < 1) {
                return null;
            }

            $basePrice = (int) $variant->price;
            $flashItem = $variant->flashSaleItems->first(function ($item) {
                $sale = $item->flashSale;
                if (!$sale || !$item->is_active || $sale->status !== 'active') {
                    return false;
                }

                $now = now();
                return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
            });

            $salePrice = $flashItem ? (int) $flashItem->discount_price : $basePrice;
            $variantText = trim(
                ($variant->variant?->name ? $variant->variant->name . ': ' : '') .
                    ($variant->variant?->value ?? '-')
            );

            return [
                'cartId' => (int) $row->id,
                'productVariantId' => (int) $variant->id,
                'id' => (int) $product->id,
                'slug' => (string) $product->slug,
                'name' => (string) $product->name,
                'variant' => $variantText !== '' ? $variantText : '-',
                'price' => $salePrice,
                'origPrice' => $basePrice,
                'qty' => $qty,
                'image' => $this->resolveProductVariantImageUrl($product, $variant, '100x100'),
                'isFlashSale' => (bool) $flashItem,
            ];
        })->filter()->values()->all();
    }
}
