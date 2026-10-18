<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;

Route::get('/products', [ProductController::class, 'index']);

Route::post('/carts', [CartController::class, 'store']);
Route::get('/carts/{cart}', [CartController::class, 'show']);
Route::post('/carts/{cart}/items', [CartController::class, 'addItem']);
Route::put('/carts/{cart}/items/{item}', [CartController::class, 'updateItem']);
Route::delete('/carts/{cart}/items/{item}', [CartController::class, 'removeItem']);

Route::post('/carts/{cart}/checkout', [CheckoutController::class, 'checkout']);
