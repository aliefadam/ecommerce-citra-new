<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
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
            ->assertSee('Chat CS');
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

    public function test_chat_input_is_bounded(): void
    {
        $this->postJson(route('storefront.chat-ai'), [
            'message' => str_repeat('a', 1001),
        ])->assertUnprocessable()->assertJsonValidationErrors('message');

        Http::assertNothingSent();
    }
}
