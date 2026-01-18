<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('appointments', 'reminder_24h_sent_at')) {
                $table->timestamp('reminder_24h_sent_at')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'reminder_1h_sent_at')) {
                $table->timestamp('reminder_1h_sent_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            if (Schema::hasColumn('appointments', 'reminder_24h_sent_at')) {
                $table->dropColumn('reminder_24h_sent_at');
            }
            if (Schema::hasColumn('appointments', 'reminder_1h_sent_at')) {
                $table->dropColumn('reminder_1h_sent_at');
            }
        });
    }
};
