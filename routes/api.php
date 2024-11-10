<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;
use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'registerUser']);
    Route::post('login', [AuthController::class, 'loginUser']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('user', [AuthController::class, 'deleteUser']);
        Route::delete('logout', [AuthController::class, 'logoutUser']);
    });
});

Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('', [UserController::class, 'getUser']);
    Route::post('', [UserController::class, 'updateOrCreateUserDetail']);
});

Route::middleware('auth:sanctum')->prefix('product')->group(function () {
    Route::get('', [ProductController::class, 'getAllProduct']);
    Route::get('{product}', [ProductController::class, 'showProduct']);
});

Route::middleware('auth:sanctum')->prefix('category')->group(function () {
    Route::get('', [CategoryController::class, 'getAllCategories']);
    Route::get('{category}', [CategoryController::class, 'showCategory']);
});

Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('', [CartController::class, 'getAllCart']);
    Route::post('', [CartController::class, 'createCart']);
    Route::put('{cart}', [CartController::class, 'updateCart']);
    Route::delete('{cart}', [CartController::class, 'deleteCart']);
});

Route::get('/provinces', function () {
    return response()->json(["data" => Province::all()], 200);
});
Route::get('/cities/{province_id}', function ($province_id) {
    return response()->json(["data" => City::where('province_id', $province_id)->get()], 200);
});
