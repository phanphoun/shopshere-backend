<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\Wishlist;
use App\Repositories\Contracts\WishlistRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function getForUser(int $userId): Collection
    {
        return Wishlist::with(['product.category', 'product.images'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function toggle(int $userId, Product $product): bool
    {
        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return false; // removed
        }

        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $product->id,
        ]);

        return true; // added
    }

    public function add(int $userId, Product $product): Wishlist
    {
        return Wishlist::firstOrCreate([
            'user_id'    => $userId,
            'product_id' => $product->id,
        ]);
    }

    public function remove(int $userId, Product $product): bool
    {
        return (bool) Wishlist::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->delete();
    }

    public function exists(int $userId, int $productId): bool
    {
        return Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }
}
