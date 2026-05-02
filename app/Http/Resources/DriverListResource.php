<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasExpiringDocs = $this->documents
            ->filter(fn($d) => $d->expires_at && $d->expires_at <= now()->addDays(30))
            ->isNotEmpty();

        $currentVehicle = $this->vehicles->first();

        return [
            'id'               => $this->id,
            'full_name'        => $this->full_name,
            'iin'              => $this->iin,
            'phone'            => $this->phone,
            'license_classes'  => $this->license_classes ? explode(',', $this->license_classes) : [],
            'status'           => $this->status,
            'docs_status'      => $hasExpiringDocs ? 'expiring' : 'valid',
            'documents'        => $this->documents->map(fn($d) => [
                'id'         => $d->id,
                'expires_at' => $d->expires_at?->toDateString(),
            ])->values(),
            'current_vehicle'  => $currentVehicle ? [
                'id'            => $currentVehicle->id,
                'tractor_brand' => $currentVehicle->tractor_brand,
                'tractor_plate' => $currentVehicle->tractor_plate,
            ] : null,
        ];
    }
}
