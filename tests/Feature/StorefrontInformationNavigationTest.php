<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontInformationNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_information_navigation_links_to_available_pages(): void
    {
        $response = $this->get(route('frontend.pages.show', 'technical'));

        $response->assertOk()
            ->assertSee('Technical')
            ->assertSee(route('frontend.pages.show', 'project'), false)
            ->assertSee(route('frontend.pages.show', 'cara-belanja'), false)
            ->assertSee(route('frontend.pages.show', 'tentang-boq'), false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_new_information_pages_have_useful_fallback_content(): void
    {
        $this->get(route('frontend.pages.show', 'project'))
            ->assertOk()
            ->assertSee('Dukungan kebutuhan proyek');

        $this->get(route('frontend.pages.show', 'tentang-boq'))
            ->assertOk()
            ->assertSee('Apa itu BOQ?');

        $this->get(route('frontend.pages.show', 'cara-belanja'))
            ->assertOk()
            ->assertSee('Temukan produk')
            ->assertSee('Panduan visual berbelanja')
            ->assertSee('imgs/how-to-shop/step-1.jpg', false)
            ->assertSee('imgs/how-to-shop/step-2.jpg', false)
            ->assertSee('imgs/how-to-shop/step-3.jpg', false);
    }

    public function test_information_pages_use_english_copy_when_selected(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get(route('frontend.pages.show', 'cara-belanja'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('How to Shop')
            ->assertSee('Visual shopping guide')
            ->assertSee('Review your cart')
            ->assertSee('Privacy Policy')
            ->assertDontSee('Cara Belanja')
            ->assertDontSee('Informasi Pelanggan');
    }
}
