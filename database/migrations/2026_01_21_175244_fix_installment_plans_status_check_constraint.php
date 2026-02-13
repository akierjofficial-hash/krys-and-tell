<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('installment_plans') || !Schema::hasColumn('installment_plans', 'status')) {
            return;
        }

        // Normalize weird casing if any
        DB::table('installment_plans')
            ->whereRaw("LOWER(status) = 'completed'")
            ->update(['status' => 'Completed']);

        // ✅ Only PostgreSQL needs this CHECK constraint logic.
        // MySQL/MariaDB either doesn't enforce checks reliably or uses different syntax.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE installment_plans DROP CONSTRAINT IF EXISTS installment_plans_status_check");
        DB::statement(
            "ALTER TABLE installment_plans
             ADD CONSTRAINT installment_plans_status_check
             CHECK (status IN ('Pending','Partially Paid','Fully Paid','Completed'))"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql' || !Schema::hasTable('installment_plans')) {
            return;
        }

        DB::statement("ALTER TABLE installment_plans DROP CONSTRAINT IF EXISTS installment_plans_status_check");
        DB::statement(
            "ALTER TABLE installment_plans
             ADD CONSTRAINT installment_plans_status_check
             CHECK (status IN ('Partially Paid','Fully Paid'))"
        );
    }
};
