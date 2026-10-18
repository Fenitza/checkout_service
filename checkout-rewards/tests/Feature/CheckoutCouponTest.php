<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_redeems_coupon_and_applies_discount(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price_cents' => 1000,
            'stock' => 5,
        ]);

        $cart = Cart::create([
            'status' => 'open',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $coupon = Coupon::create([
            'milestone_order_count' => 5,
            'discount_percent' => 10,
            'status' => 'available',
        ]);

        $response = $this->postJson(
            "/api/carts/{$cart->id}/checkout",
            [
                'idempotency_key' => 'coupon-test-checkout-001',
                'coupon_id' => $coupon->id,
            ]
        );

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders', [
            'cart_id' => $cart->id,
            'gross_cents' => 1000,
            'discount_cents' => 100,
            'net_cents' => 900,
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'status' => 'redeemed',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 4,
        ]);
    }
    
public function test_failed_checkout_does_not_redeem_coupon(): void
{
    $product = Product::create([
        'name' => 'Limited Stock Product',
        'price_cents' => 1000,
        'stock' => 0,
    ]);

    $cart = Cart::create([
        'status' => 'open',
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $coupon = Coupon::create([
        'milestone_order_count' => 5,
        'discount_percent' => 10,
        'status' => 'available',
    ]);

    $response = $this->postJson(
        "/api/carts/{$cart->id}/checkout",
        [
            'idempotency_key' => 'coupon-test-failed-001',
            'coupon_id' => $coupon->id,
        ]
    );

    $response->assertStatus(409);

    $this->assertDatabaseMissing('orders', [
        'cart_id' => $cart->id,
    ]);

    $this->assertDatabaseHas('coupons', [
        'id' => $coupon->id,
        'status' => 'available',
        'redeemed_order_id' => null,
    ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 0,
    ]);
}


public function test_redeemed_coupon_cannot_be_used_again(): void
{
    $product = Product::create([
        'name' => 'Test Product',
        'price_cents' => 1000,
        'stock' => 10,
    ]);

    $coupon = Coupon::create([
        'milestone_order_count' => 5,
        'discount_percent' => 10,
        'status' => 'available',
    ]);

    // First cart successfully uses the coupon.
    $firstCart = Cart::create(['status' => 'open']);

    CartItem::create([
        'cart_id' => $firstCart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $firstResponse = $this->postJson(
        "/api/carts/{$firstCart->id}/checkout",
        [
            'idempotency_key' => 'coupon-first-use-001',
            'coupon_id' => $coupon->id,
        ]
    );

    $firstResponse->assertSuccessful();

    // A second cart attempts to reuse the same coupon.
    $secondCart = Cart::create(['status' => 'open']);

    CartItem::create([
        'cart_id' => $secondCart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $secondResponse = $this->postJson(
        "/api/carts/{$secondCart->id}/checkout",
        [
            'idempotency_key' => 'coupon-second-use-001',
            'coupon_id' => $coupon->id,
        ]
    );

    $secondResponse->assertStatus(409);

    $this->assertDatabaseMissing('orders', [
        'cart_id' => $secondCart->id,
    ]);

    $this->assertDatabaseHas('coupons', [
        'id' => $coupon->id,
        'status' => 'redeemed',
    ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 9,
    ]);
}


public function test_checked_out_cart_cannot_use_a_different_idempotency_key(): void
{
    $product = Product::create([
        'name' => 'Test Product',
        'price_cents' => 1000,
        'stock' => 5,
    ]);

    $cart = Cart::create(['status' => 'open']);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $firstResponse = $this->postJson(
        "/api/carts/{$cart->id}/checkout",
        ['idempotency_key' => 'idempotency-test-001']
    );

    $firstResponse->assertCreated();

    $secondResponse = $this->postJson(
        "/api/carts/{$cart->id}/checkout",
        ['idempotency_key' => 'idempotency-test-002']
    );

    $secondResponse->assertStatus(409);

    $this->assertDatabaseCount('orders', 1);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 4,
    ]);
}

public function test_idempotency_key_cannot_be_reused_for_another_cart(): void
{
    $product = Product::create([
        'name' => 'Test Product',
        'price_cents' => 1000,
        'stock' => 5,
    ]);

    $firstCart = Cart::create(['status' => 'open']);

    CartItem::create([
        'cart_id' => $firstCart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $key = 'idempotency-shared-test-001';

    $this->postJson(
        "/api/carts/{$firstCart->id}/checkout",
        ['idempotency_key' => $key]
    )->assertCreated();

    $secondCart = Cart::create(['status' => 'open']);

    CartItem::create([
        'cart_id' => $secondCart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->postJson(
        "/api/carts/{$secondCart->id}/checkout",
        ['idempotency_key' => $key]
    )->assertStatus(409);

    $this->assertDatabaseCount('orders', 1);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 4,
    ]);
}

}
