<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ActivityService
{
    /**
     * Map of frontend-safe subject type strings to fully qualified model classes.
     * Add new morph types here as the application grows.
     */
    private const SUBJECT_TYPE_MAP = [
        'customer' => Customer::class,
        'deal'     => Deal::class,
    ];

    // -------------------------------------------------------------------------
    // Write (called internally by other services — never from a controller)
    // -------------------------------------------------------------------------

    /**
     * Append an immutable audit entry.
     * Payload shape is open — callers decide what before/after data to include.
     */
    public function log(
        Model  $subject,
        string $event,
        array  $payload = []
    ): Activity {
        return Activity::create([
            'user_id'      => Auth::id(),
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'event'        => $event,
            'payload'      => $payload,
        ]);
    }

    /**
     * Produce a before/after diff of only the keys that actually changed.
     * Use this in CustomerService and DealService update methods to keep
     * payloads lean — only changed fields are stored.
     *
     * Usage:
     *   $payload = ActivityService::diff($before, $after);
     *   $this->activityService->log($model, 'updated', $payload);
     */
    public static function diff(array $before, array $after): array
    {
        $changedBefore = [];
        $changedAfter  = [];

        foreach ($after as $key => $newValue) {
            $oldValue = $before[$key] ?? null;

            // Normalise both sides to a comparable scalar string.
            // Arrays (e.g. custom_data) are JSON-encoded for comparison
            // so we never cast an array to string directly.
            $oldNorm = is_array($oldValue) ? json_encode($oldValue) : (string) ($oldValue ?? '');
            $newNorm = is_array($newValue) ? json_encode($newValue) : (string) ($newValue ?? '');

            if ($oldNorm !== $newNorm) {
                $changedBefore[$key] = $oldValue;
                $changedAfter[$key]  = $newValue;
            }
        }

        return [
            'before' => $changedBefore,
            'after'  => $changedAfter,
        ];
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /**
     * Paginated list of activities with optional filters.
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Activity::query()->with('user');

        // Filter by subject type + optional subject id
        if (! empty($filters['subject_type'])) {
            $modelClass = $this->resolveSubjectClass($filters['subject_type']);
            $query->where('subject_type', $modelClass);

            if (! empty($filters['subject_id'])) {
                $query->where('subject_id', $filters['subject_id']);
            }
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['events'])) {
            $query->whereIn('event', $filters['events']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Conditionally eager-load the subject (customer or deal)
        if (! empty($filters['with_subject'])) {
            $query->with('subject');
        }

        // Audit logs are always read newest-first
        $query->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 20;

        return $query->paginate($perPage);
    }

    /**
     * Single activity lookup.
     */
    public function findOrFail(string $id): Activity
    {
        return Activity::with(['user', 'subject'])->findOrFail($id);
    }

    /**
     * All stage transitions for a specific deal — used by pipeline history views.
     */
    public function stageHistory(string $dealId): LengthAwarePaginator
    {
        // Verify the deal belongs to this tenant via global scope
        $exists = Deal::where('id', $dealId)->exists();

        if (! $exists) {
            throw new ModelNotFoundException();
        }

        return Activity::query()
            ->with('user')
            ->where('subject_type', Deal::class)
            ->where('subject_id', $dealId)
            ->where('event', 'stage_changed')
            ->orderBy('created_at', 'asc') // chronological for timeline display
            ->paginate(50);
    }

    /**
     * Recent activity feed for a specific subject (customer or deal).
     * Useful for sidebar / detail page timelines.
     */
    public function subjectFeed(string $subjectType, string $subjectId): LengthAwarePaginator
    {
        $modelClass = $this->resolveSubjectClass($subjectType);

        return Activity::query()
            ->with('user')
            ->where('subject_type', $modelClass)
            ->where('subject_id', $subjectId)
            ->orderBy('created_at', 'desc')
            ->paginate(25);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve a frontend-safe subject type string to its model class.
     *
     * @throws ValidationException
     */
    private function resolveSubjectClass(string $type): string
    {
        $map = self::SUBJECT_TYPE_MAP;

        if (! array_key_exists($type, $map)) {
            throw ValidationException::withMessages([
                'subject_type' => 'Invalid subject type. Accepted: ' . implode(', ', array_keys($map)),
            ]);
        }

        return $map[$type];
    }
}