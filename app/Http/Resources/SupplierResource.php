<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'bin_iin'      => $this->bin_iin,
            'type'         => $this->type,
            'contact_name' => $this->contact_name,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'comment'      => $this->comment,
            'status'       => $this->status,
            'created_at'   => $this->created_at->toDateTimeString(),
            'documents'    => $this->whenLoaded('documents', fn() =>
                $this->documents->map(fn($d) => [
                    'id'            => $d->id,
                    'file_path'     => $d->file_path,
                    'original_name' => $d->original_name,
                ])
            ),
            'vehicles'     => $this->whenLoaded('vehicles', fn() =>
                $this->vehicles->map(fn($v) => [
                    'id'            => $v->id,
                    'tractor_brand' => $v->tractor_brand,
                    'tractor_plate' => $v->tractor_plate,
                    'trailer_brand' => $v->trailer_brand,
                    'trailer_plate' => $v->trailer_plate,
                    'status'        => $v->status,
                ])
            ),
        ];
    }
}
