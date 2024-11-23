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

        $amount = $data->sum(function ($product) {
            $price = $product->product->price ?? $product->product->sale_price;
            return $price * $product->quantity;
        });

        return response()->json(['message' => 'Success get all cart', 'data' => CartResource::collection($data), 'amount' => $amount]);
    }

    public function createCart(CreateCartRequest $request)
    {

        $data = $request->validated();
        $cart = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($cart) {
            $cart->quantity += $data['quantity'];
            $cart->save();
        } else {
            $cart = new Cart($data);
            $cart->user_id = $request->user()->id;
            $cart->save();
        }

        return response()->json([
            'message' => 'Success add to cart',
            'data' => new CartResource($cart),
        ]);
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
