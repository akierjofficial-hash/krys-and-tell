<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_procedures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

            // Keep as string to support values like "11" or future formats
            $table->string('tooth_number', 10)->nullable(); // ex: 11, 21, 46
            $table->string('surface', 10)->nullable();      // ex: O, OB, ML, etc.
            $table->string('shade', 10)->nullable();        // ex: A1, A2, A3
            $table->decimal('price', 10, 2)->nullable();    // optional override per line
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['visit_id', 'service_id']);
            $table->index('tooth_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_procedures');
    }
};
