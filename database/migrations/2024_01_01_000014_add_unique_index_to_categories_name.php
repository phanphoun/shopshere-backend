<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate categories before adding unique index
        $duplicates = DB::table('categories')
            ->select('name', DB::raw('MIN(id) as keep_id'))
            ->whereNull('deleted_at')
            ->groupBy('name')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $duplicateIds = DB::table('categories')
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->keep_id)
                ->whereNull('deleted_at')
                ->pluck('id');

            // Reassign products from duplicate categories to the kept one
            DB::table('products')
                ->whereIn('category_id', $duplicateIds)
                ->update(['category_id' => $dup->keep_id]);

            // Soft delete duplicate categories
            DB::table('categories')
                ->whereIn('id', $duplicateIds)
                ->update(['deleted_at' => now()]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_unique');
        });
    }
};
