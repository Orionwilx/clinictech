<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Acción masiva sobre varias OT: aprobar / rechazar / asignar. */
class BatchWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update work_orders') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'reject', 'assign'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:work_orders,id'],
            'technician_id' => ['nullable', 'exists:technicians,id'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
