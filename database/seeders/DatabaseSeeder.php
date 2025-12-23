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
        // Only seed dev/test dummy users locally (optional)
        // if (app()->environment('local')) {
        //     \App\Models\User::factory(10)->create();
        // }

        $this->call([
            AdminUserSeeder::class,
            StaffUserSeeder::class,
        ]);
    }
}
