<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RegisterTenantRequest;
use App\Http\Resources\UserResource;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    public function __construct(private readonly TenantService $tenantService) {}

    /**
     * POST /api/register
     * Public — no auth required.
     */
    public function register(RegisterTenantRequest $request): JsonResponse
    {
        $result = $this->tenantService->register($request->validated());

        return response()->json([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 201);
    }
}
