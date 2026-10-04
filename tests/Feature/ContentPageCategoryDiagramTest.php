<?php

namespace Tests\Feature;

use App\Models\CategoryDetail;
use App\Models\ContentPage;
use App\Models\MainCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPageCategoryDiagramTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_form_shows_the_visual_category_hotspot_editor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $piping = MainCategory::query()->create(['name' => 'Piping', 'slug' => 'piping']);
        CategoryDetail::query()->create([
            'main_category_id' => $piping->id,
            'name' => 'Elbow',
            'slug' => 'elbow',
        ]);

        $this->actingAs($admin)
            ->get(route('content-pages.create'))
            ->assertOk()
            ->assertSee('Drawing Produk Interaktif')
            ->assertSee('diagram_image_file', false)
            ->assertSee('Piping › Elbow');
    }

    public function test_admin_can_attach_category_hotspots_to_a_content_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $elbow = MainCategory::query()->create(['name' => 'Elbow', 'slug' => 'elbow']);
        $valve = MainCategory::query()->create(['name' => 'Valve', 'slug' => 'valve']);

        $response = $this->actingAs($admin)->post(route('content-pages.store'), [
            'type' => ContentPage::TYPE_PAGE,
            'title' => 'Drawing Pemipaan',
            'slug' => 'drawing-pemipaan',
            'content' => '<p>Pilih bagian drawing.</p>',
            'diagram_image_url' => 'https://example.com/piping.webp',
            'hotspots' => [
                ['main_category_id' => $elbow->id, 'x_percent' => 24.55, 'y_percent' => 37.25],
                ['main_category_id' => $valve->id, 'x_percent' => 68.10, 'y_percent' => 52.80],
            ],
            'is_active' => 1,
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('content-pages.index'));

        $page = ContentPage::query()->where('slug', 'drawing-pemipaan')->sole();
        $this->assertSame('https://example.com/piping.webp', $page->diagram_image);
        $this->assertDatabaseHas('content_page_category_hotspots', [
            'content_page_id' => $page->id,
            'main_category_id' => $elbow->id,
            'x_percent' => 24.55,
            'y_percent' => 37.25,
        ]);
        $this->assertDatabaseCount('content_page_category_hotspots', 2);
    }

    public function test_public_page_renders_clickable_category_diagram(): void
    {
        $mainCategory = MainCategory::query()->create(['name' => 'Piping', 'slug' => 'piping']);
        $category = CategoryDetail::query()->create([
            'main_category_id' => $mainCategory->id,
            'name' => 'Elbow',
            'slug' => 'elbow',
        ]);
        $page = ContentPage::query()->create([
            'type' => ContentPage::TYPE_PAGE,
            'title' => 'Drawing Pemipaan',
            'slug' => 'drawing-pemipaan',
            'diagram_image' => 'https://example.com/piping.webp',
            'is_active' => true,
            'published_at' => now()->subMinute(),
        ]);
        $page->categoryHotspots()->create([
            'category_detail_id' => $category->id,
            'x_percent' => 24.55,
            'y_percent' => 37.25,
        ]);

        $this->get(route('frontend.pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Pilih komponen pada gambar')
            ->assertSee('Tampilkan produk kategori Elbow')
            ->assertSee(route('frontend.kategori', ['parent' => 'piping', 'category' => 'elbow']))
            ->assertSee('https://example.com/piping.webp', false);
    }

    public function test_hotspot_coordinates_must_stay_inside_the_drawing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = MainCategory::query()->create(['name' => 'Elbow', 'slug' => 'elbow']);

        $response = $this->actingAs($admin)->post(route('content-pages.store'), [
            'type' => ContentPage::TYPE_PAGE,
            'title' => 'Drawing Pemipaan',
            'diagram_image_url' => 'https://example.com/piping.webp',
            'hotspots' => [[
                'main_category_id' => $category->id,
                'x_percent' => 101,
                'y_percent' => -1,
            ]],
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors([
            'hotspots.0.x_percent',
            'hotspots.0.y_percent',
        ]);
        $this->assertDatabaseCount('content_pages', 0);
    }
}
