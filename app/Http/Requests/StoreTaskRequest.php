<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('tasks.create');
    }

    public function rules(): array
    {
        return [
            'type'           => ['required', 'in:create_client,create_contract,create_supplier'],
            'payload'        => ['nullable', 'array'],
            'priority'       => ['in:normal,high'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ];
    }
}
