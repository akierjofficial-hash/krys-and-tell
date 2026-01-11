<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->boolean('is_open_contract')->default(false)->after('months');
        });
    }

    public function down(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->dropColumn('is_open_contract');
        });
    }
};
