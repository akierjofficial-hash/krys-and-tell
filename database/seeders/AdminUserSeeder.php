<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'name' => 'Admin',
            'password' => Hash::make('Admin12345!'),
        ];

        // If your users table has a role field, set it automatically
        if (Schema::hasColumn('users', 'role')) {
            $data['role'] = 'admin';
        } elseif (Schema::hasColumn('users', 'user_type')) {
            $data['user_type'] = 'admin';
        } elseif (Schema::hasColumn('users', 'is_admin')) {
            $data['is_admin'] = 1;
        }

        User::updateOrCreate(
            ['email' => 'admin@krysandtell.com'],
            $data
        );
    }
}
