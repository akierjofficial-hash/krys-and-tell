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
        $table->unsignedBigInteger('patient_id')->nullable()->after('id');
        $table->unsignedBigInteger('service_id')->nullable()->after('patient_id');

        $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
        $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            //
        });
    }
};
