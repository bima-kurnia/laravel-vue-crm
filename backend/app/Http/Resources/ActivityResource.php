<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Map internal model class names back to frontend-safe strings.
     */
    private const TYPE_LABELS = [
        Customer::class => 'customer',
        Deal::class     => 'deal',
    ];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'event'        => $this->event,
            'payload'      => $this->payload ?? [],
            'subject_type' => self::TYPE_LABELS[$this->subject_type] ?? $this->subject_type,
            'subject_id'   => $this->subject_id,
            'created_at'   => $this->created_at,

            // Actor — null if user was deleted
            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ] : null),

            // Subject — only present when ?with_subject=true
            'subject' => $this->whenLoaded('subject', fn() => $this->subject ? [
                'id'   => $this->subject->getKey(),
                'name' => $this->subject->name ?? $this->subject->title ?? null,
            ] : null),
        ];
        
        // tenant_id intentionally excluded
    }
}
