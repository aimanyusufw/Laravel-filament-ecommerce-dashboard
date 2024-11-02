<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getAllProduct(GetProductRequest $request)
    {
        $params = $request->validated();


        $data = Product::with('categories.featuredImage', 'productPictures')
            ->when($params['query'] ?? null, function ($query, $search) {
                return $query->where('title', 'LIKE', '%' . $search . '%');
            })
            ->paginate($params['per_page'] ?? 10);



        if ($data[0]  === null) {
            throw new HttpResponseException(response(['message' => 'Product is not found'], 404));
        }

        return response()->json(['message' => 'Get products successfully', 'data' => ProductResource::collection($data)], 200);
    }

    public function showProduct(Product $product)
    {
        $product->load('categories.featuredImage', 'productPictures');
        return response()->json(['message' => 'Get product successfully', 'data' => new ProductResource($product)]);
    }
}
