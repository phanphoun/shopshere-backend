<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data', 'meta']);
    }

    public function test_can_filter_products(): void
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

        $response = $this->getJson('/api/products?min_price=150&per_page=5');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data', 'meta'])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Expensive Product');
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'Laptop Pro']);
        Product::factory()->create(['name' => 'Tablet']);

        $response = $this->getJson('/api/products/search?q=laptop');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_get_product_detail(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertNotFound();
    }

    public function test_can_get_featured_products(): void
    {
        Product::factory()->count(5)->create(['featured' => true]);

        $response = $this->getJson('/api/products/featured?limit=3');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_limit_is_capped(): void
    {
        Product::factory()->count(60)->create(['featured' => true]);

        $response = $this->getJson('/api/products/featured?limit=100');

        $response->assertOk();
        $this->assertLessThanOrEqual(50, count($response->json('data')));
    }
}
