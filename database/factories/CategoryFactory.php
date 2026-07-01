<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name) . '-' . Str::lower(Str::random(4)),
            'description' => fake()->sentence(10),
            'image'       => null,
            'status'      => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['status' => false]);
    }
}
