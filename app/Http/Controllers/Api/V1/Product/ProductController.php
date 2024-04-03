<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Http\Resources\Api\V1\User\Product\ListProductResource;
use App\Http\Resources\Api\V1\User\Product\ProductResource;
use App\Models\Product;
use App\Services\Api\V1\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ListProductResource::collection(ProductService::list($request));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        return ProductResource::collection(ProductService::store($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return ProductResource::collection($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        return ProductResource::collection(ProductService::update($request, $product));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        ProductService::delete($product);
        return $product;
    }
}
