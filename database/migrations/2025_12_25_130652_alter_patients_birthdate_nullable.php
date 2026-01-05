<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patients')) return;

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            if (Schema::hasColumn('patients', 'birthdate')) {
                DB::statement('ALTER TABLE patients ALTER COLUMN birthdate DROP NOT NULL');
            }
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if (Schema::hasColumn('patients', 'birthdate')) {
                DB::statement('ALTER TABLE `patients` MODIFY `birthdate` DATE NULL');
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('patients')) return;

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            if (Schema::hasColumn('patients', 'birthdate')) {
                DB::statement('ALTER TABLE patients ALTER COLUMN birthdate SET NOT NULL');
            }
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if (Schema::hasColumn('patients', 'birthdate')) {
                DB::statement('ALTER TABLE `patients` MODIFY `birthdate` DATE NOT NULL');
            }
        }
    }
};
