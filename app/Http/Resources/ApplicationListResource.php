<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'number'       => $this->number,
            'created_at'   => $this->created_at->toDateTimeString(),
            'client'       => [
                'id'   => $this->client?->id,
                'name' => $this->client?->name,
            ],
            'route'        => [
                'from' => $this->departureCity?->name,
                'to'   => $this->destinationCity?->name,
            ],
            'status'       => $this->status,
            'author'       => [
                'id'        => $this->author?->id,
                'full_name' => trim("{$this->author?->name} {$this->author?->surname}"),
            ],
            'client_rate'  => $this->client_rate,
            'client_rate_currency' => $this->clientRateCurrency?->code,
        ];
    }
}
