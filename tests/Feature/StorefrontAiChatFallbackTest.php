<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Variant;
use App\Support\BoqiFaq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StorefrontAiChatFallbackTest extends TestCase
{
    use RefreshDatabase;

    private const LLM_URL = 'https://koboi.example/v1/chat/completions';

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

    private function fakeLlm(string $content): void
    {
        Http::fake([self::LLM_URL => Http::response(['choices' => [['message' => ['content' => $content]]]])]);
    }

    private function fakeRejection(): void
    {
        $this->fakeLlm('{"in_scope":false,"answer":""}');
    }

    private function makeProduct(string $name, int $price, string $description = 'Sambungan pipa stainless.', int $stock = 5): Product
    {
        $product = Product::query()->create([
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'description' => $description,
            'status' => 'active',
        ]);
        $variant = Variant::query()->firstOrCreate(['name' => 'Ukuran', 'value' => '1/2 inch']);
        ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => strtoupper(str($name)->slug()->toString()),
            'price' => $price,
            'stock' => $stock,
        ]);

        return $product;
    }

    private function llmPayload(): string
    {
        $payload = '';
        Http::assertSent(function (Request $request) use (&$payload) {
            $payload = json_encode($request->data(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $request->url() === self::LLM_URL;
        });

        return $payload;
    }

    public function test_greeting_is_answered_without_llm_and_without_product_cards(): void
    {
        Http::fake();
        $this->makeProduct('Half Coupling SS304', 30000);

        $this->postJson(route('storefront.chat-ai'), ['message' => 'halo kak'])
            ->assertOk()
            ->assertJsonPath('reset', false)
            ->assertJsonMissingPath('products')
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'IBOQ'));

        Http::assertNothingSent();
    }

    public function test_thanks_never_resets_the_conversation(): void
    {
        Http::fake();

        foreach (['terima kasih', 'makasih ya kak', 'ok sip', 'Terima kasih banyak atas bantuannya!'] as $message) {
            $this->postJson(route('storefront.chat-ai'), ['message' => $message])
                ->assertOk()
                ->assertJsonPath('reset', false)
                ->assertJsonPath('message', fn (string $text) => str_contains($text, 'Sama-sama'));
        }

        Http::assertNothingSent();
    }

    public function test_wrongly_rejected_service_questions_are_answered_from_faq(): void
    {
        $this->fakeRejection();
        $this->makeProduct('Equal Tee SS304', 20000, 'Fitting dengan garansi, alamat pabrik, jam kerja, dan refund.');

        $cases = [
            'bisa COD?' => 'cod',
            'bisa cicilan atau paylater?' => 'pembayaran_lain',
            'cara retur barang gimana?' => 'retur',
            'garansinya berapa lama?' => 'garansi',
            'refund berapa lama?' => 'retur',
            'cara batalkan pesanan?' => 'pembatalan',
            'pesanan saya sudah sampai mana?' => 'lacak',
            'pengiriman pakai kurir apa saja?' => 'pengiriman',
            'alamat toko dimana? bisa ambil langsung?' => 'toko',
            'jam operasional toko?' => 'toko',
            'bisa beli grosir / partai besar, ada harga khusus?' => 'grosir',
            'bisa minta faktur pajak?' => 'pajak',
            'minimal order berapa?' => 'minimal_order',
            'cara daftar akun?' => 'akun',
            'lupa password gimana?' => 'akun',
            'kapan restock?' => 'restock',
            'ada promo atau diskon?' => 'promo',
        ];

        $settings = StoreSetting::values();
        foreach ($cases as $question => $topic) {
            $expected = BoqiFaq::fill(BoqiFaq::defaults()[BoqiFaq::key($topic)], $settings);

            $this->postJson(route('storefront.chat-ai'), ['message' => $question])
                ->assertOk()
                ->assertJsonPath('reset', false)
                ->assertJsonPath('message', $expected)
                ->assertJsonMissingPath('products');
        }
    }

    public function test_admin_revised_faq_is_sent_to_llm_and_used_as_fallback(): void
    {
        StoreSetting::setMany([BoqiFaq::key('cod') => 'COD tersedia khusus wilayah Jabodetabek.']);
        $this->fakeRejection();

        $this->postJson(route('storefront.chat-ai'), ['message' => 'bisa bayar di tempat?'])
            ->assertOk()
            ->assertJsonPath('reset', false)
            ->assertJsonPath('message', 'COD tersedia khusus wilayah Jabodetabek.');

        $payload = $this->llmPayload();
        $this->assertStringContainsString('FAQ & KEBIJAKAN TOKO', $payload);
        $this->assertStringContainsString('COD tersedia khusus wilayah Jabodetabek.', $payload);
    }

    public function test_empty_faq_topic_hands_off_to_cs_without_reset(): void
    {
        StoreSetting::setMany([BoqiFaq::key('garansi') => '']);
        $this->fakeRejection();

        $this->postJson(route('storefront.chat-ai'), ['message' => 'garansi berapa lama?'])
            ->assertOk()
            ->assertJsonPath('reset', false)
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'hubungi CS') && str_contains($message, 'wa.me'));
    }

    public function test_truly_off_topic_question_still_resets(): void
    {
        $this->fakeRejection();

        $this->postJson(route('storefront.chat-ai'), ['message' => 'buatkan puisi cinta'])
            ->assertOk()
            ->assertJsonPath('reset', true);
    }

    public function test_description_only_match_does_not_show_product_cards(): void
    {
        $this->makeProduct('Elbow 90 SS304', 18000, 'Dikemas aman untuk pengiriman dan ekspedisi jauh.');
        $this->fakeLlm('{"in_scope":true,"answer":"Kurir dipilih saat checkout."}');

        $this->postJson(route('storefront.chat-ai'), ['message' => 'kirimnya lewat apa? aman gak barangnya'])
            ->assertOk()
            ->assertJsonCount(0, 'products');
    }

    public function test_cheapest_question_returns_products_sorted_by_price(): void
    {
        $this->makeProduct('Elbow 90 SS304', 18000);
        $this->makeProduct('Banded Coupling SS304', 9000);
        $this->makeProduct('Equal Tee SS304', 26000);
        $this->fakeLlm('{"in_scope":true,"answer":"Produk termurah adalah Banded Coupling SS304."}');

        $this->postJson(route('storefront.chat-ai'), ['message' => 'barang termurah apa?'])
            ->assertOk()
            ->assertJsonPath('products.0.name', 'Banded Coupling SS304')
            ->assertJsonPath('products.1.name', 'Elbow 90 SS304');

        $this->assertStringContainsString('URUTAN: termurah', $this->llmPayload());
    }

    public function test_bestseller_question_uses_paid_sales(): void
    {
        $popular = $this->makeProduct('Elbow 45 SS304', 15000);
        $quiet = $this->makeProduct('Equal Tee SS304', 20000);
        $customer = User::factory()->create();

        foreach ([[$popular, 'paid', 9], [$quiet, 'paid', 2], [$quiet, 'unpaid', 50]] as $index => [$product, $paymentStatus, $quantity]) {
            $transaction = Transaction::query()->create([
                'user_id' => $customer->id,
                'invoice_no' => 'INV-SOLD-'.$index,
                'order_id' => 'ORDER-SOLD-'.$index,
                'status' => 'completed',
                'payment_status' => $paymentStatus,
                'grand_total' => 100000,
            ]);
            TransactionDetail::query()->create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => 10000,
                'quantity' => $quantity,
                'subtotal' => 10000 * $quantity,
            ]);
        }
        $this->fakeLlm('{"in_scope":true,"answer":"Produk terlaris: Elbow 45 SS304."}');

        $this->postJson(route('storefront.chat-ai'), ['message' => 'produk paling laris apa?'])
            ->assertOk()
            ->assertJsonPath('products.0.name', 'Elbow 45 SS304')
            ->assertJsonPath('products.1.name', 'Equal Tee SS304');
    }

    public function test_requested_older_invoice_of_the_customer_is_sent_but_not_other_customers(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        foreach (range(1, 6) as $number) {
            Transaction::query()->create([
                'user_id' => $customer->id,
                'invoice_no' => 'INV-OWN-'.$number,
                'order_id' => 'ORDER-OWN-'.$number,
                'status' => 'completed',
                'grand_total' => 100000,
            ]);
        }
        Transaction::query()->create([
            'user_id' => $other->id,
            'invoice_no' => 'INV-OTHER-1',
            'order_id' => 'ORDER-OTHER-1',
            'status' => 'paid',
            'grand_total' => 999000,
        ]);
        $this->fakeLlm('{"in_scope":true,"answer":"Invoice ditemukan."}');

        $this->actingAs($customer)
            ->postJson(route('storefront.chat-ai'), ['message' => 'cek status invoice INV-OWN-1'])
            ->assertOk();
        $payload = $this->llmPayload();
        $this->assertStringContainsString('INV-OWN-1 ', $payload);

        $this->actingAs($customer)
            ->postJson(route('storefront.chat-ai'), ['message' => 'cek invoice INV-OTHER-1'])
            ->assertOk();
        $payload = '';
        Http::assertSent(function (Request $request) use (&$payload) {
            $payload = json_encode($request->data(), JSON_UNESCAPED_SLASHES);

            return str_contains($payload, 'cek invoice INV-OTHER-1');
        });
        $this->assertStringContainsString('INV-OTHER-1 tidak ditemukan pada akun ini', $payload);
        $this->assertStringNotContainsString('999.000', $payload);
    }

    public function test_guest_order_question_points_to_login_and_order_tracking(): void
    {
        $this->fakeLlm('{"in_scope":true,"answer":"Silakan login."}');

        $this->postJson(route('storefront.chat-ai'), ['message' => 'pesanan saya sampai mana?'])->assertOk();

        $this->assertStringContainsString(route('frontend.order-tracking.index'), $this->llmPayload());
    }

    public function test_shipping_cost_question_uses_rajaongkir_estimate(): void
    {
        config()->set('services.rajaongkir.api_key', 'ro-test');
        config()->set('services.rajaongkir.base_url', 'https://rajaongkir.example/api/v1');
        StoreLocation::query()->create([
            'company_id' => Company::query()->where('slug', 'boq')->value('id'),
            'label' => 'Gudang',
            'province_id' => 6,
            'city_id' => 153,
            'city_name' => 'Jakarta Selatan',
            'province_name' => 'DKI Jakarta',
            'is_active' => true,
        ]);

        Http::fake([
            'https://rajaongkir.example/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200, 'status' => 'success'],
                'data' => [['id' => 444, 'label' => 'SURABAYA, JAWA TIMUR']],
            ]),
            'https://rajaongkir.example/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200, 'status' => 'success'],
                'data' => [
                    ['code' => 'jne', 'name' => 'JNE', 'service' => 'REG', 'cost' => 15000, 'etd' => '2-3 day'],
                    ['code' => 'jne', 'name' => 'JNE', 'service' => 'JTR', 'description' => 'Trucking', 'cost' => 5000, 'etd' => '7 day'],
                    ['code' => 'sicepat', 'name' => 'SiCepat', 'service' => 'BEST', 'cost' => 12000, 'etd' => '1-2 day'],
                ],
            ]),
            self::LLM_URL => Http::response(['choices' => [['message' => ['content' => '{"in_scope":false,"answer":""}']]]]),
        ]);

        $response = $this->postJson(route('storefront.chat-ai'), ['message' => 'ongkir ke surabaya berapa?'])
            ->assertOk()
            ->assertJsonPath('reset', false)
            ->assertJsonMissingPath('products');

        $message = (string) $response->json('message');
        $this->assertStringContainsString('SURABAYA, JAWA TIMUR', $message);
        $this->assertStringContainsString('SICEPAT BEST: Rp12.000', $message);
        $this->assertStringNotContainsString('JTR', $message);
        $this->assertLessThan(strpos($message, 'JNE REG'), strpos($message, 'SICEPAT BEST'));

        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'domestic-destination')
            && $request['search'] === 'surabaya');
    }

    public function test_active_flash_sale_is_sent_as_promo_context(): void
    {
        FlashSale::query()->create([
            'name' => 'Promo Akhir Tahun',
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'status' => 'active',
        ]);
        $this->fakeLlm('{"in_scope":true,"answer":"Ada flash sale."}');

        $this->postJson(route('storefront.chat-ai'), ['message' => 'ada promo?'])->assertOk();

        $payload = $this->llmPayload();
        $this->assertStringContainsString('Flash sale Promo Akhir Tahun (sedang berlangsung', $payload);
    }

    public function test_admin_can_revise_boqi_faq_from_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('pages.settings', ['tab' => 'boqi']))
            ->assertOk()
            ->assertSee('FAQ &amp; Kebijakan Toko untuk IBOQ', false)
            ->assertSee(BoqiFaq::key('retur'));

        $this->actingAs($admin)
            ->post(route('pages.settings.update'), [
                'section' => 'boqi_faq',
                BoqiFaq::key('retur') => '  Retur maksimal 3 hari.  ',
                BoqiFaq::key('tambahan') => 'T: Kirim luar negeri? J: Belum.',
            ])
            ->assertRedirect(route('pages.settings', ['tab' => 'boqi']));

        $settings = StoreSetting::values();
        $this->assertSame('Retur maksimal 3 hari.', $settings[BoqiFaq::key('retur')]);
        $this->assertSame('T: Kirim luar negeri? J: Belum.', $settings[BoqiFaq::key('tambahan')]);

        $this->actingAs($admin)
            ->post(route('pages.settings.update'), [
                'section' => 'boqi_faq',
                BoqiFaq::key('retur') => str_repeat('a', BoqiFaq::MAX_LENGTH + 1),
            ])
            ->assertSessionHasErrors(BoqiFaq::key('retur'));
    }

    public function test_customer_cannot_update_boqi_faq(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('pages.settings.update'), ['section' => 'boqi_faq', BoqiFaq::key('cod') => 'hack'])
            ->assertRedirect();

        $this->assertNotSame('hack', StoreSetting::values()[BoqiFaq::key('cod')]);
    }
}
