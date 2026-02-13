<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users',
            'patients',
            'visits',
            'payments',
            'appointments',
            'services',
            'installment_plans',
            'installment_payments',
            'contact_messages',
            'patient_files',
            'patient_information_records',
            'patient_informed_consents',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'deleted_at')) {
                    $t->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'patients',
            'visits',
            'payments',
            'appointments',
            'services',
            'installment_plans',
            'installment_payments',
            'contact_messages',
            'patient_files',
            'patient_information_records',
            'patient_informed_consents',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'deleted_at')) {
                    $t->dropSoftDeletes();
                }
            });
        }
    }
};
