<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerResponsiveDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_upload_must_use_the_shared_sixteen_by_seven_ratio(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'type' => 'carousel',
            'image_file' => UploadedFile::fake()->image('banner.jpg', 1600, 800),
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('image_file');
        $this->assertDatabaseCount('banners', 0);
    }

    public function test_sixteen_by_seven_banner_upload_is_accepted(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'type' => 'carousel',
            'image_file' => UploadedFile::fake()->image('banner.jpg', 1600, 700),
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('banners.index'));
        $banner = Banner::query()->sole();
        $this->assertSame('carousel', $banner->type);
        Storage::disk('public')->assertExists($banner->image);
    }

    public function test_only_two_side_banners_can_be_active(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Banner::query()->create([
            'title' => 'Side one',
            'type' => 'side',
            'image' => 'https://example.com/side-one.webp',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Banner::query()->create([
            'title' => 'Side two',
            'type' => 'side',
            'image' => 'https://example.com/side-two.webp',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'type' => 'side',
            'image_url' => 'https://example.com/side-three.webp',
            'sort_order' => 3,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('is_active');
        $this->assertDatabaseCount('banners', 2);
    }
}
