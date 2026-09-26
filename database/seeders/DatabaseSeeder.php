<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AddressSeeder::class,
            MainCategorySeeder::class,
            CategoryDetailSeeder::class,
            VariantSeeder::class,
            ProductSeeder::class,
            BannerSeeder::class,
            StoreLocationSeeder::class,
        ]);

        if (app()->environment('e2e')) {
            $this->call([
                CompanySeeder::class,
                BrowserE2eSeeder::class,
            ]);
        }
    }
}
