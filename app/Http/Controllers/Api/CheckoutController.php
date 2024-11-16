<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function calculateCost(Request $request)
    {
        $data = $request->validate([
            'destination_id' => "required|numeric"
        ]);

        $carts = Cart::with('product')->where('user_id', $request->user()->id)->get();

        if ($carts->count() < 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'error' => [
                        'Your carts is empty!'
                    ]
                ]
            ], 400));
        }

        $data["weight"] = 0;
        foreach ($carts as $cart) {
            $data["weight"] += $cart->product->weight * $cart->quantity;
        }

        $rajaOngkirResponse = Http::withHeader('key', env('RAJAONGKIR_API_KEY'))->post('https://api.rajaongkir.com/starter/cost', ["origin" => 10, "destination" => $data["destination_id"], "weight" => $data["weight"], "courier" => "jne"]);

        return response()->json(["data" => $rajaOngkirResponse->json()['rajaongkir']['results']], 200);
    }
}
