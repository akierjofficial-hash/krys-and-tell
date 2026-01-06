<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            // link a monthly payment to an actual visit (optional)
            $table->foreignId('visit_id')
                ->nullable()
                ->after('installment_plan_id')
                ->constrained('visits')
                ->nullOnDelete();

            // notes like: "recementation", "upper wire change", etc.
            $table->text('notes')
                ->nullable()
                ->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_id');
            $table->dropColumn('notes');
        });
    }
};
