<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'tractor_brand'  => $this->tractor_brand,
            'tractor_plate'  => $this->tractor_plate,
            'trailer_brand'  => $this->trailer_brand,
            'trailer_plate'  => $this->trailer_plate,
            'vehicle_type'   => $this->vehicleType?->name,
            'is_ref'         => $this->vehicleType?->is_ref,
            'tonnage'        => $this->tonnage,
            'owner'          => [
                'id'        => $this->owner?->id,
                'full_name' => $this->owner?->full_name,
            ],
            'status'         => $this->status,
        ];
    }
}
