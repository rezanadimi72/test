<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Events\Api\V1\User\PeoplePermission\PeopleDeletePermissionEvent;
use App\Events\Api\V1\User\PeoplePermission\PeopleSetPermissionEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductImageRequest;
use App\Http\Resources\Api\V1\User\PeoplePermission\PeoplePermissionResource;
use App\Http\Resources\Api\V1\User\Product\ListProductImageResource;
use App\Http\Resources\Api\V1\User\Product\ProductResource;
use App\Models\File;
use App\Models\People;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use App\Services\Api\V1\Product\ProductService;
use Illuminate\Auth\Access\Response;

class ProductImageController extends Controller
{
    public function index(Product $product)
    {
        $this->authorize('showProductImages', auth()->user());
        return ListProductImageResource::collection($product->images);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Product $product, StoreProductImageRequest $request)
    {
        $this->authorize('addProductImages', auth()->user());
        return ListProductImageResource::collection(ProductService::addImages($product, $request)->images);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, File $file)
    {
        $this->authorize('deleteProductImages', auth()->user());
        if ($file->relation_id != $product->id)
            abort(403, 'این تصویر مربوط به این محصول نمی باشد');
        if (File::delete_file($file))
            return ListProductImageResource::collection($product->images);
        abort(400, 'خطا در حذف تصویر محصول');
    }
}
