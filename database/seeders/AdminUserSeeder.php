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
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $email = trim((string) env('SEED_ADMIN_EMAIL', 'admin@krysandtell.com'));
        $plainPassword = (string) env('SEED_ADMIN_PASSWORD', '');

        if ($email === '' || $plainPassword === '') {
            $this->command?->warn('AdminUserSeeder skipped: set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD in .env.');
            return;
        }

        $data = [
            'name' => 'Admin',
            'password' => Hash::make($plainPassword),
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
            ['email' => $email],
            $data
        );
    }
}
