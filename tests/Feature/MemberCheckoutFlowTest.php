<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesCheckoutShippingQuotes;
use Tests\TestCase;

class MemberCheckoutFlowTest extends TestCase
{
    use CreatesCheckoutShippingQuotes;
    use RefreshDatabase;

    public function test_member_can_checkout_multiple_selected_cart_items_with_owned_address(): void
    {
        Mail::fake();
        $member = User::factory()->create(['email' => 'member-checkout@example.test']);
        $address = $this->makeAddress($member, 'Alamat Member');
        [$firstVariant, $secondVariant] = $this->makeVariants();
        $firstCart = Cart::create([
            'user_id' => $member->id,
            'product_variant_id' => $firstVariant->id,
            'quantity' => 2,
        ]);
        $secondCart = Cart::create([
            'user_id' => $member->id,
            'product_variant_id' => $secondVariant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($member)
            ->postJson(route('frontend.cart.prepare-checkout'), [
                'cart_ids' => [$firstCart->id, $secondCart->id],
            ])
            ->assertOk();

        $items = [
            $this->checkoutItem($firstVariant, 2),
            $this->checkoutItem($secondVariant, 1),
        ];
        $response = $this->postJson(route('frontend.checkout.manual-payment'), [
            'items' => $items,
            'company_id' => $firstVariant->product->company_id,
            'shipping_cost' => 15000,
            'shipping_label' => 'JNE REG',
            'shipping_quote_token' => $this->checkoutShippingQuote($items, (int) $firstVariant->product->company_id, 99, 15000, 'JNE REG'),
            'address_id' => $address->id,
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $transaction = Transaction::query()->firstOrFail();

        $this->assertSame($member->id, $transaction->user_id);
        $this->assertSame('menunggu_verifikasi', $transaction->status);
        $this->assertSame('Alamat Member', $transaction->shipping_recipient_name);
        $this->assertSame('Setiabudi', $transaction->shipping_district);
        $this->assertSame(215000, (int) $transaction->subtotal_amount);
        $this->assertCount(2, $transaction->details);
        $this->assertSame(2, (int) $transaction->details->firstWhere('product_variant_id', $firstVariant->id)->quantity);
        $this->assertDatabaseMissing('carts', ['id' => $firstCart->id]);
        $this->assertDatabaseMissing('carts', ['id' => $secondCart->id]);
        $this->assertSame(10, (int) $firstVariant->fresh()->stock);
        $this->assertSame(10, (int) $secondVariant->fresh()->stock);
    }

    public function test_member_cannot_checkout_using_another_users_address(): void
    {
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $otherAddress = $this->makeAddress($otherMember, 'Alamat Orang Lain');
        [$variant] = $this->makeVariants();

        $this->actingAs($member)
            ->withSession([
                'checkout' => [
                    'source' => 'buy_now',
                    'items' => [$this->checkoutItem($variant, 1)],
                ],
            ])
            ->postJson(route('frontend.checkout.manual-payment'), [
                'items' => [$this->checkoutItem($variant, 1)],
                'company_id' => $variant->product->company_id,
                'shipping_cost' => 10000,
                'shipping_label' => 'JNE REG',
                'shipping_quote_token' => $this->checkoutShippingQuote([$this->checkoutItem($variant, 1)], (int) $variant->product->company_id, 99, 10000, 'JNE REG'),
                'address_id' => $otherAddress->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('address_id');

        $this->assertDatabaseCount('transactions', 0);
    }

    private function makeAddress(User $user, string $recipient): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'label' => 'Kantor',
            'recipient_name' => $recipient,
            'phone_country_code' => '+62',
            'phone_number' => '8123456789',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Setiabudi',
            'postal_code' => '12910',
            'destination_id' => 99,
            'address_line' => 'Jl. Member Checkout No. 1',
            'is_primary' => true,
        ]);
    }

    private function makeVariants(): array
    {
        $company = Company::query()->firstOrFail();
        $result = [];

        foreach ([['Produk Member A', 75000], ['Produk Member B', 65000]] as [$name, $price]) {
            $suffix = Str::upper(Str::random(6));
            $product = Product::create([
                'company_id' => $company->id,
                'name' => $name,
                'slug' => Str::slug($name).'-'.strtolower($suffix),
                'status' => 'active',
            ]);
            $variant = Variant::create(['name' => 'Ukuran', 'value' => $suffix]);
            $result[] = ProductVariant::create([
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'sku' => 'MEMBER-'.$suffix,
                'price' => $price,
                'stock' => 10,
                'weight_grams' => 500,
            ]);
        }

        return $result;
    }

    private function checkoutItem(ProductVariant $variant, int $quantity): array
    {
        return [
            'id' => $variant->product_id,
            'productVariantId' => $variant->id,
            'companyId' => $variant->product->company_id,
            'name' => $variant->product->name,
            'variant' => $variant->variant->value,
            'price' => (int) $variant->price,
            'qty' => $quantity,
        ];
    }
}
