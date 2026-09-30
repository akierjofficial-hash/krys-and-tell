<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->decimal('total_cost', 10, 2)->nullable()->change();
            $table->decimal('balance', 10, 2)->nullable()->change();
            $table->boolean('is_unpriced_contract')->default(false);
            $table->date('first_due_date')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamp('total_agreed_at')->nullable();
            $table->foreignId('total_agreed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Refuse an automatic rollback: restoring NOT NULL would force unknown
        // historical amounts to zero, and dropping these columns would lose terms.
        throw new RuntimeException('Open monthly contracts require a reviewed data conversion before this migration can be rolled back.');
    }
};
