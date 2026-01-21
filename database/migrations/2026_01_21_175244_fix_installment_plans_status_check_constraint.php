<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old constraint (name must match)
        DB::statement("ALTER TABLE installment_plans DROP CONSTRAINT IF EXISTS installment_plans_status_check");

        // Normalize existing data
        DB::statement("UPDATE installment_plans SET status='Completed' WHERE lower(status)='completed'");

        // Add new constraint
        DB::statement("
            ALTER TABLE installment_plans
            ADD CONSTRAINT installment_plans_status_check
            CHECK (status IN ('Pending','Partially Paid','Fully Paid','Completed'))
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE installment_plans DROP CONSTRAINT IF EXISTS installment_plans_status_check");
    }
};
