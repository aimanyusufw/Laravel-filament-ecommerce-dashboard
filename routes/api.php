<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'registerUser']);
    Route::post('login', [AuthController::class, 'loginUser']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'getUser']);
        Route::delete('user', [AuthController::class, 'deleteUser']);
        Route::delete('logout', [AuthController::class, 'logoutUser']);
    });
});

Route::middleware('auth:sanctum')->prefix('product')->group(function () {
    Route::get('', [ProductController::class, 'getAllProduct']);
});

Route::get('/provinces', function () {
    return response()->json(["data" => Province::all()], 200);
});
Route::get('/cities/{province_id}', function ($province_id) {
    return response()->json(["data" => City::where('province_id', $province_id)->get()], 200);
});
