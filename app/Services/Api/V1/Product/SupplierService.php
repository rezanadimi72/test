<?php

namespace App\Services\Api\V1\Product;

use App\Events\Api\V1\Product\Supplier\SupplierCreatedEvent;
use App\Events\Api\V1\Product\Supplier\SupplierDeletedEvent;
use App\Events\Api\V1\Product\Supplier\SupplierUpdatedEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\Product\StoreSupplierRequest;
use App\Http\Requests\Api\V1\Product\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SupplierService extends BaseService
{
    public static function list(Request $request)
    {
        $page = $request->page ?? 1;
        $model = new Supplier();
        $data = $model->query()
            ->select([$model->getTable() . '.*'])
            ->when(!empty($request->product_id), function (Builder $q) use ($model, $request) {
                $q->join((new SupplierProduct())->getTable() . ' as sp', 'sp.supplier_id', '=', $model->getTable() . '.id')->distinct($model->getTable() . '.id')->where(['sp.product_id' => $request->product_id]);
            });
        if (!empty($request->s)) {
            $page = 1;
            $data = $data->whereRaw("name like '%" . $request->s . "%' or description like '%" . $request->s . "%' or phone like '%" . $request->s . "%' or address like '%" . $request->s . "%'");
        }
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }


    public static function store(StoreSupplierRequest $request)
    {
        /**
         * @var Supplier $supplier
         */
        $supplier = Supplier::create([
            'name' => $request->name,
            'description' => $request->description,
            'phone' => $request->phone,
            'address' => $request->address,
            'register_by' => auth()->id(),
        ]);
        event(new SupplierCreatedEvent($supplier));
        return $supplier;
    }

    public static function update(UpdateSupplierRequest $request, Supplier $supplier): Supplier
    {
        if ($supplier->update([
            'name' => $request->name,
            'description' => $request->description ?? $supplier->description,
            'phone' => $request->phone ?? $supplier->phone,
            'address' => $request->address ?? $supplier->address,
        ]))
            event(new SupplierUpdatedEvent($supplier));

        return $supplier;
    }

    public static function delete(Supplier $supplier): bool
    {
        if ($supplier->delete()) {
            event(new SupplierDeletedEvent($supplier));
            return true;
        }
        return false;
    }
}
