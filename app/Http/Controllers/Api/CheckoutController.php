<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Http\Resources\ShippingAddressResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserShippingAddress;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Midtrans\Config;
use Midtrans\Snap;

// Set your Merchant Server Key
Config::$serverKey = env('MIDTRANSE_SERVER_KEY');
// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
Config::$isProduction = false;
// Set sanitization on (default)
Config::$isSanitized = true;
// Set 3DS transaction for credit card to true
Config::$is3ds = true;

Config::$overrideNotifUrl = env('MIDTRANSE_NOTIF_URL');

class CheckoutController extends Controller
{
    public function calculateCost(Request $request)
    {
        $data = $request->validate([
            'user_shipping_addresses_id' => 'required|exists:user_shipping_addresses,id'
        ]);

        $address = UserShippingAddress::with('province', 'city')->findOrFail($data['user_shipping_addresses_id']);

        $carts = Cart::with('product.productPictures')->where('user_id', $request->user()->id)->get();
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
                'carts' => CartResource::collection($carts),
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

        $order->orderItmes()->createMany($shippingCosts['data']['carts']->map(function ($cart) {
            return [
                "product_title" => $cart->product->title,
                "product_price" => $cart->product->sale_price ?? $cart->product->price,
                "product_thumbnail" => $cart->product->productPictures[0]->thumbnail_url,
                "qty" => $cart->quantity,
                "sub_total" => ($cart->product->sale_price ?? $cart->product->price) * $cart->quantity
            ];
        }));

        Cart::destroy($shippingCosts['data']['carts']->map(fn($cart) => $cart->id));

        return response()->json([
            'message' => 'Your order is created, is time to paid your bill',
            'data' => $order,
            'payment' => [
                "url" => $this->midtransPaymentUrl($order)
            ]
        ], 200);
    }

    public function midtransPaymentUrl(Order $order)
    {

        $transaction_details = [
            "order_id" => $order->order_code,
            "gross_amount" => $order->grand_total
        ];

        $items = [
            [
                "id" => "TAX",
                "price" => $order->tax,
                "quantity" => 1,
                "name" => "Pajak PPN"
            ],
            [
                "id" => "Shipping cost",
                "price" => $order->shipping_cost,
                "quantity" => 1,
                "name" => "Biaya Pengiriman"
            ]
        ];
        foreach ($order->orderItmes as $item) {
            $items[] = [
                "id" => "PROD" . $item->id . $item->created_at,
                'price'    => $item->product_sale_price ?? $item->product_price,
                'quantity' => $item->qty,
                'name'     => $item->product_title
            ];
        }

        $billing_address = array(
            'first_name'   => $order->user->userDetail->billing_name,
            'last_name'    => null,
            'address'      => $order->user->userDetail->billing_address,
            'city'         => $order->user->userDetail->city->city_name,
            'postal_code'  => null,
            'phone'        => $order->user->userDetail->phone,
            'country_code' => 'IDN'
        );

        $orderShippingAddres = $order->shipping_address_detail;
        $shipping_address = array(
            'first_name'   =>  $orderShippingAddres["shipping_name"],
            'last_name'    => null,
            'address'      => $orderShippingAddres["title"],
            'city'         =>  $orderShippingAddres["shipping_city"]["type"] . " " . $orderShippingAddres["shipping_city"]["name"],
            'postal_code'  => null,
            'phone'        =>  $orderShippingAddres["shipping_phone"],
            'country_code' => 'IDN'
        );

        $params = [
            "transaction_details" =>  $transaction_details,
            "item_details"        => $items,
            "customer_details"    => [
                "billing_address"  => $billing_address,
                "shipping_address" => $shipping_address,
            ]
        ];

        $paymentUrl = \Midtrans\Snap::getSnapUrl($params);

        return $paymentUrl;
    }
}
