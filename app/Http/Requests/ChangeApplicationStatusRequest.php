<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('applications.change_status');
    }

    public function rules(): array
    {
        return [
            'status'                      => ['required', 'in:open,in_transit,closed,client_refusal,our_refusal,mutual_refusal'],
            'refusal_reason_id'           => ['required_if:status,client_refusal,our_refusal,mutual_refusal', 'nullable', 'exists:refusal_reasons,id'],
            'refusal_comment'             => ['nullable', 'string'],
            'planned_transportations_count' => ['required_if:status,in_transit', 'nullable', 'integer', 'min:1'],
        ];
    }
}
