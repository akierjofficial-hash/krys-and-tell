<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('submission_token')->nullable()->unique()->after('notes');
        });
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->uuid('submission_token')->nullable()->unique()->after('notes');
        });
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->uuid('submission_token')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('installment_plans', fn (Blueprint $table) => $table->dropUnique(['submission_token']));
        Schema::table('installment_plans', fn (Blueprint $table) => $table->dropColumn('submission_token'));
        Schema::table('installment_payments', fn (Blueprint $table) => $table->dropUnique(['submission_token']));
        Schema::table('installment_payments', fn (Blueprint $table) => $table->dropColumn('submission_token'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropUnique(['submission_token']));
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('submission_token'));
    }
};
