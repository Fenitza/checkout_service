<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class RewardService
{
    public function generateEligibleCoupon(): ?Coupon
    {
        return DB::transaction(function () {
            // Serialize reward generation using the settings rows.
            $settings = Setting::whereIn('key', [
                'reward_every_n_orders',
                'reward_discount_percent',
            ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('key');

            $everyNOrders = (int) (
                $settings->get('reward_every_n_orders')?->value ?? 5
            );

            $discountPercent = (int) (
                $settings->get('reward_discount_percent')?->value ?? 10
            );

            if (
                $everyNOrders < 1 ||
                $discountPercent < 0 ||
                $discountPercent > 100
            ) {
                throw new \RuntimeException('Reward settings are invalid.');
            }

            // Count successfully committed orders.
            $orderCount = Order::count();

            if ($orderCount < $everyNOrders) {
                return null;
            }

            $latestCoupon = null;

            // Create any missing milestone coupons.
            for (
                $milestone = $everyNOrders;
                $milestone <= $orderCount;
                $milestone += $everyNOrders
            ) {
                $coupon = Coupon::firstOrCreate(
                    ['milestone_order_count' => $milestone],
                    [
                        'discount_percent' => $discountPercent,
                        'status' => 'available',
                    ]
                );

                $latestCoupon = $coupon;
            }

            return $latestCoupon;
        }, 3);
    }

    
public function generateNextEligibleCoupon(): ?Coupon
{
    return DB::transaction(function () {
        $settings = Setting::whereIn('key', [
            'reward_every_n_orders',
            'reward_discount_percent',
        ])
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('key');

        $everyNOrders = (int) (
            $settings->get('reward_every_n_orders')?->value ?? 5
        );

        $discountPercent = (int) (
            $settings->get('reward_discount_percent')?->value ?? 10
        );

        if (
            $everyNOrders < 1 ||
            $discountPercent < 0 ||
            $discountPercent > 100
        ) {
            throw new \RuntimeException('Reward settings are invalid.');
        }

        $orderCount = Order::count();

        if ($orderCount < $everyNOrders) {
            return null;
        }

        for (
            $milestone = $everyNOrders;
            $milestone <= $orderCount;
            $milestone += $everyNOrders
        ) {
            $existingCoupon = Coupon::where(
                'milestone_order_count',
                $milestone
            )->exists();

            if (!$existingCoupon) {
                return Coupon::create([
                    'milestone_order_count' => $milestone,
                    'discount_percent' => $discountPercent,
                    'status' => 'available',
                ]);
            }
        }

        return null;
    }, 3);
}

}
