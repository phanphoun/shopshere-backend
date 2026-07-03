<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'aba_payway_txn_id')) {
                $table->string('aba_payway_txn_id')->nullable()->unique()->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('aba_payway_txn_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'paid_at')) {
                $table->dropColumn('paid_at');
            }

            if (Schema::hasColumn('orders', 'aba_payway_txn_id')) {
                $table->dropColumn('aba_payway_txn_id');
            }
        });
    }
};
