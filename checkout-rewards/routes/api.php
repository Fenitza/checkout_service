<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Models\Coupon;
use App\Http\Controllers\Api\AdminReportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminCouponController;

Route::get('/products', [ProductController::class, 'index']);

Route::post('/carts', [CartController::class, 'store']);
Route::get('/carts/{cart}', [CartController::class, 'show']);
Route::post('/carts/{cart}/items', [CartController::class, 'addItem']);
Route::put('/carts/{cart}/items/{item}', [CartController::class, 'updateItem']);
Route::delete('/carts/{cart}/items/{item}', [CartController::class, 'removeItem']);

Route::post('/carts/{cart}/checkout', [CheckoutController::class, 'checkout']);

Route::get('/coupons/available', function () {
    $coupons = Coupon::where('status', 'available')
        ->orderBy('milestone_order_count')
        ->get([
            'id',
            'milestone_order_count',
            'discount_percent',
            'status',
        ]);

    return response()->json([
        'data' => $coupons,
    ]);
});

Route::get('/admin/report', [AdminReportController::class, 'index']);

Route::get('/orders/{order}', [OrderController::class, 'show']);

Route::post(
    '/admin/coupons/generate',
    [AdminCouponController::class, 'generate']
);