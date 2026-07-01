<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
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
            'price' => 49.99,
        ]);
    }

    public function test_can_view_empty_wishlist(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/wishlist');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }

    public function test_can_toggle_add_to_wishlist(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/wishlist/{$this->product->id}");

        $response->assertOk()
            ->assertJsonPath('data.in_wishlist', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_can_toggle_remove_from_wishlist(): void
    {
        $this->actingAs($this->user)->postJson("/api/wishlist/{$this->product->id}");

        $response = $this->actingAs($this->user)->postJson("/api/wishlist/{$this->product->id}");

        $response->assertOk()
            ->assertJsonPath('data.in_wishlist', false);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_can_remove_from_wishlist(): void
    {
        $this->actingAs($this->user)->postJson("/api/wishlist/{$this->product->id}");

        $response = $this->actingAs($this->user)->deleteJson("/api/wishlist/{$this->product->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_wishlist_lists_all_items(): void
    {
        $product2 = Product::factory()->create([
            'category_id' => $this->product->category_id,
            'price' => 99.99,
        ]);

        $this->actingAs($this->user)->postJson("/api/wishlist/{$this->product->id}");
        $this->actingAs($this->user)->postJson("/api/wishlist/{$product2->id}");

        $response = $this->actingAs($this->user)->getJson('/api/wishlist');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }
}
