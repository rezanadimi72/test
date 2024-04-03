<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreBrandRequest;
use App\Http\Requests\Api\V1\Product\UpdateBrandRequest;
use App\Http\Resources\Api\V1\User\Product\Brand\ListBrandResource;
use App\Http\Resources\Api\V1\User\Product\Brand\BrandResource;
use App\Models\Brand;
use App\Services\Api\V1\Product\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Brand::class, 'brand');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ListBrandResource::collection(BrandService::list($request));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        return BrandResource::collection(BrandService::store($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return BrandResource::collection($brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        return BrandResource::collection(BrandService::update($request, $brand));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        BrandService::delete($brand);
        return $brand;
    }
}
