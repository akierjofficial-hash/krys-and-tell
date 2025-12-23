<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Link payment to a visit
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');

            // Payment details
            $table->decimal('amount', 15, 2);
            $table->string('method', 50)->default('cash'); // e.g., cash, card
            $table->text('notes')->nullable();
            $table->date('payment_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
