<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function getOrCreateForUser(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(Cart $cart, Product $product, int $quantity): CartItem
    {
        return DB::transaction(function () use ($cart, $product, $quantity) {
            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $item->quantity += $quantity;
                $item->save();
                return $item;
            }

            return CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $quantity,
            ]);
        });
    }

    public function updateQuantity(Cart $cart, int $productId, int $quantity): ?CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if (!$item) return null;

        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $item->quantity = $quantity;
        $item->save();
        return $item;
    }

    public function removeItem(Cart $cart, int $itemId): bool
    {
        $item = CartItem::where('cart_id', $cart->id)->where('id', $itemId)->first();
        return $item ? (bool) $item->delete() : false;
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }
}
