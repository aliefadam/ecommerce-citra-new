<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FrontendCustomerBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_page_query_baseline_can_be_measured(): void
    {
        $this->seed();

        $product = Product::query()->whereNotNull('slug')->firstOrFail();
        $user = User::query()->where('email', 'aliefadam21@gmail.com')->firstOrFail();
        $variant = ProductVariant::query()->firstOrFail();

        $measurements = [];
        $measurements[] = $this->measure('homepage', fn () => $this->get('/'));
        $measurements[] = $this->measure('category', fn () => $this->get('/kategori'));
        $measurements[] = $this->measure('product_detail', fn () => $this->get('/detail-produk/'.$product->slug));

        $this->actingAs($user);
        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $measurements[] = $this->measure('cart', fn () => $this->get('/cart'));

        $this->postJson('/cart/checkout', ['cart_ids' => [$cart->id]])->assertSuccessful();
        $measurements[] = $this->measure('checkout', fn () => $this->get('/checkout'));
        $measurements[] = $this->measure('profile', fn () => $this->get('/profil'));

        $path = storage_path('framework/testing/frontend-baseline-query-report.json');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode([
            'generated_at' => now()->toIso8601String(),
            'database' => config('database.default'),
            'measurements' => $measurements,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        $this->assertCount(6, $measurements);
    }

    /**
     * @return array{page: string, status: int, query_count: int, duplicate_query_count: int, duration_ms: float, response_bytes: int}
     */
    private function measure(string $page, callable $request): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $startedAt = hrtime(true);

        $response = $request();
        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertSuccessful();

        $normalizedQueries = array_map(
            fn (array $query) => preg_replace('/\s+/', ' ', trim((string) $query['query'])),
            $queries
        );

        return [
            'page' => $page,
            'status' => $response->getStatusCode(),
            'query_count' => count($queries),
            'duplicate_query_count' => count($normalizedQueries) - count(array_unique($normalizedQueries)),
            'duration_ms' => round($durationMs, 2),
            'response_bytes' => strlen((string) $response->getContent()),
        ];
    }
}
