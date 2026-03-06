<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doctor_unavailabilities')) {
            return;
        }

        Schema::create('doctor_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->date('unavailable_date');
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'unavailable_date'], 'doctor_unavailable_unique_day');
            $table->index('unavailable_date');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('doctor_unavailabilities')) {
            return;
        }

        Schema::dropIfExists('doctor_unavailabilities');
    }
};
