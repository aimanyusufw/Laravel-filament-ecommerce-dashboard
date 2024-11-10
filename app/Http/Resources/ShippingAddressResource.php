<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'shipping_name' => $this->shipping_name,
            'shipping_phone' => $this->shipping_phone,
            'shipping_email' => $this->shipping_email,
            'shipping_address' => $this->shipping_address,
            'shipping_province' => [
                "id" => $this->province->id,
                "name" => $this->province->province_name
            ],
            'shipping_city' => [
                "id" => $this->city->id,
                "name" => $this->city->city_name,
                "type" => $this->city->type,
            ],
        ];
    }
}
