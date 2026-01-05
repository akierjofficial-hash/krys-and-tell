<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_informed_consents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnDelete()
                ->unique();

            // Store initials per section as JSON (easy to change later)
            // Example: { "treatment": "KT", "radiograph": "KT", ... }
            $table->json('initials')->nullable();

            // Signatures
            $table->string('patient_signature_path')->nullable();
            $table->dateTime('patient_signed_at')->nullable();

            $table->string('dentist_signature_path')->nullable();
            $table->dateTime('dentist_signed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_informed_consents');
    }
};
