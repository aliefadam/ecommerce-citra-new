<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\VariantSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@citra.com',
            'password' => Hash::make('123123'),
        ]);

        $this->call([
            // ProductSeeder::class,
            CategorySeeder::class,
            VariantSeeder::class,
        ]);
    }
}
