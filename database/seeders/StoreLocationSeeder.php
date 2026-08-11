<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\StoreLocation;
use Illuminate\Database\Seeder;

class StoreLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = Company::query()->orderBy('id')->value('id');

        if (! $companyId) {
            throw new \RuntimeException('Perusahaan utama belum tersedia untuk StoreLocationSeeder.');
        }

        StoreLocation::updateOrCreate(
            ['company_id' => $companyId, 'label' => 'Lokasi Toko Utama'],
            [
                'province_id' => 18,
                'city_id' => 577,
                'city_name' => 'SURABAYA',
                'province_name' => 'JAWA TIMUR',
                'is_active' => true,
            ]
        );
    }
}
