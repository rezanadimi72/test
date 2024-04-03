<?php

namespace App\Services\Api\V1\Product;

use App\Events\Api\V1\Product\ProductSupplier\SupplierProductCreatedEvent;
use App\Events\Api\V1\Product\ProductSupplier\SupplierProductDeletedEvent;
use App\Events\Api\V1\Product\ProductSupplier\SupplierProductUpdatedEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\Product\StoreProductSupplierRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductSupplierRequest;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;

class SupplierProductService extends BaseService
{
    public static function list(Supplier $supplier)
    {
        $request = \request();
        $page = $request->page ?? 1;
        $model = new SupplierProduct();
        $data = $model->query()->where(['supplier_id' => $supplier->id])->with([
            'supplier',
            'product'
        ]);
        /*   if (!empty($request->s)) {
               $page = 1;
               $data = $data->whereRaw("name like '%" . $request->s . "%' or description like '%" . $request->s . "%' or phone like '%" . $request->s . "%' or address like '%" . $request->s . "%'");
           }*/
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }


    public static function store(StoreProductSupplierRequest $request): SupplierProduct
    {
        /**
         * @var SupplierProduct $model
         */
        $model = SupplierProduct::create([
            'product_id' => $request->product_id,
            'supplier_id' => $request->supplier->id,
            'price' => $request->price,
        ]);
        event(new SupplierProductCreatedEvent($model));
        return $model;
    }

    public static function update(UpdateProductSupplierRequest $request): SupplierProduct
    {
        /**
         * @var $supplierProduct SupplierProduct
         */
        $supplierProduct = SupplierProduct::query()->where(['supplier_id' => $request->supplier->id, 'product_id' => $request->product->id])->first();
        if (empty($supplierProduct))
            abort(400, 'خطا در ویرایش محصول تامین کننده');
        if ($supplierProduct->update([
            'price' => $request->price,
        ]))
            event(new SupplierProductUpdatedEvent($supplierProduct, $request));

        return $supplierProduct;
    }

    public static function delete(Request $request): bool
    {
        /**
         * @var $supplierProduct SupplierProduct
         */
        $supplierProduct = SupplierProduct::query()->where(['supplier_id' => $request->supplier->id, 'product_id' => $request->product->id])->first();

        if ($supplierProduct && $supplierProduct->forceDelete()) {
            event(new SupplierProductDeletedEvent($request->supplier, $request->product));
            return true;
        }
        return false;
    }
}
