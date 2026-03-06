<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contact_messages')) return;

        Schema::table('contact_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_messages', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->index()->after('read_at');
            }

            if (!Schema::hasColumn('contact_messages', 'reply_subject')) {
                $table->string('reply_subject', 190)->nullable()->after('replied_at');
            }

            if (!Schema::hasColumn('contact_messages', 'reply_message')) {
                $table->text('reply_message')->nullable()->after('reply_subject');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('contact_messages')) return;

        Schema::table('contact_messages', function (Blueprint $table) {
            if (Schema::hasColumn('contact_messages', 'reply_message')) {
                $table->dropColumn('reply_message');
            }
            if (Schema::hasColumn('contact_messages', 'reply_subject')) {
                $table->dropColumn('reply_subject');
            }
            if (Schema::hasColumn('contact_messages', 'replied_at')) {
                $table->dropColumn('replied_at');
            }
        });
    }
};
