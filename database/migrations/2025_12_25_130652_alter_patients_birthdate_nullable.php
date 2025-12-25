<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: make birthdate nullable (no doctrine/dbal required)
        DB::statement("ALTER TABLE `patients` MODIFY `birthdate` DATE NULL");
    }

    public function down(): void
    {
        // revert (choose a safe default if you want, or set NOT NULL back)
        DB::statement("ALTER TABLE `patients` MODIFY `birthdate` DATE NOT NULL");
    }
};
