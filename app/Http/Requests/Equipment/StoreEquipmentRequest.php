<?php

namespace App\Http\Requests\Equipment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEquipmentRequest extends FormRequest
{
    use InteractsWithEquipmentRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create equipment') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'serial_number' => ['required', 'string', 'max:255', 'unique:equipment,serial_number'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $this->applyCrossFieldChecks($validator);
    }
}
