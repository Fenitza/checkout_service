<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index(): JsonResponse
    {
        $totals = Order::query()
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(gross_cents), 0) as gross_cents,
                COALESCE(SUM(discount_cents), 0) as discount_cents,
                COALESCE(SUM(net_cents), 0) as net_cents
            ')
            ->first();

        $products = OrderItem::query()
    ->select([
        'product_id',
        'product_name',
    ])
    ->selectRaw('SUM(quantity) as quantity_sold')
    ->selectRaw('SUM(line_total_cents) as gross_cents')
    ->groupBy('product_id', 'product_name')
    ->orderBy('product_name')
    ->get()
    ->map(function ($product) {
        return [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'quantity_sold' => (int) $product->quantity_sold,
            'gross_cents' => (int) $product->gross_cents,
        ];
    });
        $coupons = Coupon::query()
            ->select('status', DB::raw('COUNT(*) as quantity'))
            ->groupBy('status')
            ->pluck('quantity', 'status');

        return response()->json([
            'orders' => [
                'total' => (int) $totals->total_orders,
                'gross_cents' => (int) $totals->gross_cents,
                'discount_cents' => (int) $totals->discount_cents,
                'net_cents' => (int) $totals->net_cents,
            ],
            'products' => $products,
            'coupons' => [
                'generated' => (int) $coupons->sum(),
                'available' => (int) ($coupons['available'] ?? 0),
                'redeemed' => (int) ($coupons['redeemed'] ?? 0),
            ],
        ]);
    }
}
