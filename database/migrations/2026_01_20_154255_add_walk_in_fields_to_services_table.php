<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'is_walk_in')) {
                $table->boolean('is_walk_in')->default(false)->after('description');
            }
            if (!Schema::hasColumn('services', 'walk_in_note')) {
                $table->string('walk_in_note', 255)->nullable()->after('is_walk_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'walk_in_note')) $table->dropColumn('walk_in_note');
            if (Schema::hasColumn('services', 'is_walk_in')) $table->dropColumn('is_walk_in');
        });
    }
};
