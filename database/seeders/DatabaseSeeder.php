<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Never seed default privileged accounts outside local/testing.
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $this->call([
            AdminUserSeeder::class,
            StaffUserSeeder::class,
        ]);
    }
}
