<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 30, 800);
        $tax      = round($subtotal * 0.10, 2);
        $shipping = 5.00;
        $total    = $subtotal + $tax + $shipping;

        return [
            'user_id'          => User::factory(),
            'order_number'     => Order::generateOrderNumber(),
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'shipping_fee'     => $shipping,
            'discount'         => 0,
            'total'            => $total,
            'status'           => fake()->randomElement([
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
            ]),
            'payment_status'   => fake()->randomElement([Order::PAYMENT_PAID, Order::PAYMENT_UNPAID]),
            'payment_method'   => fake()->randomElement(['cod', 'stripe', 'paypal']),
            'shipping_address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->country(),
            'phone'            => fake()->phoneNumber(),
            'notes'            => fake()->optional(0.3)->sentence(),
            'shipped_at'       => null,
            'delivered_at'     => null,
        ];
    }

    public function paid(): self
    {
        return $this->state(fn () => ['payment_status' => Order::PAYMENT_PAID]);
    }

    public function pending(): self
    {
        return $this->state(fn () => ['status' => Order::STATUS_PENDING]);
    }
}
