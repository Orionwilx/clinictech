<?php

namespace App\Http\Requests\Equipment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEquipmentRequest extends FormRequest
{
    use InteractsWithEquipmentRules;

    public function authorize(): bool
    {
        return $this->user()?->can('update equipment') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $equipment = $this->route('equipment');

        return array_merge($this->baseRules(), [
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('equipment', 'serial_number')->ignore($equipment->id)],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $this->applyCrossFieldChecks($validator);
    }
}
