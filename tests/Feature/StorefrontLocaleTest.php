<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_defaults_to_indonesian(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="id">', false)
            ->assertSee('Akun Saya')
            ->assertSee('Pengiriman ke Seluruh Indonesia');
    }

    public function test_customer_can_switch_to_english_and_locale_persists(): void
    {
        $this->seed();

        $this->get(route('locale.switch', ['locale' => 'en', 'redirect' => '/kategori']))
            ->assertRedirect('/kategori')
            ->assertSessionHas('locale', 'en');

        $this->get('/kategori')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('My Account')
            ->assertSee('Nationwide Shipping Across Indonesia')
            ->assertSee('All Categories')
            ->assertSee('aria-current="true"', false)
            ->assertSee('>EN</a>', false);
    }

    public function test_locale_switch_rejects_unsupported_locales_and_external_redirects(): void
    {
        $this->get('/language/fr')->assertNotFound();

        $this->get(route('locale.switch', ['locale' => 'en', 'redirect' => '//example.com']))
            ->assertRedirect('/');
    }

    public function test_authentication_pages_use_the_selected_language(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Enter your account details to continue.')
            ->assertSee('Forgot your password?')
            ->assertSee('Sign in with Google');
    }

    public function test_english_locale_covers_customer_page_content_and_interaction_copy(): void
    {
        $this->seed();

        $product = Product::query()->whereNotNull('slug')->firstOrFail();
        $user = User::query()->whereNotNull('email')->firstOrFail();

        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.detail-produk', $product->slug))
            ->assertOk()
            ->assertSee('Product Details')
            ->assertSee('Add to Cart')
            ->assertSee('Product Specifications');

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('frontend.profil'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Shipping Address')
            ->assertSee('Order History');

        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.cart'))
            ->assertOk()
            ->assertSee('Cart Summary');
    }
}
