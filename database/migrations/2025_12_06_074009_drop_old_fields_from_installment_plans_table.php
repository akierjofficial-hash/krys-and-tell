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
    Schema::table('installment_plans', function (Blueprint $table) {
        $table->dropColumn(['patient_name', 'contact', 'address', 'treatment']);
    });
}

public function down()
{
    Schema::table('installment_plans', function (Blueprint $table) {
        $table->string('patient_name');
        $table->string('contact');
        $table->string('address');
        $table->string('treatment');
    });
}

};
