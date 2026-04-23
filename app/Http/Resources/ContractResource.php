<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'number'      => $this->number,
            'client'      => new ClientResource($this->whenLoaded('client')),
            'supplier'    => new SupplierResource($this->whenLoaded('supplier')),
            'type'        => $this->type,
            'signed_date' => $this->signed_date?->toDateString(),
            'expires_at'  => $this->expires_at?->toDateString(),
            'file_path'   => $this->file_path,
            'status'      => $this->status,
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}
