<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_entry_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->string('mode', 10)->default('past');
            $table->string('status', 12)->default('draft');
            $table->unsignedInteger('version')->default(0);
            $table->json('payload');
            $table->json('warnings')->nullable();
            $table->json('summary')->nullable();
            $table->string('review_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'patient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_entry_batches');
    }
};
