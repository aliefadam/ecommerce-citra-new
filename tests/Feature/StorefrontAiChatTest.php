<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StorefrontAiChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.koboi_llm', [
            'api_key' => 'test-secret',
            'base_url' => 'https://koboi.example',
            'model' => 'test-model',
            'connect_timeout' => 1,
            'timeout' => 2,
        ]);
    }

    public function test_out_of_scope_response_is_discarded_and_conversation_is_reset(): void
    {
        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":false,"answer":"Berikut resep rahasia kue."}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'Bagaimana cara membuat kue?',
        ])->assertOk()
            ->assertJsonPath('reset', true)
            ->assertJsonMissing(['message' => 'Berikut resep rahasia kue.']);
    }

    public function test_storefront_renders_the_ai_chat_launcher(): void
    {
        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertSee('Tanya BOQI')
            ->assertSee('imgs/boqi/boqi-mascot.webp');
    }

    public function test_string_true_scope_from_provider_is_accepted_for_product_question(): void
    {
        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":"true","answer":"Saya akan cek varian elbow diameter 15."}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'Produk elbow diamter 15 apakah ada?',
        ])->assertOk()
            ->assertJsonPath('reset', false)
            ->assertJsonPath('message', 'Saya akan cek varian elbow diameter 15.');

        Http::assertSent(function (Request $request) {
            $payload = collect($request->data()['messages'] ?? [])->pluck('content')->implode("\n");

            return str_contains($payload, 'Produk elbow diamter 15 apakah ada?')
                && str_contains($payload, 'WhatsApp/nomor CS:');
        });
    }

    public function test_invalid_provider_format_does_not_clear_the_conversation(): void
    {
        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'format tidak sesuai']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'Kapan pesanan saya sampai?',
        ])->assertOk()
            ->assertJsonPath('reset', false);
    }

    public function test_matching_products_are_returned_as_clickable_card_data(): void
    {
        $product = Product::query()->create([
            'name' => 'Equal Tee PVC Diameter 15 mm',
            'slug' => 'equal-tee-pvc-15-mm',
            'description' => 'Sambungan pipa equal tee.',
            'status' => 'active',
        ]);
        $variant = Variant::query()->create(['name' => 'Diameter', 'value' => '15 mm']);
        ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'TEE-15',
            'price' => 25000,
            'stock' => 8,
        ]);
        $expensiveProduct = Product::query()->create([
            'name' => 'Equal Tee Premium Diameter 25 mm',
            'slug' => 'equal-tee-premium-25-mm',
            'description' => 'Sambungan pipa premium.',
            'status' => 'active',
        ]);
        $expensiveVariant = Variant::query()->create(['name' => 'Diameter', 'value' => '25 mm']);
        ProductVariant::query()->create([
            'product_id' => $expensiveProduct->id,
            'variant_id' => $expensiveVariant->id,
            'sku' => 'TEE-25-PREMIUM',
            'price' => 125000,
            'stock' => 5,
        ]);

        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":true,"answer":"Equal tee adalah sambungan pipa tiga arah."}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'Cari equal tee ukuran 15 di bawah 30 ribu',
        ])->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'Equal Tee PVC Diameter 15 mm')
            ->assertJsonPath('products.0.slug', 'equal-tee-pvc-15-mm')
            ->assertJsonPath('products.0.price_min', 25000)
            ->assertJsonPath('products.0.in_stock', true)
            ->assertJsonPath('products.0.url', route('frontend.detail-produk', ['slug' => 'equal-tee-pvc-15-mm']));
    }

    public function test_misspelled_product_name_still_finds_matching_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Elbow PVC 15 mm',
            'slug' => 'elbow-pvc-15-mm',
            'description' => 'Sambungan pipa siku.',
            'status' => 'active',
        ]);
        $variant = Variant::query()->create(['name' => 'Diameter', 'value' => '15 mm']);
        ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'ELB-15',
            'price' => 12000,
            'stock' => 4,
        ]);

        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":true,"answer":"Mungkin maksud Anda Elbow PVC 15 mm."}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'cari elbouw',
        ])->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.slug', 'elbow-pvc-15-mm');

        Http::assertSent(fn (Request $request) => str_contains(json_encode($request->data()), 'Elbow PVC 15 mm'));
    }

    public function test_conversational_product_question_ranks_relevant_products_first(): void
    {
        $makeProduct = function (string $name, string $slug, string $diameter, int $price) {
            $product = Product::query()->create([
                'name' => $name,
                'slug' => $slug,
                'description' => 'Produk dengan pilihan diameter.',
                'status' => 'active',
            ]);
            $variant = Variant::query()->firstOrCreate(['name' => 'Diameter', 'value' => $diameter]);
            ProductVariant::query()->create([
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'sku' => strtoupper($slug),
                'price' => $price,
                'stock' => 3,
            ]);
        };
        $makeProduct('Elbow 90 SS304', 'elbow-90-ss304', '15 mm', 18000);
        // Produk lebih baru yang hanya cocok dengan kata "diameter" tidak boleh menggeser elbow.
        $makeProduct('Testing Data Pipe', 'testing-data-pipe', '15 mm', 1000);
        $makeProduct('Equal Tee SS304', 'equal-tee-ss304', '15 mm', 20000);

        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":true,"answer":"Ada Elbow 90 SS304 diameter 15 mm."}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'Kamu punya produk elbow diameter 15 tidak',
        ])->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.slug', 'elbow-90-ss304');
    }

    public function test_wrongly_rejected_shopping_question_is_not_reset(): void
    {
        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":false,"answer":""}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'Barang apa saja yang dijual disini?',
        ])->assertOk()
            ->assertJsonPath('reset', false);

        Http::assertSent(fn (Request $request) => str_contains(json_encode($request->data()), 'KATEGORI PRODUK YANG DIJUAL'));
    }

    public function test_markdown_links_in_answer_are_flattened(): void
    {
        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":true,"answer":"Lihat di [sini](https://boq.co.id/detail-produk/elbow)."}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), ['message' => 'link elbow'])
            ->assertOk()
            ->assertJsonPath('message', 'Lihat di sini: https://boq.co.id/detail-produk/elbow.');
    }

    public function test_only_the_signed_in_customers_orders_are_sent_to_koboi(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        Transaction::query()->create([
            'user_id' => $customer->id,
            'invoice_no' => 'INV-OWN-001',
            'order_id' => 'ORDER-OWN-001',
            'status' => 'shipping',
            'grand_total' => 125000,
            'shipping_address_line' => 'Alamat pribadi tidak boleh dikirim',
            'payment_admin_note' => 'Catatan admin rahasia',
        ]);
        Transaction::query()->create([
            'user_id' => $otherCustomer->id,
            'invoice_no' => 'INV-OTHER-999',
            'order_id' => 'ORDER-OTHER-999',
            'status' => 'paid',
            'grand_total' => 900000,
        ]);

        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":true,"answer":"Pesanan Anda sedang dikirim."}']]],
            ]),
        ]);

        $this->actingAs($customer)
            ->postJson(route('storefront.chat-ai'), ['message' => 'Di mana pesanan saya?'])
            ->assertOk()
            ->assertJsonPath('reset', false);

        Http::assertSent(function (Request $request) {
            $payload = json_encode($request->data(), JSON_THROW_ON_ERROR);

            return $request->url() === 'https://koboi.example/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer test-secret')
                && str_contains($payload, 'INV-OWN-001')
                && ! str_contains($payload, 'INV-OTHER-999')
                && ! str_contains($payload, 'Alamat pribadi tidak boleh dikirim')
                && ! str_contains($payload, 'Catatan admin rahasia');
        });
    }

    public function test_internal_account_order_context_is_never_sent_to_koboi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Transaction::query()->create([
            'user_id' => $admin->id,
            'invoice_no' => 'INV-ADMIN-SECRET',
            'order_id' => 'ORDER-ADMIN-SECRET',
            'status' => 'paid',
            'grand_total' => 500000,
        ]);

        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":true,"answer":"Silakan gunakan halaman pesanan."}']]],
            ]),
        ]);

        $this->actingAs($admin)
            ->postJson(route('storefront.chat-ai'), ['message' => 'Tampilkan pesanan saya'])
            ->assertOk();

        Http::assertSent(function (Request $request) {
            $payload = json_encode($request->data(), JSON_THROW_ON_ERROR);

            return str_contains($payload, 'konteks pesanan tidak tersedia untuk akun internal')
                && ! str_contains($payload, 'INV-ADMIN-SECRET');
        });
    }

    public function test_payment_question_returns_payment_context_without_product_cards(): void
    {
        Product::query()->create([
            'name' => 'Elbow 45 M.F ISO4144 SS304',
            'slug' => 'elbow-45-mf-iso4144-ss304',
            'description' => 'Cara pembayaran tersedia melalui transfer.',
            'status' => 'active',
        ]);

        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":false,"answer":""}']]],
            ]),
        ]);

        $this->postJson(route('storefront.chat-ai'), [
            'message' => 'Jelaskan cara pembayaran yang tersedia.',
        ])->assertOk()
            ->assertJsonMissingPath('products.0');

        Http::assertSent(fn (Request $request) => str_contains(json_encode($request->data(), JSON_THROW_ON_ERROR), 'METODE PEMBAYARAN'));
    }

    public function test_chat_rate_limit_returns_friendly_message(): void
    {
        Http::fake([
            'https://koboi.example/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"in_scope":true,"answer":"Halo."}']]],
            ]),
        ]);

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->postJson(route('storefront.chat-ai'), ['message' => 'nomor CS'])->assertOk();
        }

        $this->postJson(route('storefront.chat-ai'), ['message' => 'nomor CS'])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Pertanyaan Anda terlalu banyak dalam waktu singkat. Tunggu sekitar 1 menit, lalu coba lagi ya.');
    }

    public function test_chat_input_is_bounded(): void
    {
        $this->postJson(route('storefront.chat-ai'), [
            'message' => str_repeat('a', 1001),
        ])->assertUnprocessable()->assertJsonValidationErrors('message');

        Http::assertNothingSent();
    }
}
