<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patients')) return;

        // Works on Postgres and is safe to run even if already nullable
        if (Schema::hasColumn('patients', 'birthdate')) {
            DB::statement('ALTER TABLE patients ALTER COLUMN birthdate DROP NOT NULL');
        }

        if (Schema::hasColumn('patients', 'gender')) {
            DB::statement('ALTER TABLE patients ALTER COLUMN gender DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('patients')) return;

        // NOTE: this can fail if you already have NULLs in production
        if (Schema::hasColumn('patients', 'birthdate')) {
            DB::statement('ALTER TABLE patients ALTER COLUMN birthdate SET NOT NULL');
        }

        if (Schema::hasColumn('patients', 'gender')) {
            DB::statement('ALTER TABLE patients ALTER COLUMN gender SET NOT NULL');
        }
    }
};
