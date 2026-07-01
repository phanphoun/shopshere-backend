<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
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

    public function test_can_view_order_history(): void
    {
        // Place an order first
        $this->actingAs($this->user)->postJson('/api/checkout', [
            'shipping_address' => '123 Test St',
            'phone' => '+1234567890',
            'payment_method' => 'cod',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_view_single_order_detail(): void
    {
        $this->actingAs($this->user)->postJson('/api/checkout', [
            'shipping_address' => '123 Test St',
            'phone' => '+1234567890',
            'payment_method' => 'cod',
        ]);

        $orderId = Order::first()->id;

        $response = $this->actingAs($this->user)->getJson("/api/orders/{$orderId}");

        $response->assertOk()
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_cannot_view_other_users_order(): void
    {
        $otherUser = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $otherUser->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($otherUser)->postJson('/api/checkout', [
            'shipping_address' => '456 Other St',
            'phone' => '+0987654321',
            'payment_method' => 'cod',
        ]);

        $orderId = Order::first()->id;

        $response = $this->actingAs($this->user)->getJson("/api/orders/{$orderId}");

        $response->assertForbidden();
    }

    public function test_order_history_shows_multiple_orders(): void
    {
        // Place 3 orders using the cart API to add items before each checkout
        $product = Product::factory()->create([
            'category_id' => $this->product->category_id,
            'price' => 25,
            'stock_quantity' => 100,
        ]);

        for ($i = 0; $i < 3; $i++) {
            // Add item via cart API (ensures it goes to the correct cart)
            $this->actingAs($this->user)->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

            $this->actingAs($this->user)->postJson('/api/checkout', [
                'shipping_address' => '123 Test St',
                'phone' => '+1234567890',
                'payment_method' => 'cod',
            ]);
        }

        $response = $this->actingAs($this->user)->getJson('/api/orders');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_returns_404_for_nonexistent_order(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/orders/99999');

        $response->assertNotFound();
    }
}
