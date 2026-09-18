<?php

namespace Tests\Feature;

use App\Models\MainCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MainCategoryImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_category_image_can_be_replaced(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('main-categories/old.webp', 'old-image');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = MainCategory::query()->create([
            'name' => 'Fitting',
            'slug' => 'fitting',
            'image' => 'main-categories/old.webp',
        ]);

        $response = $this->actingAs($admin)->put(route('main-categories.update', $category), [
            'name' => 'Fitting Updated',
            'image_url' => $category->image,
            'image_file' => UploadedFile::fake()->image('replacement.png', 900, 700),
        ]);

        $response->assertRedirect(route('main-categories.index'));
        $category->refresh();

        $this->assertSame('Fitting Updated', $category->name);
        $this->assertNotSame('main-categories/old.webp', $category->image);
        $this->assertStringEndsWith('.webp', $category->image);
        Storage::disk('public')->assertExists($category->image);
        Storage::disk('public')->assertMissing('main-categories/old.webp');
    }

    public function test_failed_upload_explains_server_size_limit_and_preserves_existing_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('main-categories/old.webp', 'old-image');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = MainCategory::query()->create([
            'name' => 'Fitting',
            'slug' => 'fitting',
            'image' => 'main-categories/old.webp',
        ]);
        $failedUpload = new UploadedFile(
            __FILE__,
            'oversized-category.png',
            'image/png',
            UPLOAD_ERR_INI_SIZE,
            true
        );

        $response = $this->actingAs($admin)->put(route('main-categories.update', $category), [
            'name' => 'Fitting',
            'image_url' => $category->image,
            'image_file' => $failedUpload,
        ]);

        $response->assertSessionHasErrors([
            'image_file' => 'File gambar melebihi batas upload server. Pilih ulang gambar agar dikompres otomatis sebelum dikirim.',
        ]);
        $this->assertSame('main-categories/old.webp', $category->fresh()->image);
        Storage::disk('public')->assertExists('main-categories/old.webp');
    }
}
