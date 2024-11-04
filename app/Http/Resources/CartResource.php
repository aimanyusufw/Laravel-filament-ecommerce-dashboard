<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->product->load('categories.featuredImage', 'productPictures')),
            'quantity' => $this->quantity,
            'sub_total' => ($this->product->price ?? $this->product->sale_price) * $this->quantity
        ];
    }
}
