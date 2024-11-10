<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShippingAddressRequest;
use App\Http\Resources\ShippingAddressResource;
use App\Models\UserShippingAddress;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{

    public function getAddress(Request $request)
    {
        $shippingAddress = UserShippingAddress::where('user_id', $request->user()->id)->with('province', 'city')->get();
        return response(['message' => 'Address created successfully', 'data' =>  ShippingAddressResource::collection($shippingAddress)], 201);
    }

    public function create(CreateShippingAddressRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        if (UserShippingAddress::where('user_id', $user->id)->count() >= 3) {
            throw new HttpResponseException(response([
                'errors' => [
                    'error' => [
                        'The number of addresses has exceeded the limit'
                    ]
                ]
            ], 401));
        }

        $shippingAddress = new UserShippingAddress($data);
        $shippingAddress['user_id'] = $user->id;
        $shippingAddress->save();

        return response(['message' => 'Address created successfully', 'data' => new ShippingAddressResource($shippingAddress->load('province', 'city'))], 201);
    }
}
