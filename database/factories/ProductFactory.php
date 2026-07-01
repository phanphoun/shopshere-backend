<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 5, 500);
        $hasDiscount = fake()->boolean(40);

        return [
            'category_id'    => Category::factory(),
            'name'           => ucwords($name),
            'slug'           => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'sku'            => 'SKU-' . strtoupper(Str::random(10)),
            'description'    => fake()->paragraphs(3, true),
            'price'          => $price,
            'discount_price' => $hasDiscount ? round($price * fake()->randomFloat(2, 0.5, 0.9), 2) : null,
            'stock_quantity' => fake()->numberBetween(0, 100),
            'featured'       => fake()->boolean(20),
            'status'         => true,
            'image'          => null,
        ];
    }

    public function featured(): self
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function outOfStock(): self
    {
        return $this->state(fn () => ['stock_quantity' => 0]);
    }
}
