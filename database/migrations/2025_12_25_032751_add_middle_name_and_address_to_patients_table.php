<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::table('patients', function (Blueprint $table) {
        if (!Schema::hasColumn('patients', 'middle_name')) {
            $table->string('middle_name', 120)->nullable()->after('first_name');
        }
        if (!Schema::hasColumn('patients', 'address')) {
            $table->string('address', 255)->nullable()->after('last_name');
        }
    });
}


    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'middle_name')) $table->dropColumn('middle_name');
            if (Schema::hasColumn('patients', 'address')) $table->dropColumn('address');
        });
    }
};
