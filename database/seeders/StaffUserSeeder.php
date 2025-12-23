<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'name' => 'Staff',
            'email' => 'staff@krysandtell.com',
            'password' => Hash::make('Staff123'),
        ];

        // Set role fields if your users table has them (safe + flexible)
        if (Schema::hasColumn('users', 'role')) {
            $data['role'] = 'staff';
        } elseif (Schema::hasColumn('users', 'user_type')) {
            $data['user_type'] = 'staff';
        } elseif (Schema::hasColumn('users', 'is_admin')) {
            $data['is_admin'] = 0;
        }

        User::updateOrCreate(['email' => $data['email']], $data);
    }
}
