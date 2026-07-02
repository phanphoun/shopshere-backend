<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }

            if (!Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->string('telegram_chat_id')->nullable()->after('google_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_id')) {
                $uniqueIndexes = \DB::select("SHOW INDEX FROM users WHERE Key_name = 'UNIQUE' AND Column_name = 'google_id'");
                if (!empty($uniqueIndexes)) {
                    $table->dropUnique('users_google_id_unique');
                }
            }

            if (Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->dropColumn(['telegram_chat_id']);
            }

            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropColumn(['google_id']);
            }
        });
    }
};
