<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('applications.create');
    }

    public function rules(): array
    {
        return [
            'client_id'                  => ['required', 'exists:clients,id'],
            'contract_id'                => ['nullable', 'exists:contracts,id'],
            'departure_city_id'          => ['required', 'exists:cities,id'],
            'destination_city_id'        => ['required', 'exists:cities,id'],
            'shipper'                    => ['nullable', 'string', 'max:255'],
            'consignee'                  => ['nullable', 'string', 'max:255'],
            'departure_date'             => ['nullable', 'date'],
            'arrival_date'               => ['nullable', 'date', 'after_or_equal:departure_date'],
            'loading_address'            => ['nullable', 'string'],
            'unloading_address'          => ['nullable', 'string'],
            'contact_loading'            => ['nullable', 'string', 'max:50'],
            'contact_unloading'          => ['nullable', 'string', 'max:50'],
            'cargo_name'                 => ['nullable', 'string', 'max:255'],
            'loading_type_id'            => ['nullable', 'exists:loading_types,id'],
            'special_conditions'         => ['nullable', 'string'],
            'weight'                     => ['nullable', 'numeric', 'min:0'],
            'volume'                     => ['nullable', 'numeric', 'min:0'],
            'cargo_cost'                 => ['nullable', 'numeric', 'min:0'],
            'cargo_currency_id'          => ['nullable', 'exists:currencies,id'],
            'client_rate'                => ['nullable', 'numeric', 'min:0'],
            'client_rate_vat'            => ['boolean'],
            'client_rate_currency_id'    => ['nullable', 'exists:currencies,id'],
            'client_rate_exchange'       => ['nullable', 'numeric', 'min:0'],
            'comment'                    => ['nullable', 'string'],
            'stops'                      => ['nullable', 'array'],
            'stops.*.stop_type_id'       => ['required', 'exists:stop_types,id'],
            'stops.*.address'            => ['required', 'string'],
            'stops.*.sort_order'         => ['nullable', 'integer'],
            'stops.*.expected_at'        => ['nullable', 'date'],
        ];
    }
}
