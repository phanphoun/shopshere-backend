<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support modifying columns directly, so we need a workaround
        if (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            // 1. Create temp table
            Schema::create('users_temp', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->string('phone', 32)->nullable();
                $table->text('address')->nullable();
                $table->string('avatar')->nullable();
                $table->string('google_id')->nullable()->unique();
                $table->string('telegram_chat_id')->nullable();
                $table->enum('role', ['admin', 'customer'])->default('customer')->index();
                $table->enum('status', ['active', 'inactive', 'banned'])->default('active')->index();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['role', 'status']);
            });

            // 2. Copy data
            DB::statement('INSERT INTO users_temp (id, name, email, email_verified_at, password, phone, address, avatar, google_id, telegram_chat_id, role, status, remember_token, created_at, updated_at, deleted_at) SELECT id, name, email, email_verified_at, password, phone, address, avatar, google_id, telegram_chat_id, role, status, remember_token, created_at, updated_at, deleted_at FROM users');

            // 3. Drop old table
            Schema::drop('users');

            // 4. Rename temp table
            Schema::rename('users_temp', 'users');

            Schema::enableForeignKeyConstraints();
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('users_temp', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('phone', 32)->nullable();
                $table->text('address')->nullable();
                $table->string('avatar')->nullable();
                $table->string('google_id')->nullable()->unique();
                $table->string('telegram_chat_id')->nullable();
                $table->enum('role', ['admin', 'customer'])->default('customer')->index();
                $table->enum('status', ['active', 'inactive', 'banned'])->default('active')->index();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['role', 'status']);
            });

            DB::statement('INSERT INTO users_temp (id, name, email, email_verified_at, password, phone, address, avatar, google_id, telegram_chat_id, role, status, remember_token, created_at, updated_at, deleted_at) SELECT id, name, email, email_verified_at, password, phone, address, avatar, google_id, telegram_chat_id, role, status, remember_token, created_at, updated_at, deleted_at FROM users');

            Schema::drop('users');
            Schema::rename('users_temp', 'users');

            Schema::enableForeignKeyConstraints();
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable(false)->change();
            });
        }
    }
};
