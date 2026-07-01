<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get the user's cart with items.
     */
    public function getCart(User $user)
    {
        $cart = $this->cartRepository->getOrCreateForUser($user->id);
        $cart->load('items.product.category', 'items.product.images');

        // Remove stale items whose products were soft-deleted
        $stale = $cart->items->filter(fn ($item) => !$item->product);
        $stale->each(fn ($item) => $item->delete());
        if ($stale->isNotEmpty()) {
            $cart->load('items.product.category', 'items.product.images');
        }

        return [
            'cart' => $cart,
            'summary' => $this->summarize($cart),
        ];
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(User $user, int $productId, int $quantity): array
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw ValidationException::withMessages(['product_id' => 'Product not found.']);
        }
        if (!$product->status) {
            throw ValidationException::withMessages(['product_id' => 'Product is not available.']);
        }
        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$product->stock_quantity} items available in stock.",
            ]);
        }

        $cart = $this->cartRepository->getOrCreateForUser($user->id);
        $this->cartRepository->addItem($cart, $product, $quantity);

        return $this->getCart($user);
    }

    /**
     * Update item quantity.
     */
    public function updateItem(User $user, int $productId, int $quantity): array
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw ValidationException::withMessages(['product_id' => 'Product not found.']);
        }

        $cart = $this->cartRepository->getOrCreateForUser($user->id);

        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$product->stock_quantity} items available in stock.",
            ]);
        }

        $this->cartRepository->updateQuantity($cart, $productId, $quantity);

        return $this->getCart($user);
    }

    /**
     * Remove a cart item by id.
     */
    public function removeItem(User $user, int $itemId): array
    {
        $cart = $this->cartRepository->getOrCreateForUser($user->id);
        $this->cartRepository->removeItem($cart, $itemId);

        return $this->getCart($user);
    }

    /**
     * Clear all cart items.
     */
    public function clear(User $user): void
    {
        $cart = $this->cartRepository->getOrCreateForUser($user->id);
        $this->cartRepository->clear($cart);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    public function summarize($cart): array
    {
        $subtotal = (float) $cart->items->sum(function ($item) {
            return $item->quantity * ($item->product?->final_price ?? 0);
        });

        $tax = round($subtotal * (float) config('shopsphere.tax_rate', 10) / 100, 2);
        $shipping = $subtotal > 0 ? (float) config('shopsphere.shipping_fee', 5.00) : 0;
        $total = round($subtotal + $tax + $shipping, 2);

        return [
            'subtotal'    => round($subtotal, 2),
            'tax'         => $tax,
            'shipping_fee'=> $shipping,
            'discount'    => 0,
            'total'       => $total,
            'items_count' => (int) $cart->items->sum('quantity'),
        ];
    }
}
