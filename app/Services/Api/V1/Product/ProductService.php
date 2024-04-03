<?php

namespace App\Services\Api\V1\Product;

use App\Events\Api\V1\Product\ProductCreatedEvent;
use App\Events\Api\V1\Product\ProductDeletedEvent;
use App\Events\Api\V1\Product\ProductImageAddedEvent;
use App\Events\Api\V1\Product\ProductUpdatedEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\Product\StoreProductImageRequest;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductService extends BaseService
{
    public static function list(Request $request)
    {
        $page = $request->page ?? 1;
        $model = new Product();
        /**
         * @var Builder $data
         */
        $data = $model->query()->with([
            'category',
            'brand'
        ]);
        if (!empty($request->s)) {
            $page = 1;
            $data = $data->whereRaw("title like '%" . $request->s . "%'");
        }
        if (!empty($request->product_category_id)) {
            $page = 1;
            $data = $data->where(['product_category_id' => $request->product_category_id]);
        }
        if (!empty($request->brand_id)) {
            $page = 1;
            $data = $data->where(['brand_id' => $request->brand_id]);
        }
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }

    public static function show(Product $product)
    {
        $temp = Product::query()->where(['id' => $product->id])->with(['category', 'brand'])->first();
        $product->category = $temp->category;
        $product->brand = $temp->brand;
        return $product;
    }

    public static function store(StoreProductRequest $request)
    {
        /**
         * @var Product $model
         */
        $model = Product::create([
            'product_category_id' => $request->product_category_id,
            'brand_id' => $request->brand_id,
            'title' => $request->title,
            'description' => $request->description ?? '',
            'detail' => $request->detail ?? [],
        ]);
        $model->images = $request->images;
        event(new ProductCreatedEvent($model));
        return $model;
    }

    public static function addImages(Product $product, StoreProductImageRequest $request)
    {

        $product->images = $request->images;
        event(new ProductImageAddedEvent($product));
        return $product;
    }

    public static function update(UpdateProductRequest $request, Product $product): Product
    {
        if ($product->update([
            'product_category_id' => $request->product_category_id,
            'brand_id' => $request->brand_id,
            'title' => $request->title,
            'description' => $request->description ?? $product->description,
            'detail' => $request->detail ?? $product->detail,
        ]))
            event(new ProductUpdatedEvent($product));

        return $product;
    }

    public static function delete(Product $product): Product
    {
        if ($product->delete())
            event(new ProductDeletedEvent($product));
        return $product;
    }
}
