<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // End time = start time + duration
            // Existing records will default to 60 minutes.
            if (!Schema::hasColumn('appointments', 'duration_minutes')) {
                $table->unsignedSmallInteger('duration_minutes')
                    ->default(60)
                    ->after('appointment_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};
