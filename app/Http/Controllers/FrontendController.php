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
        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(function ($banner) {
                $image = (string) $banner->image;
                if (
                    str_starts_with($image, 'http://') ||
                    str_starts_with($image, 'https://') ||
                    str_starts_with($image, '//') ||
                    str_starts_with($image, 'data:')
                ) {
                    $imageUrl = $image;
                } else {
                    $imageUrl = asset('storage/' . ltrim($image, '/'));
                }

                return [
                    'image' => $imageUrl,
                    'target_url' => (string) ($banner->target_url ?? ''),
                    'sort_order' => (int) $banner->sort_order,
                ];
            })
            ->values()
            ->all();

        return view('frontend.index', [
            'productsJson' => $products['home'],
            'flashSale' => $flashSaleData['featured'],
            'homeFilterCategories' => $homeCategories,
            'homeMainCategories' => $mainCategories,
            'bannersJson' => $banners,
        ]);
    }

    public function kategori()
    {
        $products = $this->buildFrontendProducts();
        $categoryTree = MainCategory::query()
            ->with(['categoryDetails' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();
        $selectedParentSlug = (string) request()->query('parent', '');
        $selectedCategorySlug = (string) request()->query('category', '');

        $filtered = collect($products['category'])
            ->filter(function ($product) use ($selectedParentSlug, $selectedCategorySlug) {
                if ($selectedCategorySlug !== '' && $product['categorySlug'] !== $selectedCategorySlug) {
                    return false;
                }

                if ($selectedParentSlug !== '' && $product['parentCategorySlug'] !== $selectedParentSlug) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();

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
            'productsJson' => $filtered,
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

    public function search(Request $request)
    {
        return view('frontend.search-results', [
            'query' => (string) $request->query('q', ''),
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
            ->filter(fn ($pv) => $pv->variant && filled($pv->variant->name) && filled($pv->variant->value))
            ->groupBy(fn ($pv) => strtolower(trim($pv->variant->name)))
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
            ->filter(fn ($img) => filled($img))
            ->map(fn ($img) => $this->normalizeImageUrl((string) $img, '700x700'))
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
            ->whereHas('transactionDetail', fn ($q) => $q->where('product_id', $product->id))
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
                    ->filter(fn ($pv) => $pv->variant && filled($pv->variant->value))
                    ->map(function ($pv) use ($isFlashSale, $flashSalePrice) {
                        $basePrice = (int) $pv->price;
                        $displayPrice = $isFlashSale && $flashSalePrice ? (int) $flashSalePrice : $basePrice;

                        return [
                            'id' => (int) $pv->id,
                            'name' => strtolower((string) ($pv->variant->name ?? '')),
                            'value' => (string) ($pv->variant->value ?? ''),
                            'image' => $this->normalizeImageUrl((string) ($pv->image ?? ''), '700x700'),
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
        ]);
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
        } elseif ($source === 'cart_selected') {
            $selectedIds = collect($checkout['cart_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
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

    public function profil()
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();
        $addresses = $user->addresses()->orderByDesc('is_primary')->latest()->get();
        $transactions = Transaction::query()
            ->with([
                'details.productReviews' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
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

        return view('frontend.profil', compact('user', 'addresses', 'transactions', 'notifications', 'wishlists'));
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
            ->whereIn('transaction_details.product_id', $productIds)
            ->groupBy('transaction_details.product_id')
            ->get()
            ->keyBy('product_id');
        $wishedProductIds = auth()->check()
            ? Wishlist::query()
                ->where('user_id', auth()->id())
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
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

            $image = $this->normalizeImageUrl((string) ($variant->image ?? ''), '400x400');

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
            $displayPrice = $isFlashSale ? $flashSalePrice : $price;
            $originalPrice = $price;

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
                'originalPrice' => $originalPrice,
                'category' => $this->mapHomeCategory($product->mainCategory?->name),
                'categorySlug' => (string) ($product->categoryDetail?->slug ?? ''),
                'parentCategorySlug' => (string) ($product->mainCategory?->slug ?? ''),
                'rating' => round($rating, 1),
                'reviews' => $reviews,
                'image' => $image,
                'colors' => [strtolower($variant->variant?->value ?? 'hitam')],
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
                'origPrice' => $originalPrice,
                'cat' => $this->mapCategoryPageCategory($product->mainCategory?->name),
                'categorySlug' => (string) ($product->categoryDetail?->slug ?? ''),
                'parentCategorySlug' => (string) ($product->mainCategory?->slug ?? ''),
                'rating' => round($rating, 1),
                'reviews' => $reviews,
                'image' => $image,
                'colors' => [strtolower($variant->variant?->value ?? 'hitam')],
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

    private function mapHomeCategory(?string $categoryName): string
    {
        $name = strtolower((string) $categoryName);

        return match (true) {
            str_contains($name, 'fashion') => 'fashion',
            str_contains($name, 'elektronik') => 'elektronik',
            str_contains($name, 'kecantikan') => 'kecantikan',
            str_contains($name, 'olahraga') => 'olahraga',
            str_contains($name, 'rumah') => 'rumah',
            default => 'fashion',
        };
    }

    private function homeCategoryLabel(string $key): string
    {
        return match ($key) {
            'fashion' => 'Fashion',
            'elektronik' => 'Elektronik',
            'rumah' => 'Rumah & Dapur',
            'olahraga' => 'Olahraga',
            'kecantikan' => 'Kecantikan',
            default => ucfirst($key),
        };
    }

    private function homeCategoryIcon(string $name): string
    {
        $n = strtolower($name);
        return match (true) {
            str_contains($n, 'fashion') && str_contains($n, 'pria') => 'ri-t-shirt-line',
            str_contains($n, 'fashion') && str_contains($n, 'wanita') => 'ri-women-line',
            str_contains($n, 'elektronik') => 'ri-computer-line',
            str_contains($n, 'rumah') => 'ri-home-smile-2-line',
            str_contains($n, 'olahraga') => 'ri-riding-line',
            str_contains($n, 'kecantikan') => 'ri-magic-line',
            str_contains($n, 'mainan') => 'ri-gamepad-line',
            str_contains($n, 'hp') || str_contains($n, 'tablet') => 'ri-smartphone-line',
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
            str_contains($name, 'fashion pria') => 'fashion-pria',
            str_contains($name, 'fashion wanita') => 'fashion-wanita',
            str_contains($name, 'fashion') => 'fashion-pria',
            str_contains($name, 'elektronik') => 'elektronik',
            str_contains($name, 'kecantikan') => 'kecantikan',
            str_contains($name, 'olahraga') => 'olahraga',
            str_contains($name, 'rumah') => 'rumah',
            str_contains($name, 'mainan') => 'mainan',
            str_contains($name, 'hp') => 'hp',
            default => 'fashion-pria',
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
            ->filter(fn ($item) => $item->productVariant && $item->productVariant->product)
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
                'image' => $this->normalizeImageUrl((string) ($variant->image ?? ''), '400x400'),
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
            if (!$variant || !$product) {
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
                'qty' => (int) $row->quantity,
                'image' => $this->normalizeImageUrl((string) ($variant->image ?? ''), '100x100'),
                'isFlashSale' => (bool) $flashItem,
            ];
        })->filter()->values()->all();
    }
}
