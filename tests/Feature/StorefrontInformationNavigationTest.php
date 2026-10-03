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
            ->assertSee('Temukan produk');
    }
}
