<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreSupplierRequest;
use App\Http\Requests\Api\V1\Product\UpdateSupplierRequest;

use App\Http\Resources\Api\V1\User\Product\Supplier\ListSupplierResource;
use App\Http\Resources\Api\V1\User\Product\Supplier\SupplierResource;
use App\Models\Supplier;
use App\Services\Api\V1\Product\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Supplier::class, 'supplier');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ListSupplierResource::collection(SupplierService::list($request));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        return SupplierResource::collection(SupplierService::store($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return SupplierResource::collection($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        return SupplierResource::collection(SupplierService::update($request, $supplier));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        SupplierService::delete($supplier);
        return $supplier;
    }
}
