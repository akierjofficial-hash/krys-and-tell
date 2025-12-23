<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('service_id');

            $table->date('appointment_date');
            $table->time('appointment_time'); // ✅ must exist
            $table->string('status')->default('upcoming');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('patient_id')
                  ->references('id')->on('patients')
                  ->onDelete('cascade');

            $table->foreign('service_id')
                  ->references('id')->on('services')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
