<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();

            // Link to the staff/admin user that enabled notifications.
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('role', 20)->nullable()->index();

            // Endpoint can be long; keep index safe on utf8mb4.
            $table->string('endpoint', 512)->unique();

            // Browser public key + auth secret (base64).
            $table->text('public_key');
            $table->text('auth_token');

            $table->string('content_encoding', 20)->default('aesgcm');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
