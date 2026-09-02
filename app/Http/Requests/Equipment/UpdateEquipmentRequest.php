<?php

namespace App\Http\Requests\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentModel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEquipmentRequest extends FormRequest
{
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

        return [
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'model_id' => ['nullable', 'exists:equipment_models,id'],
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('equipment', 'serial_number')->ignore($equipment->id)],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Equipment::STATUSES))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $modelId = $this->input('model_id');
            $brandId = $this->input('brand_id');

            if ($modelId && ! EquipmentModel::where('id', $modelId)->where('brand_id', $brandId)->exists()) {
                $validator->errors()->add('model_id', 'El modelo seleccionado no pertenece a la marca.');
            }
        });
    }
}
