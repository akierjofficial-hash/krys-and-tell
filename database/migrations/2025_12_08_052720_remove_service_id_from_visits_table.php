<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('visits', function (Blueprint $table) {
        if (Schema::hasColumn('visits', 'service_id')) {

            // drop FK only if it exists
            try {
                $table->dropForeign(['service_id']);
            } catch (\Exception $e) {
                // ignore if foreign key does not exist
            }

            $table->dropColumn('service_id');
        }
    });
}


public function down()
{
    Schema::table('visits', function (Blueprint $table) {
        if (!Schema::hasColumn('visits', 'service_id')) {
            $table->foreignId('service_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
        }
    });
}


};
