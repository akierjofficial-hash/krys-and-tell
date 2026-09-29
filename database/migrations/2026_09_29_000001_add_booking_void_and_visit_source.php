<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
        });
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('source_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visits', fn (Blueprint $table) => $table->dropConstrainedForeignId('source_appointment_id'));
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['voided_at', 'void_reason']);
        });
    }
};
