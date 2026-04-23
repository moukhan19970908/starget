<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationStopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('applications.edit');
    }

    public function rules(): array
    {
        return [
            'stop_type_id' => ['required', 'exists:stop_types,id'],
            'address'      => ['required', 'string', 'max:500'],
            'sort_order'   => ['nullable', 'integer'],
            'expected_at'  => ['nullable', 'date'],
        ];
    }
}
