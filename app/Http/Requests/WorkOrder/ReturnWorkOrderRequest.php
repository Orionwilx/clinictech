<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Devolver el trabajo al técnico: motivo obligatorio. */
class ReturnWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update work_orders') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }
}
