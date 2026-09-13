<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResetProductionDataCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    public function test_reset_is_cancelled_when_confirmation_phrase_does_not_match(): void
    {
        User::factory()->create();

        $this->artisan('ops:reset-production-data', ['--without-admin' => true])
            ->expectsQuestion('Ketik RESET DATA untuk melanjutkan', 'tidak')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_it_resets_all_business_data_cleans_uploads_and_creates_initial_admin(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('public')->put('product-variants/old.webp', 'old-product');
        Storage::disk('local')->put('payment-proofs/old.webp', 'old-proof');
        Storage::disk('local')->put('backups/keep.zip', 'backup');

        $oldUser = User::factory()->create(['email' => 'customer@example.com']);
        Product::query()->create([
            'name' => 'Produk Lama',
            'slug' => 'produk-lama',
        ]);

        $this->artisan('ops:reset-production-data', ['--force' => true])
            ->expectsConfirmation('Buat akun super admin awal setelah reset?', 'yes')
            ->expectsQuestion('Nama admin', 'Admin Production')
            ->expectsQuestion('Email admin', 'admin@example.com')
            ->expectsQuestion('Password admin (minimal 12 karakter)', 'password-production')
            ->expectsQuestion('Ulangi password admin', 'password-production')
            ->expectsQuestion('Ketik RESET DATA untuk melanjutkan', 'RESET DATA')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $oldUser->id, 'email' => 'customer@example.com']);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('users', 1);

        $admin = User::query()->sole();
        $this->assertSame('admin', $admin->role);
        $this->assertSame('admin@example.com', $admin->email);
        $this->assertTrue(Hash::check('password-production', $admin->password));

        Storage::disk('public')->assertMissing('product-variants/old.webp');
        Storage::disk('local')->assertMissing('payment-proofs/old.webp');
        Storage::disk('local')->assertExists('backups/keep.zip');
    }
}
