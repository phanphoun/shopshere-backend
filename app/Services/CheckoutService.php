<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CartService $cartService
    ) {}

    /**
     * Convert the user's cart into an order.
     */
    public function placeOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cart = $this->cartRepository->getOrCreateForUser($user->id);
            $cart->load('items.product');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            // Validate stock
            foreach ($cart->items as $item) {
                if ($item->product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Product \"{$item->product->name}\" has only {$item->product->stock_quantity} in stock.",
                    ]);
                }
            }

            $summary = $this->cartService->summarize($cart);

            $order = $this->orderRepository->create([
                'user_id'          => $user->id,
                'subtotal'         => $summary['subtotal'],
                'tax'              => $summary['tax'],
                'shipping_fee'     => $summary['shipping_fee'],
                'discount'         => 0,
                'total'            => $summary['total'],
                'status'           => Order::STATUS_PENDING,
                'payment_status'   => Order::PAYMENT_UNPAID,
                'payment_method'   => $data['payment_method'] ?? 'cod',
                'shipping_address' => $data['shipping_address'],
                'phone'            => $data['phone'],
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $price   = $product->final_price;

                $this->orderItemRepository->create([
                    'order_id'      => $order->id,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_sku'   => $product->sku,
                    'product_image' => $product->image,
                    'quantity'      => $item->quantity,
                    'price'         => $price,
                    'subtotal'      => round($price * $item->quantity, 2),
                ]);

                // Decrement stock
                $this->productRepository->decrementStock($product, $item->quantity);
            }

            // Clear cart
            $this->cartRepository->clear($cart);

            return $order->fresh(['items', 'user']);
        });
    }
}
