<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontDesignSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_shell_exposes_accessible_navigation_and_permanent_legal_links(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('data-storefront-shell', false)
            ->assertSee('aria-label="Navigasi utama"', false)
            ->assertSee('aria-controls="ecCategoryDropdown"', false)
            ->assertSee('aria-label="Navigasi cepat"', false)
            ->assertSee(route('frontend.pages.show', 'kebijakan-privasi'), false)
            ->assertSee(route('frontend.pages.show', 'syarat-ketentuan'), false)
            ->assertDontSee('footerModalOpen', false);
    }

    public function test_legal_routes_have_accessible_fallback_content_without_database_pages(): void
    {
        $this->get('/pages/kebijakan-privasi')
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('Data yang diproses');

        $this->get('/pages/syarat-ketentuan')
            ->assertOk()
            ->assertSee('Syarat dan Ketentuan')
            ->assertSee('Informasi produk');

        $this->get('/pages/halaman-tidak-dikenal')->assertNotFound();
    }

    public function test_component_foundations_render_semantic_states(): void
    {
        $button = $this->blade('<x-ui.button variant="primary" disabled>Simpan</x-ui.button>');
        $button->assertSee('ec-btn-primary', false)->assertSee('disabled', false);

        $field = $this->blade('<x-ui.field label="Email" name="email" error="Email tidak valid" />');
        $field->assertSee('aria-invalid="true"', false)->assertSee('Email tidak valid');

        $modal = $this->blade('<x-ui.modal id="test-dialog" title="Konfirmasi">Isi dialog</x-ui.modal>');
        $modal->assertSee('role="dialog"', false)->assertSee('aria-modal="true"', false)->assertSee('data-ec-dialog-close', false);
    }

    public function test_customer_bundle_contains_central_tokens_and_shell_behavior(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $js = file_get_contents(resource_path('js/storefront-shell.js'));

        $this->assertStringContainsString('--ec-z-modal', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringContainsString('env(safe-area-inset-bottom)', $css);
        $this->assertStringContainsString('focusableSelector', $js);
        $this->assertStringContainsString("event.key === 'Escape'", $js);
    }
}
