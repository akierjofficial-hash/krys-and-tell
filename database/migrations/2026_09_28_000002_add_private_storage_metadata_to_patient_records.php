<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_files', function (Blueprint $table) {
            $table->string('storage_disk', 40)->default('public')->after('file_path');
            $table->string('original_name')->nullable()->after('title');
            $table->boolean('patient_visible')->default(false)->after('size');
        });

        Schema::table('patient_information_records', function (Blueprint $table) {
            $table->string('signature_disk', 40)->default('public')->after('signature_path');
        });

        Schema::table('patient_informed_consents', function (Blueprint $table) {
            $table->string('patient_signature_disk', 40)->default('public')->after('patient_signature_path');
            $table->string('dentist_signature_disk', 40)->default('public')->after('dentist_signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('patient_files', function (Blueprint $table) {
            $table->dropColumn(['storage_disk', 'original_name', 'patient_visible']);
        });
        Schema::table('patient_information_records', function (Blueprint $table) {
            $table->dropColumn('signature_disk');
        });
        Schema::table('patient_informed_consents', function (Blueprint $table) {
            $table->dropColumn(['patient_signature_disk', 'dentist_signature_disk']);
        });
    }
};
