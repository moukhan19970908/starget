<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'bin_iin'              => $this->bin_iin,
            'type'                 => $this->type,
            'contact_name'         => $this->contact_name,
            'phone'                => $this->phone,
            'email'                => $this->email,
            'status'               => $this->status,
            'transportations_count' => $this->transportations()->count(),
        ];
    }
}
