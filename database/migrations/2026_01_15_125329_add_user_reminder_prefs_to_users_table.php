<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'notify_24h')) {
                $table->boolean('notify_24h')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'notify_1h')) {
                $table->boolean('notify_1h')->default(true)->after('notify_24h');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notify_24h')) $table->dropColumn('notify_24h');
            if (Schema::hasColumn('users', 'notify_1h')) $table->dropColumn('notify_1h');
        });
    }
};
