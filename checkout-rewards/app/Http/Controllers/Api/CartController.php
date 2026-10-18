<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function store(): JsonResponse
    {
        $cart = Cart::create([
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Cart created successfully',
            'data' => $cart,
        ], 201);
    }

    public function addItem(Request $request, Cart $cart): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($cart->status !== 'open') {
            return response()->json([
                'message' => 'This cart is no longer open.',
            ], 409);
        }

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['quantity'] > $product->stock) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock.',
            ], 422);
        }

        $item = DB::transaction(function () use ($cart, $validated) {
            $item = $cart->items()
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($item) {
                $item->quantity += $validated['quantity'];
                $item->save();
                return $item;
            }

            return $cart->items()->create([
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        });

        return response()->json([
            'message' => 'Product added to cart',
            'data' => $item->load('product'),
        ], 201);
    }

    
public function show(Cart $cart): JsonResponse
{
    $cart->load('items.product');

    $items = $cart->items->map(function ($item) {
        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $item->product->name,
            'unit_price_cents' => $item->product->price_cents,
            'quantity' => $item->quantity,
            'line_total_cents' =>
                $item->product->price_cents * $item->quantity,
        ];
    });

    $total = $items->sum('line_total_cents');

    return response()->json([
        'data' => [
            'id' => $cart->id,
            'status' => $cart->status,
            'items' => $items,
            'total_cents' => $total,
        ],
    ]);
}


public function updateItem(Request $request, Cart $cart, int $item): JsonResponse
{
    $validated = $request->validate([
        'quantity' => ['required', 'integer', 'min:1'],
    ]);

    if ($cart->status !== 'open') {
        return response()->json([
            'message' => 'This cart is no longer open.',
        ], 409);
    }

    $cartItem = $cart->items()->findOrFail($item);

    $product = Product::findOrFail($cartItem->product_id);

    if ($validated['quantity'] > $product->stock) {
        return response()->json([
            'message' => 'Requested quantity exceeds available stock.',
        ], 422);
    }

    $cartItem->update([
        'quantity' => $validated['quantity'],
    ]);

    return response()->json([
        'message' => 'Cart item updated successfully.',
        'data' => $cartItem->load('product'),
    ]);
}

public function removeItem(Cart $cart, int $item): JsonResponse
{
    if ($cart->status !== 'open') {
        return response()->json([
            'message' => 'This cart is no longer open.',
        ], 409);
    }

    $cartItem = $cart->items()->findOrFail($item);
    $cartItem->delete();

    return response()->json([
        'message' => 'Cart item removed successfully.',
    ]);
}


}
