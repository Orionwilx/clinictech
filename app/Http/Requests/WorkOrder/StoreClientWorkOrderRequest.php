<?php

namespace App\Http\Requests\WorkOrder;

use App\Models\Equipment;
use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreClientWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('cliente') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'equipment_id' => ['nullable', 'exists:equipment,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(array_keys(WorkOrder::TYPES))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $equipmentId = $this->input('equipment_id');
            $clientId = $this->user()->client?->id;

            if ($equipmentId && $clientId
                && ! Equipment::where('id', $equipmentId)->where('client_id', $clientId)->exists()) {
                $validator->errors()->add('equipment_id', 'El equipo seleccionado no te pertenece.');
            }
        });
    }
}
