<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RewardService;
use Illuminate\Http\JsonResponse;

class AdminCouponController extends Controller
{
    public function generate(RewardService $rewardService): JsonResponse
    {
        $coupon = $rewardService->generateNextEligibleCoupon();

        if ($coupon === null) {
            return response()->json([
                'message' => 'No new reward milestone is eligible for coupon generation.',
            ], 409);
        }

        return response()->json([
            'message' => 'Reward coupon generated successfully.',
            'data' => [
                'id' => $coupon->id,
                'milestone_order_count' => $coupon->milestone_order_count,
                'discount_percent' => $coupon->discount_percent,
                'status' => $coupon->status,
            ],
        ], 201);
    }
}
