<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationStopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'stop_type'   => $this->stopType?->name,
            'stop_type_id' => $this->stop_type_id,
            'address'     => $this->address,
            'sort_order'  => $this->sort_order,
            'expected_at' => $this->expected_at?->toDateTimeString(),
        ];
    }
}
