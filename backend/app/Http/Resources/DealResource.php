<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'title'               => $this->title,
            'value'               => $this->value,
            'currency'            => $this->currency,
            'status'              => $this->status,
            'stage'               => $this->stage,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'custom_data'         => $this->custom_data ?? [],
            'deleted_at'          => $this->deleted_at,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,

            // Related models — flattened to safe sub-shapes, no tenant_id.
            // whenLoaded loads data when using "with" (eager load).
            // whenLoaded avoid unwanted lazy loading.
            'customer' => $this->whenLoaded('customer', fn() => [
                'id'      => $this->customer->id,
                'name'    => $this->customer->name,
                'company' => $this->customer->company,
            ]),

            'owner' => $this->whenLoaded('owner', fn() => [
                'id'   => $this->owner->id,
                'name' => $this->owner->name,
            ]),
        ];

        // tenant_id intentionally excluded
    }
}
