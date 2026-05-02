<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'tractor_brand'   => $this->tractor_brand,
            'tractor_plate'   => $this->tractor_plate,
            'tractor_year'    => $this->tractor_year,
            'trailer_brand'   => $this->trailer_brand,
            'trailer_plate'   => $this->trailer_plate,
            'trailer_year'    => $this->trailer_year,
            'vehicle_type'    => [
                'id'     => $this->vehicleType?->id,
                'name'   => $this->vehicleType?->name,
                'is_ref' => $this->vehicleType?->is_ref,
            ],
            'tonnage'         => $this->tonnage,
            'volume'          => $this->volume,
            'temperature_min' => $this->temperature_min,
            'temperature_max' => $this->temperature_max,
            'status'          => $this->status,
            'owner'           => [
                'id'        => $this->owner?->id,
                'full_name' => $this->owner?->full_name,
                'type'      => $this->owner?->type,
            ],
            'drivers'         => $this->whenLoaded('drivers', fn() =>
                $this->drivers->map(fn($d) => [
                    'id'        => $d->id,
                    'full_name' => $d->full_name,
                    'iin'       => $d->iin,
                ])
            ),
            'suppliers'       => $this->whenLoaded('suppliers', fn() =>
                $this->suppliers->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name,
                ])
            ),
        ];
    }
}
