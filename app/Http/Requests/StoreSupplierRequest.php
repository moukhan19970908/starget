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
            'bin_iin'      => ['nullable', 'string', 'max:20'],
            'type'         => ['required', 'in:individual,legal'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email'],
            'comment'      => ['nullable', 'string'],
            'documents'    => ['nullable', 'array'],
            'documents.*'  => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
