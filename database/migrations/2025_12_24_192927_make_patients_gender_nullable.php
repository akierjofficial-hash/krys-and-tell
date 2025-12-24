<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // MySQL (XAMPP)
        if ($driver === 'mysql') {
            $col = DB::selectOne("SHOW COLUMNS FROM `patients` WHERE Field = 'gender'");

            if ($col && isset($col->Type)) {
                // Preserve the existing type (varchar/enum/etc), just make it NULLABLE
                $type = $col->Type;
                DB::statement("ALTER TABLE `patients` MODIFY `gender` {$type} NULL");
            }
        }

        // PostgreSQL (Render)
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE patients ALTER COLUMN gender DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Optional: we won't force it back to NOT NULL to avoid breaking existing rows.
    }
};
