<?php

namespace App\Http\Controllers;

use App\Models\FlashSaleItem;
use App\Models\Product;
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

        return view('frontend.kategori', [
            'productsJson' => $products['category'],
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
            'category',
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
        $origPrice = (int) round($price * 1.35);
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

        return view('frontend.detail-produk', [
            'productData' => [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'categoryName' => $product->category?->name ?? 'Produk',
                'categoryUrlName' => $this->mapCategoryPageCategory($product->category?->name),
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
            ],
        ]);
    }

    public function checkout()
    {
        $addresses = auth()->check()
            ? auth()->user()->addresses()->orderByDesc('is_primary')->latest()->get()
            : collect();

        return view('frontend.checkout', compact('addresses'));
    }

    public function profil()
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();
        $addresses = $user->addresses()->orderByDesc('is_primary')->latest()->get();

        return view('frontend.profil', compact('user', 'addresses'));
    }

    private function buildFrontendProducts(): array
    {
        $products = Product::with(['category', 'productVariants.variant'])
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
            $origPrice = (int) round($price * 1.35);
            $sold = (int) max(1, round($variant->stock * 0.6));
            $rating = 4.4 + (($idx % 6) * 0.1);
            $reviews = 90 + ($idx * 37);

            $badge = null;
            if ($idx % 5 === 0) {
                $badge = 'promo';
            } elseif ($idx % 5 === 1) {
                $badge = 'best';
            } elseif ($idx % 5 === 2) {
                $badge = 'new';
            }

            $home[] = [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => $price,
                'originalPrice' => $origPrice,
                'category' => $this->mapHomeCategory($product->category?->name),
                'rating' => round($rating, 1),
                'reviews' => $reviews,
                'image' => $image,
                'colors' => [strtolower($variant->variant?->value ?? 'hitam')],
                'badge' => $badge,
                'sold' => $sold,
                'isNew' => $badge === 'new',
            ];

            $category[] = [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => $price,
                'origPrice' => $origPrice,
                'cat' => $this->mapCategoryPageCategory($product->category?->name),
                'rating' => round($rating, 1),
                'reviews' => $reviews,
                'image' => $image,
                'badge' => $badge,
                'sold' => $sold,
                'isNew' => $badge === 'new',
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
}
