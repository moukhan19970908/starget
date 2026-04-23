<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('contracts.create');
    }

    public function rules(): array
    {
        return [
            'number'      => ['required', 'string', 'unique:contracts,number'],
            'client_id'   => ['nullable', 'exists:clients,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'type'        => ['required', 'string', 'max:100'],
            'signed_date' => ['nullable', 'date'],
            'expires_at'  => ['nullable', 'date'],
            'status'      => ['in:active,draft,expiring,refused,completed'],
            'file'        => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ];
    }
}
