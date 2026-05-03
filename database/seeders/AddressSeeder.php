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
                'phone_number' => '895364711840',
                'province_id' => 18,
                'city_id' => 577,
                'district_id' => 5874,
                'subdistrict_id' => 69217,
                'province' => 'JAWA TIMUR',
                'city' => 'SURABAYA',
                'district' => 'BENOWO',
                'subdistrict' => 'SEMEMI',
                'postal_code' => '60198',
                'destination_id' => '69217',
                'address_line' => 'JL. Bandarejo Candi 3 No. 11 RT 11 RW 05, Sememi, Benowo',
                'is_primary' => true,
            ]
        );
    }
}
