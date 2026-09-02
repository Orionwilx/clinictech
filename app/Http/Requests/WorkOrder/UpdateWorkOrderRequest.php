<?php

namespace App\Http\Requests\WorkOrder;

use App\Models\Equipment;
use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update work_orders') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'equipment_id' => ['nullable', 'exists:equipment,id'],
            'technician_id' => ['nullable', 'exists:technicians,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(array_keys(WorkOrder::TYPES))],
            'priority' => ['required', Rule::in(array_keys(WorkOrder::PRIORITIES))],
            'status' => ['required', Rule::in(array_keys(WorkOrder::STATUSES))],
            'diagnosis' => ['nullable', 'string'],
            'work_performed' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $equipmentId = $this->input('equipment_id');
            $clientId = $this->input('client_id');

            if ($equipmentId && $clientId
                && ! Equipment::where('id', $equipmentId)->where('client_id', $clientId)->exists()) {
                $validator->errors()->add('equipment_id', 'El equipo seleccionado no pertenece al cliente.');
            }
        });
    }
}
