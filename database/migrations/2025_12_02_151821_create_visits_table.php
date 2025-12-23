<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patient_id');
            $table->date('visit_date')->default(now());
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('price', 10, 2)->nullable();

            $table->timestamps();

            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('visits');
    }
};
