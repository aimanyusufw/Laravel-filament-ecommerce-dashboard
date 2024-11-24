<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingAddressResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\UserShippingAddress;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function calculateCost(Request $request)
    {
        $data = $request->validate([
            'user_shipping_addresses_id' => 'required|exists:user_shipping_addresses,id'
        ]);

        $address = UserShippingAddress::with('province', 'city')->findOrFail($data['user_shipping_addresses_id']);

        $carts = Cart::with('product')->where('user_id', $request->user()->id)->get();
        if ($carts->isEmpty()) {
            throw new HttpResponseException(response([
                'errors' => ['error' => ['Your cart is empty!']]
            ], 400));
        }

        $totalWeight = $carts->sum(fn($cart) => $cart->product->weight * $cart->quantity);
        $response = Http::withHeader('key', env('RAJAONGKIR_API_KEY'))->post('https://api.rajaongkir.com/starter/cost', ["origin" => 92, "destination" => $address->shipping_city_id, "weight" => $totalWeight, "courier" => "jne"]);

        if ($response->failed()) {
            return response()->json([
                'errors' => ['error' => ['Failed to fetch shipping cost!']]
            ], 500);
        }

        $costs = [];
        foreach ($response->json()['rajaongkir']['results'] as $key => $service) {
            $costs[] = [
                'name' => $service['name'],
                'code' => $service['code'],
                'costs' => []
            ];

            foreach ($service['costs'] as $cost) {
                $costs[$key]['costs'][] = [
                    "service" => $cost['service'],
                    "description" => $cost['description'],
                    "cost" => $cost['cost'][0]['value'],
                    "etd" => $cost['cost'][0]['etd']
                ];
            }
        }

        return [
            'message' => 'Calculate costs successfully',
            'data' => [
                'address' => new ShippingAddressResource($address),
                'carts' => $carts,
                'costs' => $costs
            ]
        ];
    }


    public function checkOut(Request $request)
    {
        $data = $request->validate([
            'service_id' => 'required|string',
            'notes' => 'string|max:100'
        ]);

        $shippingCosts = $this->calculateCost($request);
        $shippingDetail = collect($shippingCosts['data']['costs'])
            ->flatMap(function ($item) {
                return $item['costs'];
            })
            ->firstWhere('service', $data['service_id']);

        if (!$shippingDetail) {
            return response()->json([
                'errors' => ['error' => ['Invalid service ID!']],
            ], 400);
        }

        $subtotal = $shippingCosts['data']['carts']->sum(function ($product) {
            $price = $product->product->sale_price ?? $product->product->price;
            return $price * $product->quantity;
        });

        $orderCode = 'ORD-' . date('Y-m-d') . '-' . rand(100000, 999999);

        $order = Order::create([
            'user_id' => $request->user()->id,
            'shipping_detail' => $shippingDetail,
            'shipping_address_detail' => $shippingCosts['data']['address'],
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingDetail['cost'],
            'tax' => $subtotal * 0.11,
            'grand_total' => $subtotal + $subtotal * 0.11 +  $shippingDetail['cost'],
            'order_date' => date('Y-m-d'),
            'order_code' => $orderCode,
            'notes' => $request['notes']
        ]);

        Cart::destroy($shippingCosts['data']['carts']->map(fn($cart) => $cart->id));

        return response()->json([
            'message' => 'Your order is created, is time to paid your bill',
            'data' => $order,
        ], 200);
    }
}
