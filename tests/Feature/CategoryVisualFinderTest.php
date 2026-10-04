<?php

namespace Tests\Feature;

use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryVisualFinderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_clickable_subcategory_areas_on_a_main_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mainCategory = MainCategory::query()->create(['name' => 'Piping', 'slug' => 'piping']);
        $elbow = CategoryDetail::query()->create([
            'main_category_id' => $mainCategory->id,
            'name' => 'Elbow',
            'slug' => 'elbow',
        ]);

        $response = $this->actingAs($admin)->put(route('main-categories.update', $mainCategory), [
            'name' => 'Piping',
            'diagram_image_url' => 'https://example.com/piping-drawing.webp',
            'diagram_areas' => [[
                'category_detail_id' => $elbow->id,
                'x_percent' => 20.5,
                'y_percent' => 31.25,
                'width_percent' => 12.75,
                'height_percent' => 18.5,
            ]],
        ]);

        $response->assertRedirect(route('main-categories.index'));
        $this->assertSame('https://example.com/piping-drawing.webp', $mainCategory->fresh()->diagram_image);
        $this->assertDatabaseHas('main_category_diagram_areas', [
            'main_category_id' => $mainCategory->id,
            'category_detail_id' => $elbow->id,
            'x_percent' => 20.5,
            'y_percent' => 31.25,
            'width_percent' => 12.75,
            'height_percent' => 18.5,
        ]);
    }

    public function test_area_cannot_reference_a_subcategory_from_another_main_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $piping = MainCategory::query()->create(['name' => 'Piping', 'slug' => 'piping']);
        $tools = MainCategory::query()->create(['name' => 'Tools', 'slug' => 'tools']);
        $foreignDetail = CategoryDetail::query()->create([
            'main_category_id' => $tools->id,
            'name' => 'Drill',
            'slug' => 'drill',
        ]);

        $this->actingAs($admin)->put(route('main-categories.update', $piping), [
            'name' => 'Piping',
            'diagram_image_url' => 'https://example.com/piping-drawing.webp',
            'diagram_areas' => [[
                'category_detail_id' => $foreignDetail->id,
                'x_percent' => 20,
                'y_percent' => 20,
                'width_percent' => 10,
                'height_percent' => 10,
            ]],
        ])->assertSessionHasErrors('diagram_areas.0.category_detail_id');

        $this->assertDatabaseCount('main_category_diagram_areas', 0);
    }

    public function test_selected_category_page_renders_visual_product_finder(): void
    {
        $piping = MainCategory::query()->create([
            'name' => 'Piping',
            'slug' => 'piping',
            'diagram_image' => 'https://example.com/piping-drawing.webp',
        ]);
        $elbow = CategoryDetail::query()->create([
            'main_category_id' => $piping->id,
            'name' => 'Elbow',
            'slug' => 'elbow',
        ]);
        $piping->diagramAreas()->create([
            'category_detail_id' => $elbow->id,
            'x_percent' => 20,
            'y_percent' => 30,
            'width_percent' => 15,
            'height_percent' => 12,
        ]);

        $this->get(route('frontend.kategori', ['parent' => 'piping']))
            ->assertOk()
            ->assertSee('data-testid="visual-product-finder"', false)
            ->assertSee('Tampilkan produk Elbow')
            ->assertSee('https://example.com/piping-drawing.webp', false)
            ->assertSee('activateVisualCategory');
    }
}
