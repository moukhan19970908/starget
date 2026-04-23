<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'number'                 => $this->number,
            'status'                 => $this->status,
            'organization'           => $this->organization,
            'vat_kz'                 => $this->vat_kz,
            'application'            => new ApplicationResource($this->whenLoaded('application')),
            'client_manager'         => new UserResource($this->whenLoaded('clientManager')),
            'logistic_manager'       => new UserResource($this->whenLoaded('logisticManager')),
            'client_contract'        => $this->when($this->client_contract_id, [
                'id'     => $this->clientContract?->id,
                'number' => $this->clientContract?->number,
            ]),
            'vehicle'                => new VehicleResource($this->whenLoaded('vehicle')),
            'driver'                 => new DriverResource($this->whenLoaded('driver')),
            'supplier'               => new SupplierResource($this->whenLoaded('supplier')),
            'supplier_contract'      => $this->when($this->supplier_contract_id, [
                'id'     => $this->supplierContract?->id,
                'number' => $this->supplierContract?->number,
            ]),
            'supplier_delay_days'    => $this->supplier_delay_days,
            'supplier_rate'          => $this->supplier_rate,
            'supplier_rate_currency' => $this->supplier_rate_currency,
            'call_photo_path'        => $this->call_photo_path,
            'documents'              => $this->whenLoaded('documents', fn() =>
                $this->documents->map(fn($d) => [
                    'id'            => $d->id,
                    'type'          => $d->type,
                    'file_path'     => $d->file_path,
                    'original_name' => $d->original_name,
                ])
            ),
            'created_at'             => $this->created_at->toDateTimeString(),
        ];
    }
}
