<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 29.99,
            'stock_quantity' => 10,
        ]);
    }

    public function test_can_get_empty_cart(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/cart');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['cart', 'summary']]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_add_out_of_stock_product(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 0,
            'status' => true,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_add_inactive_product(): void
    {
        $product = Product::factory()->create([
            'status' => false,
            'stock_quantity' => 10,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $this->actingAs($this->user)->postJson('/api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)->putJson('/api/cart/update', [
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $this->actingAs($this->user)->postJson('/api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $itemId = $this->user->cart->items->first()->id;

        $response = $this->actingAs($this->user)->deleteJson("/api/cart/remove/{$itemId}");

        $response->assertOk();
        $this->assertDatabaseMissing('cart_items', ['id' => $itemId]);
    }

    public function test_can_clear_cart(): void
    {
        $this->actingAs($this->user)->postJson('/api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($this->user)->deleteJson('/api/cart/clear');

        $response->assertOk();
        $this->assertCount(0, $this->user->cart->items);
    }
}
