<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('installment_plans')) return;

        // 1) is_open_contract
        if (!Schema::hasColumn('installment_plans', 'is_open_contract')) {
            Schema::table('installment_plans', function (Blueprint $table) {
                $table->boolean('is_open_contract')->default(false)->after('months');
            });
        }

        // 2) open_monthly_payment (for Open Contract)
        if (!Schema::hasColumn('installment_plans', 'open_monthly_payment')) {
            Schema::table('installment_plans', function (Blueprint $table) {
                $table->decimal('open_monthly_payment', 10, 2)->nullable()->after('is_open_contract');
            });
        }

        // 3) Ensure status supports: Pending / Partially Paid / Fully Paid / Completed
        if (!Schema::hasColumn('installment_plans', 'status')) {
            Schema::table('installment_plans', function (Blueprint $table) {
                $table->string('status', 40)->default('Pending')->after('start_date');
            });
        } else {
            // Convert enum -> varchar safely (no doctrine/dbal needed)
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE installment_plans MODIFY status VARCHAR(40) NOT NULL DEFAULT 'Pending'");
            } elseif ($driver === 'pgsql') {
                DB::statement("ALTER TABLE installment_plans ALTER COLUMN status TYPE VARCHAR(40)");
                DB::statement("ALTER TABLE installment_plans ALTER COLUMN status SET DEFAULT 'Pending'");
            }
        }

        DB::table('installment_plans')->whereNull('status')->update(['status' => 'Pending']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('installment_plans')) return;

        if (Schema::hasColumn('installment_plans', 'open_monthly_payment')) {
            Schema::table('installment_plans', function (Blueprint $table) {
                $table->dropColumn('open_monthly_payment');
            });
        }

        if (Schema::hasColumn('installment_plans', 'is_open_contract')) {
            Schema::table('installment_plans', function (Blueprint $table) {
                $table->dropColumn('is_open_contract');
            });
        }
    }
};
