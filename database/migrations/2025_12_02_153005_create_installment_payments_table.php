<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('installment_plan_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->unsignedInteger('month_number'); // 1 = downpayment
            $table->decimal('amount', 10, 2);
            $table->string('method')->nullable();
            $table->date('payment_date');

            $table->timestamps();

            // prevent paying the same month twice
            $table->unique(['installment_plan_id', 'month_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('installment_payments');
    }
};
