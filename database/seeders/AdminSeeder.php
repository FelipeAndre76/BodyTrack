<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'felipeandre76@hotmail.com'
            ],
            [
                'name' => 'FELIPE ANDRE SOUSA BRITO',
                'password' => Hash::make('12345678'),
                'is_admin' => true,
                'is_active' => true,
            ]
        );
    }


}
