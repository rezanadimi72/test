<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Resources\Api\V1\User\Product\Supplier\SupplierResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductSupplierRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductSupplierRequest;
use App\Http\Resources\Api\V1\User\Product\SupplierProduct\ListSupplierProductResource;
use App\Http\Resources\Api\V1\User\Product\SupplierProduct\SupplierProductResource;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\Api\V1\Product\SupplierProductService;


class SupplierProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SupplierProduct::class, 'supplierProduct', [
            'except' => [
                'update',
                'destroy'
            ]
        ]);
    }

    public function index(Supplier $supplier)
    {
        return ListSupplierProductResource::collection(SupplierProductService::list($supplier));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Supplier $supplier, StoreProductSupplierRequest $request)
    {
        return SupplierProductResource::collection(SupplierProductService::store($request));
    }

    public function update(Supplier $supplier, Product $product, UpdateProductSupplierRequest $request)
    {
        return SupplierProductResource::collection(SupplierProductService::update($request));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier, Product $product, Request $request)
    {
        if (SupplierProductService::delete($request))
            return SupplierResource::collection($request->supplier);
        abort(400, 'خطا در حذف محصول تامین کننده');
    }
}
