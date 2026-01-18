<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('password_set')->default(false)->after('password');
        });

        // Mark existing “local accounts” as password_set = true
        DB::table('users')
            ->whereNull('google_id')
            ->whereNotNull('password')
            ->update(['password_set' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_set');
        });
    }
};

