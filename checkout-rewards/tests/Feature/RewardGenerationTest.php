<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use App\Services\RewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_is_not_generated_before_fifth_order(): void
    {
        Setting::create([
            'key' => 'reward_every_n_orders',
            'value' => '5',
        ]);

        Setting::create([
            'key' => 'reward_discount_percent',
            'value' => '10',
        ]);

        $this->createOrders(4);

        $coupon = app(RewardService::class)->generateEligibleCoupon();

        $this->assertNull($coupon);
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_coupon_is_generated_at_fifth_order(): void
    {
        Setting::create([
            'key' => 'reward_every_n_orders',
            'value' => '5',
        ]);

        Setting::create([
            'key' => 'reward_discount_percent',
            'value' => '10',
        ]);

        $this->createOrders(5);

        $coupon = app(RewardService::class)->generateEligibleCoupon();

        $this->assertNotNull($coupon);
        $this->assertSame(5, $coupon->milestone_order_count);
        $this->assertSame(10, $coupon->discount_percent);
        $this->assertSame('available', $coupon->status);

        $this->assertDatabaseCount('coupons', 1);
    }

    public function test_repeated_generation_does_not_duplicate_coupon(): void
    {
        Setting::create([
            'key' => 'reward_every_n_orders',
            'value' => '5',
        ]);

        Setting::create([
            'key' => 'reward_discount_percent',
            'value' => '10',
        ]);

        $this->createOrders(5);

        $service = app(RewardService::class);

        $service->generateEligibleCoupon();
        $service->generateEligibleCoupon();

        $this->assertDatabaseCount('coupons', 1);
    }

    private function createOrders(int $count): void
    {
        // These orders satisfy the order-count query. They don't represent
        // full checkout flows, which are tested separately.
        for ($i = 1; $i <= $count; $i++) {
            $cartId = \App\Models\Cart::create([
                'status' => 'checked_out',
                'checked_out_at' => now(),
            ])->id;

            Order::create([
                'cart_id' => $cartId,
                'idempotency_key' => "reward-test-order-{$i}",
                'gross_cents' => 1000,
                'discount_cents' => 0,
                'net_cents' => 1000,
                'placed_at' => now(),
            ]);
        }
    }
}
