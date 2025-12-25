<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'public_birthdate')) {
                $table->date('public_birthdate')->nullable()->after('public_last_name');
            }
            if (!Schema::hasColumn('appointments', 'public_gender')) {
                $table->string('public_gender', 32)->nullable()->after('public_birthdate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'public_gender')) {
                $table->dropColumn('public_gender');
            }
            if (Schema::hasColumn('appointments', 'public_birthdate')) {
                $table->dropColumn('public_birthdate');
            }
        });
    }
};
