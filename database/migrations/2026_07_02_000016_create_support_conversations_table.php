<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamp('closed_at')->nullable();
            $table->string('source')->default('web'); // web, telegram
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_conversations');
    }
};
