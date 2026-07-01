<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugProductSqliteTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_products_created(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Cheap Product',
            'price' => 100,
            'status' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Expensive Product',
            'price' => 200,
            'status' => true,
        ]);

        $this->assertSame(2, Product::count());
        $this->assertSame(1, Product::where('price', '>=', '150')->count());
    }
}