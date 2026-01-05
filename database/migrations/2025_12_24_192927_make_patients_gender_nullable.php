<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patients') || !Schema::hasColumn('patients', 'gender')) {
            return;
        }

        $driver = DB::getDriverName();

        // ✅ PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE patients ALTER COLUMN gender DROP NOT NULL');
            return; // IMPORTANT: stops MySQL-only code from running on Postgres
        }

        // ✅ MySQL / MariaDB
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $type = DB::table('information_schema.columns')
                ->where('table_schema', DB::raw('database()'))
                ->where('table_name', 'patients')
                ->where('column_name', 'gender')
                ->value('column_type');

            $type = $type ?: 'varchar(20)';

            DB::statement("ALTER TABLE `patients` MODIFY `gender` {$type} NULL");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('patients') || !Schema::hasColumn('patients', 'gender')) {
            return;
        }

        $driver = DB::getDriverName();

        // ✅ PostgreSQL
        if ($driver === 'pgsql') {
            // Safety: don't break if there are already NULL rows
            $hasNulls = DB::table('patients')->whereNull('gender')->exists();
            if (!$hasNulls) {
                DB::statement('ALTER TABLE patients ALTER COLUMN gender SET NOT NULL');
            }
            return;
        }

        // ✅ MySQL / MariaDB
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $hasNulls = DB::table('patients')->whereNull('gender')->exists();
            if ($hasNulls) {
                return;
            }

            $type = DB::table('information_schema.columns')
                ->where('table_schema', DB::raw('database()'))
                ->where('table_name', 'patients')
                ->where('column_name', 'gender')
                ->value('column_type');

            $type = $type ?: 'varchar(20)';

            DB::statement("ALTER TABLE `patients` MODIFY `gender` {$type} NOT NULL");
        }
    }
};
