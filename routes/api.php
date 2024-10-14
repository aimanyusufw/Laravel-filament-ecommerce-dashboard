<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/provinces', function () {
    return response()->json(["data" => Province::all()], 200);
});
Route::get('/cities/{province_id}', function ($province_id) {
    return response()->json(["data" => City::where('province_id', $province_id)->get()], 200);
});
