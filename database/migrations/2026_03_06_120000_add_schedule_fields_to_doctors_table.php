<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('doctors')) return;

        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('doctors', 'working_days')) {
                $table->json('working_days')->nullable()->after('sort_order');
            }

            if (!Schema::hasColumn('doctors', 'work_start_time')) {
                $table->time('work_start_time')->nullable()->after('working_days');
            }

            if (!Schema::hasColumn('doctors', 'work_end_time')) {
                $table->time('work_end_time')->nullable()->after('work_start_time');
            }
        });

        // Backfill existing doctors with sensible defaults.
        DB::table('doctors')
            ->whereNull('working_days')
            ->update(['working_days' => json_encode([1, 2, 3, 4, 5, 6])]);

        DB::table('doctors')
            ->whereNull('work_start_time')
            ->update(['work_start_time' => '09:00:00']);

        DB::table('doctors')
            ->whereNull('work_end_time')
            ->update(['work_end_time' => '17:00:00']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('doctors')) return;

        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'work_end_time')) {
                $table->dropColumn('work_end_time');
            }
            if (Schema::hasColumn('doctors', 'work_start_time')) {
                $table->dropColumn('work_start_time');
            }
            if (Schema::hasColumn('doctors', 'working_days')) {
                $table->dropColumn('working_days');
            }
        });
    }
};

