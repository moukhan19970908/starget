<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('owners.create');
    }

    public function rules(): array
    {
        return [
            'type'      => ['required', 'in:individual,legal'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'iin'       => ['nullable', 'string', 'max:20'],
        ];
    }
}
