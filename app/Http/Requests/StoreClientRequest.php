<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('clients.create');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'bin_iin'        => ['nullable', 'string', 'max:20'],
            'type'           => ['required', 'in:corporate,supplier,archive'],
            'contact_name'   => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email'],
            'legal_address'  => ['nullable', 'string'],
            'actual_address' => ['nullable', 'string'],
            'status'         => ['in:active,inactive'],
        ];
    }
}
