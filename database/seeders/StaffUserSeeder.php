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
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $email = trim((string) env('SEED_STAFF_EMAIL', 'staff@krysandtell.com'));
        $plainPassword = (string) env('SEED_STAFF_PASSWORD', '');

        if ($email === '' || $plainPassword === '') {
            $this->command?->warn('StaffUserSeeder skipped: set SEED_STAFF_EMAIL and SEED_STAFF_PASSWORD in .env.');
            return;
        }

        $data = [
            'name' => 'Staff',
            'email' => $email,
            'password' => Hash::make($plainPassword),
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
