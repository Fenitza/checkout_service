<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        $order->load('items');

        return response()->json([
            'data' => [
                'id' => $order->id,
                'status' => 'placed',
                'idempotency_key' => $order->idempotency_key,
                'gross_cents' => $order->gross_cents,
                'discount_cents' => $order->discount_cents,
                'net_cents' => $order->net_cents,
                'placed_at' => $order->placed_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'unit_price_cents' => $item->unit_price_cents,
                        'quantity' => $item->quantity,
                        'line_total_cents' => $item->line_total_cents,
                    ];
                })->values(),
            ],
        ]);
    }
}
