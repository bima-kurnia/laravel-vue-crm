<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Deal\ListDealRequest;
use App\Http\Requests\Deal\StoreDealRequest;
use App\Http\Requests\Deal\UpdateDealRequest;
use App\Http\Requests\Deal\UpdateStageRequest;
use App\Http\Resources\DealResource;
use App\Services\DealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DealController extends Controller
{
    public function __construct(private readonly DealService $dealService) {}

    /**
     * GET /api/deals
     */
    public function index(ListDealRequest $request): AnonymousResourceCollection
    {
        $deals = $this->dealService->list($request->validated());

        return DealResource::collection($deals);
    }

    /**
     * GET /api/deals/pipeline
     */
    public function pipeline(): JsonResponse
    {
        $summary = $this->dealService->pipelineSummary();

        return response()->json(['data' => $summary]);
    }

    /**
     * POST /api/deals
     */
    public function store(StoreDealRequest $request): JsonResponse
    {
        $deal = $this->dealService->create($request->validated());

        return (new DealResource($deal))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/deals/{id}
     */
    public function show(string $id): DealResource
    {
        $deal = $this->dealService->findOrFail($id);

        return new DealResource($deal);
    }

    /**
     * PATCH /api/deals/{id}
     */
    public function update(UpdateDealRequest $request, string $id): DealResource
    {
        $deal = $this->dealService->update($id, $request->validated());

        return new DealResource($deal);
    }

    /**
     * PATCH /api/deals/{id}/stage
     */
    public function moveStage(UpdateStageRequest $request, string $id): DealResource
    {
        $deal = $this->dealService->moveStage($id, $request->validated('stage'));

        return new DealResource($deal);
    }

    /**
     * DELETE /api/deals/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $this->dealService->delete($id);

        return response()->json(['message' => 'Deal deleted.']);
    }

    /**
     * PATCH /api/deals/{id}/restore
     */
    public function restore(string $id): DealResource
    {
        $deal = $this->dealService->restore($id);

        return new DealResource($deal);
    }

    /**
     * DELETE /api/deals/{id}/force
     */
    public function forceDelete(string $id): JsonResponse
    {
        $this->dealService->forceDelete($id);

        return response()->json(['message' => 'Deal permanently deleted.']);
    }
}
