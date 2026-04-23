<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasExpiringDocs = $this->documents()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30))
            ->exists();

        $currentVehicle = $this->vehicles()->first();

        return [
            'id'               => $this->id,
            'full_name'        => $this->full_name,
            'iin'              => $this->iin,
            'phone'            => $this->phone,
            'license_classes'  => $this->license_classes,
            'status'           => $this->status,
            'docs_status'      => $hasExpiringDocs ? 'expiring' : 'valid',
            'current_vehicle'  => $currentVehicle ? [
                'id'            => $currentVehicle->id,
                'tractor_brand' => $currentVehicle->tractor_brand,
                'tractor_plate' => $currentVehicle->tractor_plate,
            ] : null,
        ];
    }
}
