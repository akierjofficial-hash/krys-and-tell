<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_staff_only')->default(false);
            $table->string('internal_code')->nullable()->unique();
        });

        Schema::table('visit_procedures', function (Blueprint $table) {
            $table->foreignId('related_visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->foreignId('related_installment_plan_id')->nullable()->constrained('installment_plans')->nullOnDelete();
        });

        $existing = DB::table('services')->whereRaw('LOWER(name) = ?', ['recement'])->orderBy('id')->first();
        if ($existing) {
            DB::table('services')->where('id', $existing->id)->update([
                'base_price' => 500,
                'allow_custom_price' => true,
                'is_staff_only' => true,
                'internal_code' => 'recement',
                'deleted_at' => null,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('services')->insert([
                'name' => 'Recement',
                'base_price' => 500,
                'allow_custom_price' => true,
                'description' => 'Braces-related recement procedure. Charged separately from an orthodontic plan.',
                'is_staff_only' => true,
                'internal_code' => 'recement',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Retain historical procedure references while ensuring Recement cannot become public.
        DB::table('services')->where('internal_code', 'recement')->update(['deleted_at' => now()]);
        Schema::table('visit_procedures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_installment_plan_id');
            $table->dropConstrainedForeignId('related_visit_id');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['internal_code']);
            $table->dropColumn(['internal_code', 'is_staff_only']);
        });
    }
};
