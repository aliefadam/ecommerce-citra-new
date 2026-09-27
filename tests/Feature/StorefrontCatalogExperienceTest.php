<?php

namespace Tests\Feature;

use App\Models\PromoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCatalogExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_empty_catalog_is_actionable(): void
    {
        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertSee('Katalog belum tersedia')
            ->assertSee('Hubungi Kami')
            ->assertSee(route('frontend.pages.show', 'pusat-bantuan'), false);
    }

    public function test_catalog_cards_receive_authoritative_stock_unit_and_seller_data(): void
    {
        $this->seed();

        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertSee('"unit":"pcs"', false)
            ->assertSee('"stock":', false)
            ->assertSee('Satuan pcs');

        $this->get(route('frontend.kategori'))
            ->assertOk()
            ->assertSee('"unit":"pcs"', false)
            ->assertSee('"storeName":', false);

        $this->get(route('frontend.search', ['q' => 'baut']))
            ->assertOk()
            ->assertSee('"unit":"pcs"', false)
            ->assertSee('"stock":', false)
            ->assertSee('content="noindex, follow"', false);
    }

    public function test_expired_promotions_are_not_rendered_as_active(): void
    {
        $this->seed();

        $this->get(route('frontend.flash-sale'))
            ->assertOk()
            ->assertSee('Belum ada flash sale aktif saat ini.');

        $promo = PromoPage::query()->create([
            'title' => 'Promo Pengujian Sprint 3',
            'slug' => 'promo-pengujian-sprint-3',
            'is_active' => true,
        ]);
        $this->get(route('frontend.promo', $promo->slug))
            ->assertOk()
            ->assertSee('Kampanye Promo')
            ->assertDontSee('Promo Campaign');
    }
}
