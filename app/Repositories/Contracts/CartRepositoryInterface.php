<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

interface CartRepositoryInterface
{
    public function getOrCreateForUser(int $userId): Cart;

    public function addItem(Cart $cart, Product $product, int $quantity): CartItem;

    public function updateQuantity(Cart $cart, int $productId, int $quantity): ?CartItem;

    public function removeItem(Cart $cart, int $itemId): bool;

    public function clear(Cart $cart): void;
}
