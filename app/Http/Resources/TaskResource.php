<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'assigned_role'  => $this->assigned_role,
            'payload'        => $this->payload,
            'status'         => $this->status,
            'priority'       => $this->priority,
            'created_at'     => $this->created_at->toDateTimeString(),
            'resolved_at'    => $this->resolved_at?->toDateTimeString(),
            'creator'        => [
                'id'        => $this->creator?->id,
                'full_name' => trim("{$this->creator?->name} {$this->creator?->surname}"),
            ],
            'application'    => $this->when($this->application_id, [
                'id'     => $this->application?->id,
                'number' => $this->application?->number,
            ]),
        ];
    }
}
