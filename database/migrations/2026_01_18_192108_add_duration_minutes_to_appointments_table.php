<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ if column already exists, do nothing (prevents Duplicate column error)
        if (Schema::hasColumn('appointments', 'duration_minutes')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            // if appointment_time exists, keep nice column order
            if (Schema::hasColumn('appointments', 'appointment_time')) {
                $table->unsignedSmallInteger('duration_minutes')->nullable()->after('appointment_time');
            } else {
                $table->unsignedSmallInteger('duration_minutes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('appointments', 'duration_minutes')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};
