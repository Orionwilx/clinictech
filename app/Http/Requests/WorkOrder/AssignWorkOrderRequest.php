<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Aprobar solicitud / avanzar / asignar: técnico opcional. */
class AssignWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update work_orders') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'technician_id' => ['nullable', 'exists:technicians,id'],
        ];
    }
}
