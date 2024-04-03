<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Customer\Product\Request\StoreCustomerProductRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToAdminApprovalRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToAdminFinalInvoiceApprovalRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToCancelRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToCompleteRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToExpertAssignRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToExpertInvoiceApprovalRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToExpertSupplierAssignRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToProductDeliveryRequest;
use App\Http\Requests\Api\V1\Customer\UpdateCustomerRequest;
use App\Http\Resources\Api\V1\Customer\Product\Request\CustomerProductRequestResource;
use App\Http\Resources\Api\V1\Customer\Product\Request\ListCustomerProductRequestResource;
use App\Http\Resources\Api\V1\Customer\Product\Request\StageResource;
use App\Models\CustomerProductRequest;
use App\Services\Api\V1\Customer\CustomerProductRequestService;
use Illuminate\Http\Request;

class CustomerProductRequestController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(CustomerProductRequest::class, 'customerProductRequest');
    }

    protected function resourceAbilityMap(): array
    {
        return array_merge(parent::resourceAbilityMap(), [
            'toAdminApproval' => 'toAdminApproval',
            'toExpertAssign' => 'toExpertAssign',
            'toExpertSupplierAssign' => 'toExpertSupplierAssign',
            'toExpertInvoiceApproval' => 'toExpertInvoiceApproval',
            'toAdminFinalInvoiceApproval' => 'toAdminFinalInvoiceApproval',
            'toProductDelivery' => 'toProductDelivery',
            'toComplete' => 'toComplete',
            'toCancel' => 'toCancel',
            'show' => 'view',
        ]);
    }

    /**
     * Get the list of resource methods which do not have model parameters.
     *
     * @return array
     */
    protected function resourceMethodsWithoutModels()
    {
        return array_merge(parent::resourceMethodsWithoutModels(), [
            'toAdminApproval',
            'toExpertAssign',
            'toExpertSupplierAssign',
            'toExpertInvoiceApproval',
            'toAdminFinalInvoiceApproval',
            'toProductDelivery',
            'toComplete',
            'toCancel',
            'show',
        ]);
    }

    public function toAdminApproval(ToAdminApprovalRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toAdminApproval($request, $customerProductRequest));
    }

    public function toExpertAssign(ToExpertAssignRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toExpertAssign($request, $customerProductRequest));
    }

    public function toExpertSupplierAssign(ToExpertSupplierAssignRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toExpertSupplierAssign($request, $customerProductRequest));
    }

    public function toExpertInvoiceApproval(ToExpertInvoiceApprovalRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toExpertInvoiceApproval($request, $customerProductRequest));
    }

    public function toAdminFinalInvoiceApproval(ToAdminFinalInvoiceApprovalRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toAdminFinalInvoiceApproval($request, $customerProductRequest));
    }

    public function toProductDelivery(ToProductDeliveryRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toProductDelivery($request, $customerProductRequest));
    }

    public function toComplete(ToCompleteRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toComplete($request, $customerProductRequest));
    }

    public function toCancel(ToCancelRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::toCancel($request, $customerProductRequest));
    }

    public function stage_list(Request $request)
    {
        return StageResource::collection(CustomerProductRequestService::stage_list($request));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ListCustomerProductRequestResource::collection(CustomerProductRequestService::list($request));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerProductRequest $request)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::store($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection($customerProductRequest);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, CustomerProductRequest $customerProductRequest)
    {
        return CustomerProductRequestResource::collection(CustomerProductRequestService::update($request, $customerProductRequest));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerProductRequest $customerProductRequest)
    {
        CustomerProductRequestService::delete($customerProductRequest);
        return CustomerProductRequestResource::collection($customerProductRequest);
    }
}
