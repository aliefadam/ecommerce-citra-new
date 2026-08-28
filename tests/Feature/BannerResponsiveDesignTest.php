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

    public function test_non_sixteen_by_seven_banner_is_cropped_and_saved_as_webp(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'type' => 'carousel',
            'image_file' => UploadedFile::fake()->image('banner.jpg', 1600, 800),
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('banners.index'));
        $banner = Banner::query()->sole();
        $this->assertStringEndsWith('.webp', $banner->image);

        $imageInfo = getimagesizefromstring(Storage::disk('public')->get($banner->image));
        $this->assertSame(1600, $imageInfo[0]);
        $this->assertSame(700, $imageInfo[1]);
        $this->assertSame('image/webp', $imageInfo['mime']);
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
        $this->assertStringEndsWith('.webp', $banner->image);
        Storage::disk('public')->assertExists($banner->image);
    }

    public function test_editing_banner_replaces_upload_with_compressed_webp(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        Storage::disk('public')->put('banners/old.webp', 'old image');
        $banner = Banner::query()->create([
            'title' => 'Old banner',
            'type' => 'carousel',
            'image' => 'banners/old.webp',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('banners.update', $banner), [
            'title' => 'Updated banner',
            'type' => 'carousel',
            'image_url' => $banner->image,
            'image_file' => UploadedFile::fake()->image('generated-prompt.png', 1536, 1024),
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('banners.index'));
        $banner->refresh();
        $this->assertSame('Updated banner', $banner->title);
        $this->assertStringEndsWith('.webp', $banner->image);
        Storage::disk('public')->assertMissing('banners/old.webp');
        Storage::disk('public')->assertExists($banner->image);

        $imageInfo = getimagesizefromstring(Storage::disk('public')->get($banner->image));
        $this->assertSame([1600, 700], [$imageInfo[0], $imageInfo[1]]);
        $this->assertSame('image/webp', $imageInfo['mime']);
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
