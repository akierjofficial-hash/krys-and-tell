<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_user_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('relationship', 30)->default('self');
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at');
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unlinked_at')->nullable();
            $table->foreignId('unlinked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['patient_id', 'user_id']);
            $table->index(['user_id', 'unlinked_at']);
            $table->index(['patient_id', 'unlinked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_user_links');
    }
};
