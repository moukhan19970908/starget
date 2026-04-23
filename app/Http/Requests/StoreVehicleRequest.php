<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vehicles.create');
    }

    public function rules(): array
    {
        return [
            'owner_id'        => ['required', 'exists:owners,id'],
            'vehicle_type_id' => ['required', 'exists:vehicle_types,id'],
            'tonnage'         => ['required', 'numeric', 'min:0'],
            'volume'          => ['required', 'numeric', 'min:0'],
            'tractor_brand'   => ['required', 'string', 'max:100'],
            'tractor_plate'   => ['required', 'string', 'max:20'],
            'trailer_brand'   => ['nullable', 'string', 'max:100'],
            'trailer_plate'   => ['nullable', 'string', 'max:20'],
            'temperature_min' => ['nullable', 'numeric'],
            'temperature_max' => ['nullable', 'numeric'],
            'driver_id'       => ['nullable', 'exists:drivers,id'],
        ];
    }
}
