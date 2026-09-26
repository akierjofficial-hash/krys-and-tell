<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('target_type', 120)->nullable()->after('description');
            $table->string('target_id', 80)->nullable()->after('target_type');
            $table->json('before_values')->nullable()->after('properties');
            $table->json('after_values')->nullable()->after('before_values');
            $table->text('reason')->nullable()->after('after_values');
            $table->boolean('is_sensitive')->default(false)->after('reason');
            $table->boolean('succeeded')->nullable()->after('is_sensitive');
            $table->index(['target_type', 'target_id']);
            $table->index(['is_sensitive', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['target_type', 'target_id']);
            $table->dropIndex(['is_sensitive', 'created_at']);
            $table->dropColumn(['target_type', 'target_id', 'before_values', 'after_values', 'reason', 'is_sensitive', 'succeeded']);
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
