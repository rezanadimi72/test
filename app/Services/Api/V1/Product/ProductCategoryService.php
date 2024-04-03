<?php

namespace App\Services\Api\V1\Product;

use App\Events\Api\V1\Product\Brand\BrandCreatedEvent;
use App\Events\Api\V1\Product\Brand\BrandDeletedEvent;
use App\Events\Api\V1\Product\Brand\BrandUpdatedEvent;
use App\Events\Api\V1\Product\Category\ProductCategoryCreatedEvent;
use App\Events\Api\V1\Product\Category\ProductCategoryDeletedEvent;
use App\Events\Api\V1\Product\Category\ProductCategoryUpdatedEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\Product\StoreBrandRequest;
use App\Http\Requests\Api\V1\Product\StoreProductCategoryRequest;
use App\Http\Requests\Api\V1\Product\UpdateBrandRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductCategoryRequest;
use App\Models\Brand;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryService extends BaseService
{
    public static function list(Request $request)
    {
        $page = $request->page ?? 1;
        $model = new ProductCategory();
        $data = $model->query();
        if (!empty($request->s)) {
            $page = 1;
            $data = $data->whereRaw("title like '%" . $request->s . "%' or description like '%" . $request->s . "%' ");
        }
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }

    public static function store(StoreProductCategoryRequest $request)
    {
        /**
         * @var ProductCategory $model
         */
        $model = ProductCategory::create([
            'parent_id' => $request->parent_id ?? 0,
            'title' => $request->title,
            'description' => $request->description ?? ''
        ]);
        event(new ProductCategoryCreatedEvent($model));
        return $model;
    }

    public static function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory): ProductCategory
    {
        if ($productCategory->update([
            'parent_id' => $request->parent_id ?? $productCategory->parent_id,
            'title' => $request->title,
            'description' => $request->description ?? $productCategory->description
        ]))
            event(new ProductCategoryUpdatedEvent($productCategory));

        return $productCategory;
    }

    public static function delete(ProductCategory $productCategory): ProductCategory
    {
        if ($productCategory->delete())
            event(new ProductCategoryDeletedEvent($productCategory));
        return $productCategory;
    }
}
