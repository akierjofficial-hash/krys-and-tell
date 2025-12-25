<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patients')) return;

        // Make birthdate nullable (keep the SAME type)
        if (Schema::hasColumn('patients', 'birthdate')) {
            $type = DB::table('information_schema.columns')
                ->where('table_schema', DB::raw('database()'))
                ->where('table_name', 'patients')
                ->where('column_name', 'birthdate')
                ->value('column_type'); // e.g. "date"

            $type = $type ?: 'date';
            DB::statement("ALTER TABLE `patients` MODIFY `birthdate` {$type} NULL");
        }

        // Make gender nullable (keep SAME type — enum stays enum)
        if (Schema::hasColumn('patients', 'gender')) {
            $type = DB::table('information_schema.columns')
                ->where('table_schema', DB::raw('database()'))
                ->where('table_name', 'patients')
                ->where('column_name', 'gender')
                ->value('column_type'); // e.g. "varchar(20)" or "enum('male','female')"

            $type = $type ?: 'varchar(20)';
            DB::statement("ALTER TABLE `patients` MODIFY `gender` {$type} NULL");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('patients')) return;

        // Down = return NOT NULL (this can fail if you already have NULL data)
        // So we do it safely only if you really want it back.
        // Recommended: leave as-is.

        // Example (NOT recommended):
        // DB::statement("ALTER TABLE `patients` MODIFY `birthdate` date NOT NULL");
        // DB::statement("ALTER TABLE `patients` MODIFY `gender` varchar(20) NOT NULL");
    }
};
