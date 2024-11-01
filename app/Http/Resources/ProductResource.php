<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            // 'image' => $this->categories->map(fn($category) => $category->featuredImage),
            'categories' => $this->categories ? $this->categories->map(function ($category) {
                return [
                    'name' => $category->title,
                    'slug' => $category->slug,
                    'featured_image' => $category->featuredImage ?
                        new MediaResource($category->featuredImage)
                        : null
                ];
            }) : 'Uncategorized',
            'description' => $this->description,
            'weight' => $this->weight,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'stock' => $this->stock,
            'images' => MediaResource::collection($this->productPictures),
            'created_at' => [
                "date_time" => $this->created_at,
                "humanize" => $this->created_at->diffForHumans(),
            ]
        ];
    }
}
