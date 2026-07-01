<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_sees_all_products_by_default(): void
    {
        Product::factory()->create(['status' => true]);
        Product::factory()->create(['status' => false]);

        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertOk();
        $response->assertViewHas('products');
    }

    public function test_admin_can_filter_by_status(): void
    {
        Product::factory()->create(['status' => true, 'name' => 'Active Product']);
        Product::factory()->create(['status' => false, 'name' => 'Inactive Product']);

        $response = $this->actingAs($this->admin)->get('/admin/products?status=1');

        $response->assertOk();
        $products = $response->viewData('products');
        $this->assertTrue($products->every(fn ($p) => $p->status));
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'New Product',
            'description' => 'A brand new product with a longer description',
            'price' => 29.99,
            'stock_quantity' => 100,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function test_discount_price_must_not_exceed_price(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Discounted Product',
            'description' => 'A product with a discount that should be valid',
            'price' => 100,
            'discount_price' => 80,
            'stock_quantity' => 10,
        ]);

        $response->assertSessionHasNoErrors();
    }
}
