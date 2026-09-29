<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')
            || !Schema::hasColumn('appointments', 'user_id')
            || !Schema::hasColumn('appointments', 'patient_id')) {
            return;
        }

        $query = DB::table('appointments')
            ->whereNotNull('user_id')
            ->whereNotNull('patient_id');

        // Public bookings always record the submitted patient name. Historical
        // staff-created appointments only received user_id from an email match.
        foreach (['public_name', 'public_first_name', 'public_last_name'] as $column) {
            if (Schema::hasColumn('appointments', $column)) {
                $query->where(function ($value) use ($column) {
                    $value->whereNull($column)->orWhere($column, '');
                });
            }
        }

        $query->update(['user_id' => null]);
    }

    public function down(): void
    {
        // Email-derived ownership cannot be restored safely. The appointment
        // record itself remains intact and verified patient links provide access.
    }
};
