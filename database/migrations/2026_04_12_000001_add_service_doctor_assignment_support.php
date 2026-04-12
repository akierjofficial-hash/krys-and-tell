<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('services') && !Schema::hasColumn('services', 'restrict_to_assigned_doctors')) {
            Schema::table('services', function (Blueprint $table) {
                $table->boolean('restrict_to_assigned_doctors')
                    ->default(false)
                    ->after('duration_minutes');
            });
        }

        if (!Schema::hasTable('doctor_service')) {
            Schema::create('doctor_service', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['doctor_id', 'service_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doctor_service')) {
            Schema::dropIfExists('doctor_service');
        }

        if (Schema::hasTable('services') && Schema::hasColumn('services', 'restrict_to_assigned_doctors')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('restrict_to_assigned_doctors');
            });
        }
    }
};
