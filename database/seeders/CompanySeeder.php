<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Seed 3 perusahaan contoh untuk uji coba fitur multi-company (company switcher, scoping, dst).
     * BOQ sudah ada dari migrasi Fase 1, jadi setelah seeder ini jalan total ada 4 perusahaan.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'PT Dua Sejahtera',
                'slug' => 'pt-dua-sejahtera',
                'legal_name' => 'PT Dua Sejahtera Indonesia',
                'address' => 'Jl. Industri Raya No. 12, Surabaya',
                'phone' => '0311234567',
                'email' => 'contact@duasejahtera.test',
                'npwp' => '01.234.567.8-901.000',
                'invoice_prefix' => 'PTDUA',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'PT Tiga Makmur',
                'slug' => 'pt-tiga-makmur',
                'legal_name' => 'PT Tiga Makmur Bersama',
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta',
                'phone' => '0217654321',
                'email' => 'contact@tigamakmur.test',
                'npwp' => '02.345.678.9-012.000',
                'invoice_prefix' => 'PTTIGA',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'PT Empat Perkasa',
                'slug' => 'pt-empat-perkasa',
                'legal_name' => 'PT Empat Perkasa Mandiri',
                'address' => 'Jl. Soekarno Hatta No. 88, Bandung',
                'phone' => '0229988776',
                'email' => 'contact@empatperkasa.test',
                'npwp' => '03.456.789.0-123.000',
                'invoice_prefix' => 'PTEMPAT',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['slug' => $company['slug']],
                $company
            );
        }
    }
}
