<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'number'      => $this->number,
            'client'      => $this->when($this->client_id, [
                'id'   => $this->client?->id,
                'name' => $this->client?->name,
            ]),
            'supplier'    => $this->when($this->supplier_id, [
                'id'   => $this->supplier?->id,
                'name' => $this->supplier?->name,
            ]),
            'type'        => $this->type,
            'signed_date' => $this->signed_date?->toDateString(),
            'expires_at'  => $this->expires_at?->toDateString(),
            'status'      => $this->status,
        ];
    }
}
