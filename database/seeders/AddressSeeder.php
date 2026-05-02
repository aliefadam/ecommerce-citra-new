<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Address::updateOrCreate(
            [
                'user_id' => 2,
                'label' => 'Rumah',
            ],
            [
                'recipient_name' => 'Alief Adam',
                'phone_country_code' => '+62',
                'phone_number' => '81234567890',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12920',
                'address_line' => 'Jl. Sudirman No. 123, Kel. Karet Semanggi, Kec. Setiabudi',
                'is_primary' => true,
            ]
        );

        Address::updateOrCreate(
            [
                'user_id' => 2,
                'label' => 'Kantor',
            ],
            [
                'recipient_name' => 'Alief Adam',
                'phone_country_code' => '+62',
                'phone_number' => '81234567890',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12950',
                'address_line' => 'Gedung Menara BRI Lt. 5, Jl. Gatot Subroto, Kec. Setiabudi',
                'is_primary' => false,
            ]
        );
    }
}
