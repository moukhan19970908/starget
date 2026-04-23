<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                          => $this->id,
            'number'                      => $this->number,
            'status'                      => $this->status,
            'created_at'                  => $this->created_at->toDateTimeString(),
            'updated_at'                  => $this->updated_at->toDateTimeString(),
            'client'                      => new ClientResource($this->whenLoaded('client')),
            'contract'                    => $this->when($this->contract_id, [
                'id'     => $this->contract?->id,
                'number' => $this->contract?->number,
            ]),
            'departure_city'              => $this->departureCity?->name,
            'destination_city'            => $this->destinationCity?->name,
            'shipper'                     => $this->shipper,
            'consignee'                   => $this->consignee,
            'departure_date'              => $this->departure_date?->toDateString(),
            'arrival_date'                => $this->arrival_date?->toDateString(),
            'loading_address'             => $this->loading_address,
            'unloading_address'           => $this->unloading_address,
            'contact_loading'             => $this->contact_loading,
            'contact_unloading'           => $this->contact_unloading,
            'cargo_name'                  => $this->cargo_name,
            'loading_type'                => $this->loadingType?->name,
            'special_conditions'          => $this->special_conditions,
            'weight'                      => $this->weight,
            'volume'                      => $this->volume,
            'cargo_cost'                  => $this->cargo_cost,
            'cargo_currency'              => $this->cargoCurrency?->code,
            'client_rate'                 => $this->client_rate,
            'client_rate_vat'             => $this->client_rate_vat,
            'client_rate_currency'        => $this->clientRateCurrency?->code,
            'client_rate_exchange'        => $this->client_rate_exchange,
            'comment'                     => $this->comment,
            'refusal_reason'              => $this->refusalReason?->name,
            'refusal_comment'             => $this->refusal_comment,
            'planned_transportations_count' => $this->planned_transportations_count,
            'created_transportations_count' => $this->transportations()->count(),
            'author'                      => new UserResource($this->whenLoaded('author')),
            'stops'                       => ApplicationStopResource::collection($this->whenLoaded('stops')),
            'transportations'             => TransportationListResource::collection($this->whenLoaded('transportations')),
        ];
    }
}
