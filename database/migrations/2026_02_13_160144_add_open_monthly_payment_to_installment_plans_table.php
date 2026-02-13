<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('installment_plans', function (Blueprint $table) {
        if (!Schema::hasColumn('installment_plans', 'open_monthly_payment')) {
            $table->decimal('open_monthly_payment', 10, 2)->nullable()->after('months');
        }
    });
}

public function down(): void
{
    Schema::table('installment_plans', function (Blueprint $table) {
        if (Schema::hasColumn('installment_plans', 'open_monthly_payment')) {
            $table->dropColumn('open_monthly_payment');
        }
    });
}

};
