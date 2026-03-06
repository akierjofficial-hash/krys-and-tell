<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $this->call([
            AdminUserSeeder::class,
            StaffUserSeeder::class,
        ]);
    }
}
