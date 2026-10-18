<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\RewardService;

class TestRewardGeneration extends Command
{
    protected $signature = 'rewards:test-generation';

    protected $description = 'Test reward generation without modifying orders';

    public function handle(RewardService $rewardService): int
    {
        $actualOrderCount = Order::count();

        $this->info("Current successful orders: {$actualOrderCount}");

        if ($actualOrderCount < 5) {
            $this->warn(
                'Fewer than 5 orders exist. No milestone-5 coupon should be generated.'
            );
        }

        $coupon = $rewardService->generateEligibleCoupon();

        if ($coupon) {
            $this->info(
                "Coupon milestone: {$coupon->milestone_order_count}"
            );
            $this->info(
                "Discount: {$coupon->discount_percent}%"
            );
            $this->info(
                "Status: {$coupon->status}"
            );
        } else {
            $this->info('No eligible coupon was generated.');
        }

        return self::SUCCESS;
    }
}
