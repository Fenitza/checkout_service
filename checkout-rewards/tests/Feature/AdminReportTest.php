<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_returns_order_revenue_and_product_totals(): void
    {
        $cart = Cart::create([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        $order = Order::create([
            'cart_id' => $cart->id,
            'idempotency_key' => 'report-test-order-001',
            'gross_cents' => 3000,
            'discount_cents' => 300,
            'net_cents' => 2700,
            'placed_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Test Keyboard',
            'unit_price_cents' => 1500,
            'quantity' => 2,
            'line_total_cents' => 3000,
        ]);

        $response = $this->getJson('/api/admin/report');

        $response->assertOk()
            ->assertJsonPath('orders.total', 1)
            ->assertJsonPath('orders.gross_cents', 3000)
            ->assertJsonPath('orders.discount_cents', 300)
            ->assertJsonPath('orders.net_cents', 2700)
            ->assertJsonPath('products.0.product_name', 'Test Keyboard')
            ->assertJsonPath('products.0.quantity_sold', 2)
            ->assertJsonPath('products.0.gross_cents', 3000)
            ->assertJsonPath('coupons.generated', 0)
            ->assertJsonPath('coupons.available', 0)
            ->assertJsonPath('coupons.redeemed', 0);
    }

    public function test_report_does_not_change_database_records(): void
    {
        $response = $this->getJson('/api/admin/report');

        $response->assertOk();

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('coupons', 0);
    }
}
