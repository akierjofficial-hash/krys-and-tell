<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // what happened
            $table->string('event', 120)->nullable();        // e.g. route name
            $table->string('description', 255)->nullable();  // short human text

            // request meta
            $table->string('route_name', 150)->nullable();
            $table->text('url');
            $table->string('method', 10);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            // optional extra data (safe subset)
            $table->json('properties')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['route_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
