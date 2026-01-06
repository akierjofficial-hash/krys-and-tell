<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Render / Postgres
            DB::statement("ALTER TABLE patients ALTER COLUMN contact_number TYPE VARCHAR(20)");
        } elseif ($driver === 'mysql') {
            // XAMPP / MySQL
            DB::statement("ALTER TABLE patients MODIFY contact_number VARCHAR(20) NULL");
        } else {
            // fallback
            DB::statement("ALTER TABLE patients ALTER COLUMN contact_number TYPE VARCHAR(20)");
        }
    }

    public function down(): void
    {
        // optional rollback (you can leave this empty if you don't plan to revert)
    }
};
