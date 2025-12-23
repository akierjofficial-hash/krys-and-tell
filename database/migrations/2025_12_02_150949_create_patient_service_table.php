<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('patient_service', function (Blueprint $table) {
        $table->id();

        // Foreign keys
        $table->unsignedBigInteger('patient_id');
        $table->unsignedBigInteger('service_id');

        // Optional: date of service, status, notes
        $table->date('date')->nullable();
        $table->string('status')->default('pending');
        $table->text('notes')->nullable();

        // Set up foreign key constraints
        $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::dropIfExists('patient_service');
}

};
