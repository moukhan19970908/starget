<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('drivers.create');
    }

    public function rules(): array
    {
        return [
            'full_name'               => ['required', 'string', 'max:255'],
            'iin'                     => ['nullable', 'string', 'max:20'],
            'type'                    => ['required', 'in:individual,legal'],
            'phone'                   => ['nullable', 'string', 'max:30'],
            'license_classes'         => ['nullable', 'string', 'max:50'],
            'is_owner'                => ['boolean'],
            'documents'               => ['nullable', 'array'],
            'documents.*.document_type_id' => ['required', 'exists:document_types,id'],
            'documents.*.number'      => ['required', 'string'],
            'documents.*.issued_date' => ['required', 'date'],
            'documents.*.issued_by'   => ['required', 'string'],
            'documents.*.expires_at'  => ['nullable', 'date'],
            'files'                   => ['nullable', 'array'],
            'files.*'                 => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
