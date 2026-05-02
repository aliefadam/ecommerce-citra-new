<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@citra.com'],
            [
                'name' => 'Administrator',
                'first_name' => 'Administrator',
                'last_name' => '',
                'username' => 'admin',
                'role' => 'admin',
                'gender' => 'male',
                'phone_country_code' => '+62',
                'phone_number' => '81200000000',
                'birth_date' => '1990-01-01',
                'social_url' => 'https://instagram.com/admin',
                'bio' => 'Admin Ecommerce Citra',
                'password' => Hash::make('123123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'aliefadam6@gmail.com'],
            [
                'name' => 'Alief Adam',
                'first_name' => 'Alief',
                'last_name' => 'Adam',
                'username' => 'alief.adam',
                'role' => 'user',
                'gender' => 'male',
                'phone_country_code' => '+62',
                'phone_number' => '81234567890',
                'birth_date' => '1995-07-15',
                'social_url' => 'https://instagram.com/aliefadam',
                'bio' => 'Suka belanja online, terutama fashion dan elektronik',
                'password' => Hash::make('123123'),
            ]
        );
    }
}
