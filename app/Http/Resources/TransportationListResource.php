<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'number'                 => $this->number,
            'status'                 => $this->status,
            'application_number'     => $this->application?->number,
            'route'                  => [
                'from' => $this->application?->departureCity?->name,
                'to'   => $this->application?->destinationCity?->name,
            ],
            'arrival_date'           => $this->application?->arrival_date?->toDateString(),
            'weight'                 => $this->application?->weight,
            'vehicle'                => $this->when($this->vehicle_id, [
                'tractor_brand' => $this->vehicle?->tractor_brand,
                'tractor_plate' => $this->vehicle?->tractor_plate,
                'trailer_brand' => $this->vehicle?->trailer_brand,
                'trailer_plate' => $this->vehicle?->trailer_plate,
            ]),
            'driver'                 => $this->when($this->driver_id, [
                'full_name' => $this->driver?->full_name,
                'iin'       => $this->driver?->iin,
            ]),
            'supplier_rate'          => $this->supplier_rate,
            'supplier_rate_currency' => $this->supplier_rate_currency,
            'created_at'             => $this->created_at?->toDateTimeString(),
        ];
    }
}
