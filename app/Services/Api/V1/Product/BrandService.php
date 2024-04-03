<?php

namespace App\Services\Api\V1\Product;

use App\Events\Api\V1\Product\Brand\BrandCreatedEvent;
use App\Events\Api\V1\Product\Brand\BrandDeletedEvent;
use App\Events\Api\V1\Product\Brand\BrandUpdatedEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\Product\StoreBrandRequest;
use App\Http\Requests\Api\V1\Product\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandService extends BaseService
{
    public static function list(Request $request)
    {
        $page = $request->page ?? 1;
        $model = new Brand();
        $data = $model->query();
        if (!empty($request->s)) {
            $page = 1;
            $data = $data->whereRaw("name like '%" . $request->s . "%' or description like '%" . $request->s . "%' or phone like '%" . $request->s . "%' or address like '%" . $request->s . "%'");
        }
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }

    public static function store(StoreBrandRequest $request)
    {
        /**
         * @var Brand $brand
         */
        $brand = Brand::create([
            'name' => $request->name,
            'description' => $request->description,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        event(new BrandCreatedEvent($brand));
        return $brand;
    }

    public static function update(UpdateBrandRequest $request, Brand $brand): Brand
    {
        if ($brand->update([
            'name' => $request->name,
            'description' => $request->description ?? $brand->description,
            'phone' => $request->phone ?? $brand->phone,
            'address' => $request->address ?? $brand->address,
        ]))
            event(new BrandUpdatedEvent($brand));

        return $brand;
    }

    public static function delete(Brand $brand): bool
    {
        if ($brand->delete()) {
            event(new BrandDeletedEvent($brand));
            return true;
        }
        return false;
    }
}
