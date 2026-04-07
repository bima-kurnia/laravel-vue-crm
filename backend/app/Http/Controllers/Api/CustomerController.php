<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomerRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    /**
     * GET /api/customers
     */
    public function index(ListCustomerRequest $request): AnonymousResourceCollection
    {
        $customers = $this->customerService->list($request->validated());

        return CustomerResource::collection($customers);
    }

    /**
     * POST /api/customers
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/customers/{id}
     */
    public function show(string $id): CustomerResource
    {
        $customer = $this->customerService->findOrFail($id);

        return new CustomerResource($customer);
    }

    /**
     * PATCH /api/customers/{id}
     */
    public function update(UpdateCustomerRequest $request, string $id): CustomerResource
    {
        $customer = $this->customerService->update($id, $request->validated());

        return new CustomerResource($customer);
    }

    /**
     * DELETE /api/customers/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $this->customerService->delete($id);

        return response()->json(['message' => 'Customer deleted.']);
    }

    /**
     * PATCH /api/customers/{id}/restore
     */
    public function restore(string $id): CustomerResource
    {
        $customer = $this->customerService->restore($id);

        return new CustomerResource($customer);
    }

    /**
     * DELETE /api/customers/{id}/force
     */
    public function forceDelete(string $id): JsonResponse
    {
        $this->customerService->forceDelete($id);

        return response()->json(['message' => 'Customer permanently deleted.']);
    }
}
