<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'public_first_name')) {
                $table->string('public_first_name', 120)->nullable()->after('public_name');
            }
            if (!Schema::hasColumn('appointments', 'public_middle_name')) {
                $table->string('public_middle_name', 120)->nullable()->after('public_first_name');
            }
            if (!Schema::hasColumn('appointments', 'public_last_name')) {
                $table->string('public_last_name', 120)->nullable()->after('public_middle_name');
            }
            if (!Schema::hasColumn('appointments', 'public_address')) {
                $table->string('public_address', 255)->nullable()->after('public_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['public_first_name','public_middle_name','public_last_name','public_address'] as $col) {
                if (Schema::hasColumn('appointments', $col)) $table->dropColumn($col);
            }
        });
    }
};
