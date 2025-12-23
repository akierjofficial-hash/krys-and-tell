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
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Ensure foreign keys work
            $table->id();
            $table->string('patient_name');
            $table->string('contact');
            $table->string('address');
            $table->string('treatment');
            $table->decimal('total_cost', 10, 2);
            $table->decimal('downpayment', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->integer('months');
            $table->date('start_date');
            $table->enum('status', ['Partially Paid', 'Fully Paid'])->default('Partially Paid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};
