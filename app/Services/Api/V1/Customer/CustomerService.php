<?php

namespace App\Services\Api\V1\Customer;

use App\Events\Api\V1\Customer\CustomerCreatedEvent;
use App\Events\Api\V1\Customer\CustomerUpdatedEvent;
use App\Events\Api\V1\Customer\CustomerDeletedEvent;
use App\Events\Api\V1\Product\Brand\BrandCreatedEvent;
use App\Events\Api\V1\Product\Brand\BrandDeletedEvent;
use App\Events\Api\V1\Product\Brand\BrandUpdatedEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\V1\Customer\UpdateCustomerRequest;
use App\Http\Requests\Api\V1\Product\StoreBrandRequest;
use App\Http\Requests\Api\V1\Product\UpdateBrandRequest;
use App\Models\Brand;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CustomerService extends BaseService
{
    public static function list(Request $request): LengthAwarePaginator
    {
        $page = $request->page ?? 1;
        $model = new Customer();
        $data = $model->query();
        if (!empty($request->s)) {
            $page = 1;
            $data = $data->whereRaw("name like '%" . $request->s . "%' or description like '%" . $request->s . "%' or phone like '%" . $request->s . "%' or address like '%" . $request->s . "%'");
        }
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }

    public static function store(StoreCustomerRequest $request)
    {
        /**
         * @var Customer $customer
         */
        $customer = Customer::create([
            'name' => $request->name,
            'description' => $request->description,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        event(new CustomerCreatedEvent($customer));
        return $customer;
    }

    public static function update(UpdateCustomerRequest $request, Customer $customer): Customer
    {
        if ($customer->update([
            'name' => $request->name,
            'description' => $request->description ?? $customer->description,
            'phone' => $request->phone ?? $customer->phone,
            'address' => $request->address ?? $customer->address,
        ]))
            event(new CustomerUpdatedEvent($customer));

        return $customer;
    }

    public static function delete(Customer $customer): bool
    {
        if ($customer->delete()) {
            event(new CustomerDeletedEvent($customer));
            return true;
        }
        return false;
    }
}
