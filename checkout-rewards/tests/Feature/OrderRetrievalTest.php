<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_order_totals_and_item_snapshots(): void
    {
        $product = Product::create([
            'name' => 'Current Product Name',
            'price_cents' => 2000,
            'stock' => 5,
        ]);

        $cart = Cart::create([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        $order = Order::create([
            'cart_id' => $cart->id,
            'idempotency_key' => 'order-retrieval-test-001',
            'gross_cents' => 3000,
            'discount_cents' => 300,
            'net_cents' => 2700,
            'placed_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Name at Purchase',
            'unit_price_cents' => 1500,
            'quantity' => 2,
            'line_total_cents' => 3000,
        ]);

        // Change the current product after the order was placed.
        $product->update([
            'name' => 'Updated Product Name',
            'price_cents' => 2500,
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.gross_cents', 3000)
            ->assertJsonPath('data.discount_cents', 300)
            ->assertJsonPath('data.net_cents', 2700)
            ->assertJsonPath(
                'data.items.0.product_name',
                'Name at Purchase'
            )
            ->assertJsonPath('data.items.0.unit_price_cents', 1500);
    }

    public function test_it_returns_404_for_an_unknown_order(): void
    {
        $this->getJson('/api/orders/999999')->assertNotFound();
    }
}
