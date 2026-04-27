<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'full_name'       => $this->full_name,
            'iin'             => $this->iin,
            'phone'           => $this->phone,
            'type'            => $this->type,
            'license_classes' => $this->license_classes ? explode(',', $this->license_classes) : [],
            'status'          => $this->status,
            'created_at'      => $this->created_at?->toDateString(),
            'documents'       => $this->whenLoaded('documents', fn() =>
                $this->documents->map(fn($d) => [
                    'id'            => $d->id,
                    'document_type' => $d->documentType?->name,
                    'number'        => $d->number,
                    'issued_date'   => $d->issued_date?->toDateString(),
                    'issued_by'     => $d->issued_by,
                    'expires_at'    => $d->expires_at?->toDateString(),
                    'file_path'     => $d->file_path,
                ])
            ),
            'vehicles'        => $this->whenLoaded('vehicles', fn() =>
                $this->vehicles->map(fn($v) => [
                    'id'            => $v->id,
                    'tractor_brand' => $v->tractor_brand,
                    'tractor_plate' => $v->tractor_plate,
                ])
            ),
        ];
    }
}
