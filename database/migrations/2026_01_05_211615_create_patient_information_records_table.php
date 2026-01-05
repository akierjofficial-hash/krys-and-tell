<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_information_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnDelete()
                ->unique();

            // Top section
            $table->string('nickname')->nullable();
            $table->string('occupation')->nullable();
            $table->string('dental_insurance')->nullable();
            $table->date('effective_date')->nullable();

            $table->string('home_no')->nullable();
            $table->string('office_no')->nullable();
            $table->string('fax_no')->nullable();

            // Minor
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name')->nullable();
            $table->string('guardian_occupation')->nullable();

            // Referral / reason
            $table->string('referral_source')->nullable();
            $table->text('consultation_reason')->nullable();

            // Dental history
            $table->string('previous_dentist')->nullable();
            $table->date('last_dental_visit')->nullable();

            // Medical history basics
            $table->string('physician_name')->nullable();
            $table->string('physician_specialty')->nullable();

            $table->boolean('good_health')->nullable();
            $table->boolean('under_treatment')->nullable();
            $table->string('treatment_condition')->nullable();

            $table->boolean('serious_illness')->nullable();
            $table->text('serious_illness_details')->nullable();

            $table->boolean('hospitalized')->nullable();
            $table->text('hospitalized_reason')->nullable();

            // Medications / allergies
            $table->boolean('taking_medication')->nullable();
            $table->text('medications')->nullable();
            $table->boolean('takes_aspirin')->nullable();

            // Store selected allergies + optional other
            $table->json('allergies')->nullable();
            $table->string('allergies_other')->nullable();

            // Habits
            $table->boolean('tobacco_use')->nullable();
            $table->boolean('alcohol_use')->nullable();
            $table->boolean('dangerous_drugs')->nullable();

            $table->string('bleeding_time')->nullable();

            // Female-only
            $table->boolean('pregnant')->nullable();
            $table->boolean('nursing')->nullable();
            $table->boolean('birth_control_pills')->nullable();

            // Vitals
            $table->string('blood_type')->nullable();
            $table->string('blood_pressure')->nullable();

            // Medical conditions checklist (store as array)
            $table->json('medical_conditions')->nullable();
            $table->string('medical_conditions_other')->nullable();

            // Signature
            $table->string('signature_path')->nullable();
            $table->dateTime('signed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_information_records');
    }
};
