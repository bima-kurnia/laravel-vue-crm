<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ListActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Services\ActivityService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    public function __construct(private readonly ActivityService $activityService) {}

    /**
     * GET /api/activities
     * Global activity feed with filters.
     */
    public function index(ListActivityRequest $request): AnonymousResourceCollection
    {
        $activities = $this->activityService->list($request->validated());

        return ActivityResource::collection($activities);
    }

    /**
     * GET /api/activities/{id}
     * Single activity detail (always loads subject).
     */
    public function show(string $id): ActivityResource
    {
        $activity = $this->activityService->findOrFail($id);

        return new ActivityResource($activity);
    }

    /**
     * GET /api/activities/subject/{type}/{id}
     * Activity feed scoped to a single subject (customer or deal timeline).
     */
    public function subjectFeed(string $type, string $id): AnonymousResourceCollection
    {
        $activities = $this->activityService->subjectFeed($type, $id);

        return ActivityResource::collection($activities);
    }

    /**
     * GET /api/activities/deals/{id}/stage-history
     * Ordered stage transitions for a deal — pipeline timeline.
     */
    public function stageHistory(string $id): AnonymousResourceCollection
    {
        $activities = $this->activityService->stageHistory($id);

        return ActivityResource::collection($activities);
    }
}
