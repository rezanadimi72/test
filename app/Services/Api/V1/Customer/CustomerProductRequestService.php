<?php

namespace App\Services\Api\V1\Customer;

use App\Enums\CustomerProductRequestStageEnum;
use App\Events\Api\V1\Customer\Product\Request\CPRCreatedEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToAdminApprovalEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToAdminFinalInvoiceApprovalEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToCancelEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToCompleteEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToExpertAssignedEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToInvoiceApprovalEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToProductDeliveryEvent;
use App\Events\Api\V1\Customer\Product\Request\StageToSupplierAssignedEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\Customer\Product\Request\StoreCustomerProductRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToAdminApprovalRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToAdminFinalInvoiceApprovalRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToCancelRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToCompleteRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToExpertAssignRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToExpertInvoiceApprovalRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToExpertSupplierAssignRequest;
use App\Http\Requests\Api\V1\Customer\Product\Request\ToProductDeliveryRequest;
use App\Models\Customer;
use App\Models\CustomerProductRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CustomerProductRequestService extends BaseService
{
    public static function stage_list(Request $request)
    {
        $stage = [];
        foreach (collect(CustomerProductRequestStageEnum::cases())->pluck('value')->toArray() as $item) {
            $stage[] = [
                'id' => $item,
                'label' => CustomerProductRequestStageEnum::from($item)->label()
            ];
        }
        return $stage;
    }

    public static function list(Request $request): LengthAwarePaginator
    {
        $page = $request->page ?? 1;
        $user = new User();
        $model = new CustomerProductRequest();
        $data = $model->query()
            ->with([
                'registrar',
                'expert',
                'product',
                'customer',
                'supplier',
            ])
            ->when(!empty($request->registrar_user_id), function ($q) use ($request) {
                return $q->where(['registrar_user_id' => $request->registrar_user_id]);
            })
            ->when(!empty($request->product_id), function ($q) use ($request) {
                return $q->where(['product_id' => $request->product_id]);
            })
            ->when(!empty($request->customer_id), function ($q) use ($request) {
                return $q->where(['customer_id' => $request->customer_id]);
            })
            ->when(!empty($request->expert_id), function ($q) use ($request) {
                return $q->where(['expert_id' => $request->expert_id]);
            })
            ->when(!empty($request->supplier_id), function ($q) use ($request) {
                return $q->where(['supplier_id' => $request->supplier_id]);
            })
            ->when(!empty($request->product_count), function ($q) use ($request) {
                return $q->where(['product_count' => $request->product_count]);
            })
            ->when(!empty($request->fee), function ($q) use ($request) {
                return $q->where(['fee' => $request->fee]);
            })
            ->when(!empty($request->profit_percentage), function ($q) use ($request) {
                return $q->where(['profit_percentage' => $request->profit_percentage]);
            })
            ->when(!empty($request->stage), function ($q) use ($request) {
                return $q->where(['stage' => $request->stage]);
            });
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }

    public static function delete(CustomerProductRequest $customerProductRequest): bool
    {
        if ($customerProductRequest->delete()) {
            //event(new CustomerDeletedEvent($customerProductRequest));
            return true;
        }
        return false;
    }

    public static function store(StoreCustomerProductRequest $request): CustomerProductRequest
    {
        /**
         * @var CustomerProductRequest $customerProductRequest
         */
        $customerProductRequest = CustomerProductRequest::create([
            'product_id' => $request->product_id,
            'customer_id' => $request->customer_id,
            'product_count' => $request->product_count,
            'description' => $request->description ?? "",
            'registrar_user_id' => auth()->id(),
            'stage' => CustomerProductRequestStageEnum::REGISTER->value,
        ]);
        event(new CPRCreatedEvent($customerProductRequest));
        return $customerProductRequest;
    }

    public static function toAdminApproval(ToAdminApprovalRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::ADMIN_APPROVAL->value;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToAdminApprovalEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

    public static function toExpertAssign(ToExpertAssignRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::EXPERT_ASSIGNED->value;
        $customerProductRequest->expert_id = $request->expert->id;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToExpertAssignedEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

    public static function toExpertSupplierAssign(ToExpertSupplierAssignRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::EXPERT_SUPPLIER_ASSIGNED->value;
        $customerProductRequest->supplier_id = $request->supplier->id;
        $customerProductRequest->fee = $request->fee;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToSupplierAssignedEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

    public static function toExpertInvoiceApproval(ToExpertInvoiceApprovalRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::EXPERT_INVOICE_APPROVAL->value;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToInvoiceApprovalEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

    public static function toAdminFinalInvoiceApproval(ToAdminFinalInvoiceApprovalRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::ADMIN_FINAL_INVOICE_APPROVAL->value;
        $customerProductRequest->profit_percentage = $request->profit_percentage;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToAdminFinalInvoiceApprovalEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

    public static function toProductDelivery(ToProductDeliveryRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::PRODUCT_DELIVERY->value;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToProductDeliveryEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

    public static function toComplete(ToCompleteRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::COMPLETED->value;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToCompleteEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

    public static function toCancel(ToCancelRequest $request, CustomerProductRequest $customerProductRequest): CustomerProductRequest
    {
        $old_stage = $customerProductRequest->stage;
        $customerProductRequest->stage = CustomerProductRequestStageEnum::CANCELED->value;
        if (!$customerProductRequest->save())
            abort(400, 'خطا در انجام عملیات');

        event(new StageToCancelEvent($request, $customerProductRequest, $old_stage));
        return $customerProductRequest;
    }

}
