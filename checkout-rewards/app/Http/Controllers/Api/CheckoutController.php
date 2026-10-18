<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\RewardService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(
        Request $request,
        Cart $cart,
        RewardService $rewardService
    ): JsonResponse {
        $validated = $request->validate([
            'idempotency_key' => ['required', 'string', 'max:100'],
            'coupon_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:coupons,id',
            ],
        ]);

        try {
            $result = DB::transaction(function () use (
                $cart,
                $validated
            ) {
                $lockedCart = Cart::whereKey($cart->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Return an existing order only for the same key.
                $existingOrder = Order::where(
                    'cart_id',
                    $lockedCart->id
                )->first();

                if ($existingOrder) {
                    if (
                        $existingOrder->idempotency_key !==
                        $validated['idempotency_key']
                    ) {
                        abort(
                            409,
                            'This cart has already been checked out with a different idempotency key.'
                        );
                    }

                    return [
                        'order' => $existingOrder,
                        'created' => false,
                    ];
                }

                // Prevent a key from being reused for another cart.
                $keyOrder = Order::where(
                    'idempotency_key',
                    $validated['idempotency_key']
                )->first();

                if ($keyOrder) {
                    abort(
                        409,
                        'This idempotency key has already been used.'
                    );
                }

                if ($lockedCart->status !== 'open') {
                    abort(409, 'This cart is no longer open.');
                }

                $items = $lockedCart->items()
                    ->orderBy('product_id')
                    ->get();

                if ($items->isEmpty()) {
                    abort(422, 'Cannot checkout an empty cart.');
                }

                $coupon = null;
                $discountCents = 0;
                $grossCents = 0;
                $orderItems = [];

                // Lock the coupon before checking whether it is available.
                if (!empty($validated['coupon_id'])) {
                    $coupon = Coupon::whereKey(
                        $validated['coupon_id']
                    )->lockForUpdate()->firstOrFail();

                    if ($coupon->status !== 'available') {
                        abort(
                            409,
                            'This coupon has already been redeemed or is unavailable.'
                        );
                    }

                    if (
                        $coupon->discount_percent < 0 ||
                        $coupon->discount_percent > 100
                    ) {
                        abort(422, 'Coupon discount percentage is invalid.');
                    }
                }

                // Lock products in a consistent order to protect stock.
                foreach ($items as $item) {
                    $product = Product::whereKey($item->product_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($item->quantity > $product->stock) {
                        abort(
                            409,
                            "Insufficient stock for {$product->name}."
                        );
                    }

                    $unitPrice = $product->price_cents;
                    $lineTotal = $unitPrice * $item->quantity;

                    $grossCents += $lineTotal;

                    // Preserve the name and price at purchase time.
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price_cents' => $unitPrice,
                        'quantity' => $item->quantity,
                        'line_total_cents' => $lineTotal,
                    ];

                    $product->stock -= $item->quantity;
                    $product->save();
                }

                if ($coupon) {
                    $discountCents = intdiv(
                        $grossCents * $coupon->discount_percent,
                        100
                    );
                }

                $netCents = $grossCents - $discountCents;

                $order = Order::create([
                    'cart_id' => $lockedCart->id,
                    'idempotency_key' =>
                        $validated['idempotency_key'],
                    'gross_cents' => $grossCents,
                    'discount_cents' => $discountCents,
                    'net_cents' => $netCents,
                    'placed_at' => now(),
                ]);

                $order->items()->createMany($orderItems);

                // Redemption is committed or rolled back with the order.
                if ($coupon) {
                    $coupon->update([
                        'status' => 'redeemed',
                        'redeemed_order_id' => $order->id,
                        'redeemed_at' => now(),
                    ]);
                }

                $lockedCart->update([
                    'status' => 'checked_out',
                    'checked_out_at' => now(),
                ]);

                return [
                    'order' => $order,
                    'created' => true,
                ];
            }, 3);

        } catch (QueryException $exception) {
            // Recover if a concurrent request already created this order.
            $existingOrder = Order::where(
                'cart_id',
                $cart->id
            )->first();

            if ($existingOrder) {
                if (
                    $existingOrder->idempotency_key !==
                    $validated['idempotency_key']
                ) {
                    return response()->json([
                        'message' =>
                            'This cart has already been checked out with a different idempotency key.',
                    ], 409);
                }

                $result = [
                    'order' => $existingOrder,
                    'created' => false,
                ];
            } else {
                $keyOrder = Order::where(
                    'idempotency_key',
                    $validated['idempotency_key']
                )->first();

                if ($keyOrder) {
                    return response()->json([
                        'message' =>
                            'This idempotency key has already been used.',
                    ], 409);
                }

                // Do not hide unrelated database errors.
                throw $exception;
            }
        }

        // Run after the order transaction commits.
        // Safe to retry because milestone coupons are unique.
        $rewardService->generateEligibleCoupon();

        return response()->json([
            'message' => $result['created']
                ? 'Checkout successful.'
                : 'Checkout already processed.',
            'data' => $result['order']->load('items'),
        ], $result['created'] ? 201 : 200);
    }
}
