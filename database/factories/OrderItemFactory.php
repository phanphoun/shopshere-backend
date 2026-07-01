<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $qty     = fake()->numberBetween(1, 5);
        $price   = $product->discount_price ?? $product->price;

        return [
            'order_id'      => Order::factory(),
            'product_id'    => $product->id,
            'product_name'  => $product->name,
            'product_sku'   => $product->sku,
            'product_image' => $product->image,
            'quantity'      => $qty,
            'price'         => $price,
            'subtotal'      => round($qty * $price, 2),
        ];
    }
}
