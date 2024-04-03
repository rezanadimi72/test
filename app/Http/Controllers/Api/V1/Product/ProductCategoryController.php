<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductCategoryRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductCategoryRequest;
use App\Http\Resources\Api\V1\User\Product\Category\CategoryResource;
use App\Http\Resources\Api\V1\User\Product\Category\ListCategoryResource;
use App\Models\ProductCategory;
use App\Services\Api\V1\Product\ProductCategoryService;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductCategory::class, 'category');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ListCategoryResource::collection(ProductCategoryService::list($request));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        return CategoryResource::collection(ProductCategoryService::store($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        return CategoryResource::collection($productCategory);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        return CategoryResource::collection(ProductCategoryService::update($request, $productCategory));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        ProductCategoryService::delete($productCategory);
        return $productCategory;

    }
}
