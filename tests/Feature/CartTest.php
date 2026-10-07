<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_add_approved_product_to_cart_via_fetch_endpoint(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();
        $product = Product::approved()->where('stock', '>', 5)->first();
        $this->assertNotNull($product);

        $response = $this->actingAs($buyer)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cart_count' => 2,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_add_pending_product_to_cart(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();
        $pendingProduct = Product::where('status', 'pending')->first();
        $this->assertNotNull($pendingProduct);

        $response = $this->actingAs($buyer)->postJson('/cart', [
            'product_id' => $pendingProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_adding_same_product_variant_increments_quantity_instead_of_duplicate(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();
        $product = Product::approved()->has('variants')->first();
        $this->assertNotNull($product);
        $variant = $product->variants()->where('stock', '>', 10)->first();
        $this->assertNotNull($variant);

        // First add
        $this->actingAs($buyer)->postJson('/cart', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertStatus(200);

        // Second add of the same item
        $response = $this->actingAs($buyer)->postJson('/cart', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cart_count' => 5,
            ]);

        $userCart = Cart::where('user_id', $buyer->id)->first();
        $this->assertEquals(1, CartItem::where('cart_id', $userCart->id)->where('product_id', $product->id)->where('variant_id', $variant->id)->count());
        $this->assertEquals(5, CartItem::where('cart_id', $userCart->id)->where('product_id', $product->id)->where('variant_id', $variant->id)->first()->quantity);
    }

    public function test_quantity_is_capped_at_maximum_available_stock(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();
        $product = Product::approved()->where('stock', '<=', 10)->where('stock', '>', 0)->first();
        $this->assertNotNull($product);
        $maxStock = $product->stock;

        $response = $this->actingAs($buyer)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => $maxStock + 100, // Exceeding stock
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_capped' => true,
                'cart_count' => $maxStock,
            ]);

        $userCart = Cart::where('user_id', $buyer->id)->first();
        $cartItem = CartItem::where('cart_id', $userCart->id)->where('product_id', $product->id)->first();
        $this->assertEquals($maxStock, $cartItem->quantity);
    }

    public function test_cart_stepper_update_endpoint_recalculates_totals(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();
        $product = Product::approved()->where('stock', '>', 10)->first();
        $this->assertNotNull($product);

        $addResponse = $this->actingAs($buyer)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $itemId = $addResponse->json('item.id');

        $updateResponse = $this->actingAs($buyer)->putJson("/cart/{$itemId}", [
            'quantity' => 4,
        ]);

        $expectedLineTotal = $product->price * 4;
        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'quantity' => 4,
                'line_total' => $expectedLineTotal,
                'cart_count' => 4,
            ]);
    }

    public function test_cart_remove_item_endpoint_works(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();
        $product = Product::approved()->where('stock', '>', 5)->first();
        $addResponse = $this->actingAs($buyer)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $itemId = $addResponse->json('item.id');

        $deleteResponse = $this->actingAs($buyer)->deleteJson("/cart/{$itemId}");
        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cart_count' => 0,
                'is_empty' => true,
            ]);

        $this->assertDatabaseMissing('cart_items', ['id' => $itemId]);
    }

    public function test_guest_localStorage_cart_merges_on_login(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();
        $product = Product::approved()->where('stock', '>', 5)->first();

        $response = $this->actingAs($buyer)->postJson('/cart/merge', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cart_count' => 3,
            ]);

        $userCart = Cart::where('user_id', $buyer->id)->first();
        $this->assertNotNull($userCart);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }
}
