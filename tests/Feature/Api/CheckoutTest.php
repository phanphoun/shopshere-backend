<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
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
            'price' => 50,
            'stock_quantity' => 10,
        ]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_can_place_order(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/checkout', [
            'shipping_address' => '123 Test St',
            'phone' => '+1234567890',
            'payment_method' => 'cod',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_status' => 'unpaid',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 8,
        ]);
    }

    public function test_cannot_checkout_with_empty_cart(): void
    {
        Cart::where('user_id', $this->user->id)->first()->items()->delete();

        $response = $this->actingAs($this->user)->postJson('/api/checkout', [
            'shipping_address' => '123 Test St',
            'phone' => '+1234567890',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422);
    }

    public function test_new_orders_are_unpaid(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/checkout', [
            'shipping_address' => '123 Test St',
            'phone' => '+1234567890',
            'payment_method' => 'stripe',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_status' => 'unpaid',
        ]);
    }
}
