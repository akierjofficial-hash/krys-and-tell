<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) return;

        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'is_walk_in_request')) {
                $table->boolean('is_walk_in_request')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('appointments')) return;

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'is_walk_in_request')) {
                $table->dropColumn('is_walk_in_request');
            }
        });
    }
};
