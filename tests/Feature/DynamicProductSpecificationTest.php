<?php

namespace Tests\Feature;

use App\Models\AttributeDefinition;
use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\SpecificationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class DynamicProductSpecificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_templates_are_available_and_can_be_assigned_to_a_category(): void
    {
        $this->assertDatabaseHas('specification_templates', ['code' => 'bolt', 'is_active' => true]);
        $this->assertDatabaseHas('specification_templates', ['code' => 'pipe', 'is_active' => true]);
        $this->assertDatabaseHas('specification_templates', ['code' => 'valve', 'is_active' => true]);
        $this->assertDatabaseHas('specification_templates', ['code' => 'flange', 'is_active' => true]);
        $this->assertDatabaseHas('specification_templates', ['code' => 'nut', 'is_active' => true]);

        [$main, $detail] = $this->categoryFixture();
        $pipe = SpecificationTemplate::query()->where('code', 'pipe')->firstOrFail();

        $this->actingAs($this->admin())->put(route('category-details.update', $detail), [
            'main_category_id' => $main->id,
            'name' => $detail->name,
            'specification_template_id' => $pipe->id,
        ])->assertRedirect(route('category-details.index'));

        $this->assertSame($pipe->id, $detail->fresh()->specification_template_id);
    }

    public function test_creating_a_main_category_also_creates_and_opens_its_empty_template(): void
    {
        $response = $this->actingAs($this->admin())->post(route('main-categories.store'), [
            'name' => 'Fastener Khusus',
        ]);

        $category = MainCategory::query()->where('name', 'Fastener Khusus')->firstOrFail();
        $template = SpecificationTemplate::query()->findOrFail($category->default_specification_template_id);

        $response->assertRedirect(route('specification-templates.edit', $template));
        $this->assertSame('Fastener Khusus - Spesifikasi', $template->name);
        $this->assertSame('fastener_khusus', $template->code);
        $this->assertTrue($template->is_active);
        $this->assertCount(0, $template->fields);
    }

    public function test_creating_a_detail_category_also_creates_and_opens_its_own_template(): void
    {
        $mainCategory = MainCategory::create([
            'name' => 'Fastener',
            'slug' => 'fastener',
        ]);

        $response = $this->actingAs($this->admin())->post(route('category-details.store'), [
            'main_category_id' => $mainCategory->id,
            'name' => 'Baut Tanam',
        ]);

        $category = CategoryDetail::query()->where('name', 'Baut Tanam')->firstOrFail();
        $template = SpecificationTemplate::query()->findOrFail($category->specification_template_id);

        $response->assertRedirect(route('specification-templates.edit', $template));
        $this->assertSame('Baut Tanam - Spesifikasi', $template->name);
        $this->assertSame('baut_tanam', $template->code);
        $this->assertCount(0, $template->fields);
    }

    public function test_quick_added_detail_category_receives_an_automatic_template(): void
    {
        MainCategory::create([
            'name' => 'Kategori Pertama',
            'slug' => 'kategori-pertama',
        ]);

        $response = $this->actingAs($this->admin())->postJson(route('categories.quick-add'), [
            'name' => 'Quick Coupling',
        ]);

        $category = CategoryDetail::query()->where('name', 'Quick Coupling')->firstOrFail();

        $response->assertOk()
            ->assertJsonPath('id', $category->id)
            ->assertJsonPath('template_id', $category->specification_template_id);
        $this->assertNotNull($category->specification_template_id);
        $this->assertDatabaseHas('specification_templates', [
            'id' => $category->specification_template_id,
            'code' => 'quick_coupling',
            'is_active' => true,
        ]);
    }

    public function test_existing_bolt_category_mapping_is_sent_to_the_product_form(): void
    {
        $bolt = SpecificationTemplate::query()->where('code', 'bolt')->firstOrFail();
        $main = MainCategory::create([
            'name' => 'Bolt Existing',
            'slug' => 'bolt-existing',
            'default_specification_template_id' => $bolt->id,
        ]);
        CategoryDetail::create([
            'main_category_id' => $main->id,
            'name' => 'Hex Bolt Existing',
            'slug' => 'hex-bolt-existing',
        ]);

        $response = $this->actingAs($this->admin())->get(route('products.create'));
        $response->assertOk();
        $categoryPayload = collect($response->viewData('categories'))
            ->first(fn ($category) => $category['type'] === 'detail' && (int) $category['id'] === (int) CategoryDetail::query()->where('slug', 'hex-bolt-existing')->value('id'));

        $this->assertSame($bolt->id, (int) $categoryPayload['templateId']);
        $this->assertSame('bolt', $response->viewData('specificationTemplates')[(string) $bolt->id]['code']);
    }

    public function test_pipe_product_requires_fields_from_its_template(): void
    {
        [, $detail] = $this->categoryFixture('pipe');
        $nominalSize = AttributeDefinition::query()->where('code', 'nominal_size')->firstOrFail();

        $response = $this->actingAs($this->admin())
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => 'Steel Pipe Test',
                'category_detail_id' => $detail->id,
                'status' => 'active',
                'variants' => [[
                    'price' => 850000,
                    'stock' => 20,
                    'weight_grams' => 1000,
                    'attributes' => [
                        $nominalSize->id => [
                            'attribute_definition_id' => $nominalSize->id,
                            'value_text' => '4',
                        ],
                    ],
                ]],
            ]);

        $response->assertRedirect(route('products.create'));
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('products', ['name' => 'Steel Pipe Test']);
    }

    public function test_pipe_product_is_created_with_dynamic_attributes_and_visible_on_storefront(): void
    {
        [, $detail] = $this->categoryFixture('pipe');
        $attributes = $this->attributePayload([
            'nominal_size' => '4',
            'schedule_class' => 'Sch 40',
            'material' => 'Carbon Steel',
            'thickness_mm' => '6.02',
            'pipe_length_m' => '6',
            'standard' => 'ASTM A106',
        ]);

        $response = $this->actingAs($this->admin())->post(route('products.store'), [
            'name' => 'Steel Pipe Dynamic',
            'category_detail_id' => $detail->id,
            'status' => 'active',
            'variants' => [[
                'price' => 850000,
                'stock' => 20,
                'weight_grams' => 15000,
                'attributes' => $attributes,
            ]],
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasNoErrors();

        $product = Product::query()->where('name', 'Steel Pipe Dynamic')->firstOrFail();
        $variant = $product->productVariants()->with(['variant', 'attributeValues.definition'])->firstOrFail();
        $this->assertStringContainsString('Sch 40', (string) $variant->variant?->value);
        $this->assertStringContainsString('ASTM A106', $variant->attributeSummary());

        $this->get(route('frontend.detail-produk', ['slug' => $product->slug]))
            ->assertOk()
            ->assertSee('Nominal Size (inch)')
            ->assertSee('Schedule / Class')
            ->assertSee('ASTM A106');
    }

    public function test_duplicate_dynamic_variant_combination_is_rejected(): void
    {
        [, $detail] = $this->categoryFixture('nut');
        $attributes = $this->attributePayload([
            'thread_size' => 'M12', 'pitch_mm' => '1.75', 'grade' => '8',
            'material' => 'Carbon Steel', 'finish_coating' => 'Zinc Plated',
        ]);
        $variant = fn () => ['price' => 750, 'stock' => 10, 'weight_grams' => 20, 'attributes' => $attributes];

        $response = $this->actingAs($this->admin())->from(route('products.create'))->post(route('products.store'), [
            'name' => 'Hex Nut Duplicate',
            'category_detail_id' => $detail->id,
            'status' => 'active',
            'variants' => [$variant(), $variant()],
        ]);

        $response->assertRedirect(route('products.create'));
        $response->assertSessionHasErrors('variants.1');
        $this->assertDatabaseMissing('products', ['name' => 'Hex Nut Duplicate']);
    }

    public function test_dynamic_excel_template_uses_attribute_codes_from_selected_template(): void
    {
        $response = $this->actingAs($this->admin())->get(route('products.import-template', ['template' => 'pipe']));
        $response->assertOk();
        $temporaryFile = tempnam(sys_get_temp_dir(), 'dynamic-product-template-');

        try {
            file_put_contents($temporaryFile, $response->streamedContent());
            $sheet = IOFactory::load($temporaryFile)->getActiveSheet();
            $this->assertSame('nominal_size', $sheet->getCell('M1')->getValue());
            $this->assertSame('schedule_class', $sheet->getCell('N1')->getValue());
            $this->assertSame('standard', $sheet->getCell('R1')->getValue());
        } finally {
            if (is_string($temporaryFile) && file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    public function test_admin_can_add_a_new_field_to_a_template_without_code_changes(): void
    {
        $template = SpecificationTemplate::query()->where('code', 'valve')->firstOrFail();

        $this->actingAs($this->admin())->post(route('specification-templates.attributes.store', $template), [
            'attribute_name' => 'Operating Temperature',
            'attribute_code' => 'operating_temperature',
            'data_type' => 'number',
            'unit' => 'C',
        ])->assertRedirect();

        $definition = AttributeDefinition::query()->where('code', 'operating_temperature')->firstOrFail();
        $this->assertDatabaseHas('specification_template_fields', [
            'specification_template_id' => $template->id,
            'attribute_definition_id' => $definition->id,
            'is_active' => true,
        ]);
    }

    private function categoryFixture(?string $templateCode = null): array
    {
        $main = MainCategory::create(['name' => 'Industrial '.uniqid(), 'slug' => 'industrial-'.uniqid()]);
        $template = $templateCode ? SpecificationTemplate::query()->where('code', $templateCode)->firstOrFail() : null;
        $detail = CategoryDetail::create([
            'main_category_id' => $main->id,
            'specification_template_id' => $template?->id,
            'name' => 'Detail '.uniqid(),
            'slug' => 'detail-'.uniqid(),
        ]);

        return [$main, $detail];
    }

    private function attributePayload(array $values): array
    {
        return collect($values)->mapWithKeys(function ($value, $code) {
            $definition = AttributeDefinition::query()->where('code', $code)->firstOrFail();
            $valueKey = $definition->data_type === 'number' ? 'value_number' : 'value_text';

            return [$definition->id => [
                'attribute_definition_id' => $definition->id,
                $valueKey => $value,
            ]];
        })->all();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
