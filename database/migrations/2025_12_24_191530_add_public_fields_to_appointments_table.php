<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'public_name')) {
                $table->string('public_name')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'public_email')) {
                $table->string('public_email')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'public_phone')) {
                $table->string('public_phone')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'public_message')) {
                $table->text('public_message')->nullable();
            }

            // Optional: doctor_id for better scheduling (if your system uses doctors)
            if (!Schema::hasColumn('appointments', 'doctor_id')) {
                $table->unsignedBigInteger('doctor_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['public_name','public_email','public_phone','public_message','doctor_id'] as $col) {
                if (Schema::hasColumn('appointments', $col)) $table->dropColumn($col);
            }
        });
    }
};
