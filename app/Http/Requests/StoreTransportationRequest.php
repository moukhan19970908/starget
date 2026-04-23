<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransportationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transportations.create');
    }

    public function rules(): array
    {
        return [
            'vat_kz'                  => ['boolean'],
            'vehicle_id'              => ['nullable', 'exists:vehicles,id'],
            'vehicle_type_id'         => ['nullable', 'exists:vehicle_types,id'],
            'temperature_min'         => ['nullable', 'numeric'],
            'temperature_max'         => ['nullable', 'numeric'],
            'driver_id'               => ['nullable', 'exists:drivers,id'],
            'supplier_id'             => ['nullable', 'exists:suppliers,id'],
            'supplier_contract_id'    => ['nullable', 'exists:contracts,id'],
            'supplier_delay_days'     => ['nullable', 'integer', 'min:0'],
            'supplier_rate'           => ['nullable', 'numeric', 'min:0'],
            'supplier_rate_currency'  => ['nullable', 'in:KZT,USD'],
        ];
    }
}
