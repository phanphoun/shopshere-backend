<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('source');
            $table->unsignedBigInteger('telegram_user_id')->nullable()->after('telegram_chat_id');
            $table->index(['telegram_chat_id']);
        });
    }

    public function down(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropIndex(['telegram_chat_id']);
            $table->dropColumn(['telegram_chat_id', 'telegram_user_id']);
        });
    }
};
