<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
    ['email' => 'admin@clinic.com'],
    [
        'name' => 'Admin',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'is_active' => true,
    ]
);


        User::create([
            'name' => 'Staff',
            'email' => 'staff@clinic.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'is_active' => true,
        ]);
    }
}
