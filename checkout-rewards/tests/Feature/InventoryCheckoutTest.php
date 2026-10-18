<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_carts_cannot_purchase_the_last_unit(): void
    {
        // Arrange: only one unit is available.
        $product = Product::create([
            'name' => 'Limited Stock Webcam',
            'price_cents' => 3999,
            'stock' => 1,
        ]);

        $cart1 = Cart::create(['status' => 'open']);
        $cart2 = Cart::create(['status' => 'open']);

        CartItem::create([
            'cart_id' => $cart1->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        CartItem::create([
            'cart_id' => $cart2->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Act: the first customer checks out.
        $firstResponse = $this->postJson(
            "/api/carts/{$cart1->id}/checkout",
            ['idempotency_key' => 'inventory-test-cart-1']
        );

        // The first order should succeed.
        $firstResponse->assertCreated();

        // The second customer tries to buy the same last unit.
        $secondResponse = $this->postJson(
            "/api/carts/{$cart2->id}/checkout",
            ['idempotency_key' => 'inventory-test-cart-2']
        );

        // Assert: insufficient inventory is a conflict.
        $secondResponse->assertStatus(409);

        // Only one order exists and no inventory was oversold.
        $this->assertSame(1, Order::count());

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 0,
        ]);
    }
}
