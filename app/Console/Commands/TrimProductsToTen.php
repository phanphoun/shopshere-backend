<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TrimProductsToTen extends Command
{
    protected $signature = 'products:trim-to-ten {--force : Skip confirmation}';
    protected $description = 'Keep the 10 most recent products and remove older ones';

    public function handle(): int
    {
        $total = Product::count();

        if ($total <= 10) {
            $this->info("Nothing to trim. Current product count: {$total}");

            return self::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("This will permanently remove {$total} products and related records. Continue?", false)) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $keep = Product::orderByDesc('id')->limit(10)->pluck('id');
        $remove = Product::whereNotIn('id', $keep)->get();

        DB::transaction(function () use ($remove) {
            foreach ($remove as $product) {
                $product->load('images');
                $product->images->each(function ($image) {
                    $image->delete();
                });
                $product->delete();
            }
        });

        $this->info("Products trimmed. Remaining count: " . Product::count());

        return self::SUCCESS;
    }
}
