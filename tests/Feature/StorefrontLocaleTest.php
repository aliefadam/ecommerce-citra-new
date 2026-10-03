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
            ->assertSee('Chat with us on WhatsApp')
            ->assertSee('class="ri-whatsapp-line', false)
            ->assertDontSee('Konsultasi WhatsApp')
            ->assertDontSee('>WH</a>', false)
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

    public function test_help_center_uses_complete_english_copy_without_mixed_labels(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.pages.show', 'pusat-bantuan'))
            ->assertOk()
            ->assertSee('Help Categories')
            ->assertSee('Frequently Asked Questions')
            ->assertSee('How do I place an order?')
            ->assertSee('Products &amp; Stock', false)
            ->assertSee('Need help with a project?')
            ->assertSee('Help Center')
            ->assertDontSee('>Contact Us</a>', false)
            ->assertDontSee('Kategori Bantuan')
            ->assertDontSee('Cara Pemesanan')
            ->assertDontSee('Products &amp; Stok', false)
            ->assertDontSee('Butuh bantuan untuk kebutuhan proyek?');
    }

    public function test_secondary_customer_pages_render_english_interface_copy(): void
    {
        $this->seed();

        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.order-tracking.index'))
            ->assertOk()
            ->assertSee('Track your order')
            ->assertSee('Order Email')
            ->assertDontSee('Lacak perjalanan pesananmu');

        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.flash-sale'))
            ->assertOk()
            ->assertSee('Limited-Time Offers')
            ->assertSee('View Offers')
            ->assertDontSee('Promo Terbatas');

        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.redeem-point'))
            ->assertOk()
            ->assertSee('Redeem your points for selected products')
            ->assertDontSee('Tukarkan point kamu dengan produk pilihan');

        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.blog.index'))
            ->assertOk()
            ->assertSee('Articles & Insights', false)
            ->assertDontSee('Artikel &amp; Informasi', false);
    }
}
