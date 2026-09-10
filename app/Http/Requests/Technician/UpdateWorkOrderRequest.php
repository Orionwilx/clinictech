<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\WorkOrder\InteractsWithOrderEquipmentRules;
use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Diligenciamiento del formulario por el técnico dueño de la OT. */
class UpdateWorkOrderRequest extends FormRequest
{
    use InteractsWithOrderEquipmentRules;

    public function authorize(): bool
    {
        $workOrder = $this->route('work_order');

        return $workOrder instanceof WorkOrder
            && $this->user()?->technician?->id === $workOrder->technician_id
            && in_array($workOrder->status, ['assigned', 'in_progress'], true);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'diagnosis' => ['nullable', 'string'],
            'work_performed' => ['nullable', 'string'],
            'additional_observations' => ['nullable', 'string'],
            ...$this->orderEquipmentRules(),
        ];
    }
}
