<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->select('id', 'name', 'price_cents', 'stock')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }
}
