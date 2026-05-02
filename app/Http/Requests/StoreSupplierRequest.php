<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('suppliers.create');
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'status'       => ['nullable','string', 'in:active,pending,inactive'],
            'bin_iin'        => ['required', 'string', 'max:20'],
            'type'           => ['required', 'in:individual,legal'],
            'specialization' => ['required', 'string', 'max:255'],
            'contact_name'   => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:30'],
            'email'          => ['nullable', 'email'],
            'comment'        => ['nullable', 'string'],
            'documents'      => ['required', 'array', 'min:1'],
            'documents.*'    => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'vehicles'     => ['nullable', 'array'],
            'vehicles.*.tractor_brand' => ['nullable', 'string', 'max:255'],
            'vehicles.*.tractor_plate' => ['nullable', 'string', 'max:50'],
        ];
    }
}
