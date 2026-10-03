<?php

namespace Tests\Feature;

use App\Models\PromoPage;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class StorefrontCatalogExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_empty_catalog_is_actionable(): void
    {
        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertSee('Sedang garap proyek apa?')
            ->assertSee('Oil &amp; Gas', false)
            ->assertSee('Chemical &amp; Petrochemical', false)
            ->assertSee('Konsultasi via WhatsApp')
            ->assertSee('https://wa.me/6281389365955?text=', false)
            ->assertSee('Katalog belum tersedia')
            ->assertSee('Hubungi Kami')
            ->assertSee(route('frontend.pages.show', 'pusat-bantuan'), false);
    }

    public function test_homepage_project_consultation_prefills_whatsapp_message(): void
    {
        View::share('appStoreSettings', array_merge(StoreSetting::defaults(), [
            'social_whatsapp' => 'https://wa.me/628123456789',
        ]));

        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertSee('Konsultasi via WhatsApp')
            ->assertSee('https://wa.me/628123456789?text=Halo%2C%20saya%20sedang%20mengerjakan%20proyek%20Oil%20%26%20Gas%20dan%20membutuhkan%20bantuan%20memilih%20produk%20serta%20spesifikasi%20yang%20sesuai.', false);
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
