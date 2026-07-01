<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;

interface WishlistRepositoryInterface
{
    public function getForUser(int $userId): Collection;

    public function toggle(int $userId, Product $product): bool;

    public function add(int $userId, Product $product): Wishlist;

    public function remove(int $userId, Product $product): bool;

    public function exists(int $userId, int $productId): bool;
}
