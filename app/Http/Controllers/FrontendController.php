<?php

namespace App\Http\Controllers;

use App\Models\FlashSaleItem;
use App\Models\MainCategory;
use App\Models\CategoryDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        $products = $this->buildFrontendProducts();
        $flashSaleData = $this->buildActiveFlashSaleData();

        return view('frontend.index', [
            'productsJson' => $products['home'],
            'flashSale' => $flashSaleData['featured'],
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
        $sold = (int) max(1, round($variant->stock * 0.6));
        $rating = 4.8;
        $reviews = 234;

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
            ->with(['details'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('frontend.profil', compact('user', 'addresses', 'transactions'));
    }

    private function buildFrontendProducts(): array
    {
        $products = Product::with([
            'mainCategory',
            'categoryDetail',
            'productVariants.variant',
            'flashSaleItems.flashSale',
        ])
            ->where('status', 'active')
            ->latest()
            ->get();

        $home = [];
        $category = [];

        foreach ($products as $idx => $product) {
            $variant = $product->productVariants->first();
            if (!$variant) {
                continue;
            }

            $image = $this->normalizeImageUrl((string) ($variant->image ?? ''), '400x400');

            $price = (int) $variant->price;
            $sold = (int) max(1, round($variant->stock * 0.6));
            $rating = 4.4 + (($idx % 6) * 0.1);
            $reviews = 90 + ($idx * 37);
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
                'badge' => $badge,
                'sold' => $sold,
                'isNew' => $badge === 'new',
                'isFlashSale' => $isFlashSale,
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
