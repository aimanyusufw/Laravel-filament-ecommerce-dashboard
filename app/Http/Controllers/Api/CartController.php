<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

use function PHPSTORM_META\map;

class CartController extends Controller
{
    public function getAllCart(Request $request)
    {
        $data = Cart::with('product')->where('user_id', $request->user()->id)->get();
        return response()->json(['message' => 'Success get all cart', 'data' => CartResource::collection($data)]);
    }

    public function createCart(CreateCartRequest $request)
    {
        $data = $request->validated();

        $cart = new Cart($data);
        $cart->user_id = $request->user()->id;
        $cart->save();

        return response()->json(['message' => 'Success add to cart', 'data' => $data]);
    }

    public function updateCart(Request $request, Cart $cart)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        if ($cart->user_id !== $request->user()->id) {
            throw new HttpResponseException(response(['message' => 'Unauthenticated.'], 401));
        }

        $cart->quantity = $data['quantity'];
        $cart->save();

        return response()->json(['message' => 'Success update cart data', 'data' => $data]);
    }

    public function deleteCart(Request $request, Cart $cart)
    {

        if ($cart->user_id !== $request->user()->id) {
            throw new HttpResponseException(response(['message' => 'Unauthenticated.'], 401));
        }

        $data = $cart->delete();

        return response()->json(['message' => 'Success delete cart', 'data' => $data]);
    }
}