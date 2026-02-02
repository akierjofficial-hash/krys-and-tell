<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) return;

        if (!Schema::hasColumn('appointments', 'staff_note')) {
            Schema::table('appointments', function (Blueprint $table) {
                // put after notes if it exists, otherwise add it normally
                if (Schema::hasColumn('appointments', 'notes')) {
                    $table->text('staff_note')->nullable()->after('notes');
                } else {
                    $table->text('staff_note')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('appointments')) return;

        if (Schema::hasColumn('appointments', 'staff_note')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('staff_note');
            });
        }
    }
};
