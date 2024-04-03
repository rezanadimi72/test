<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\V1\Customer\UpdateCustomerRequest;
use App\Http\Resources\Api\V1\Customer\CustomerResource;
use App\Http\Resources\Api\V1\Customer\ListCustomerResource;
use App\Models\Customer;
use App\Services\Api\V1\Customer\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ListCustomerResource::collection(CustomerService::list($request));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        return CustomerResource::collection(CustomerService::store($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return CustomerResource::collection($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        return CustomerResource::collection(CustomerService::update($request, $customer));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        CustomerService::delete($customer);
        return CustomerResource::collection($customer);
    }
}
