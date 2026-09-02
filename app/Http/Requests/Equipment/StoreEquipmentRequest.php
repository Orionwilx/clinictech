<?php

namespace App\Http\Requests\Equipment;

use App\Models\Area;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create equipment') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'model_id' => ['nullable', 'exists:equipment_models,id'],
            'serial_number' => ['required', 'string', 'max:255', 'unique:equipment,serial_number'],
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

            $areaId = $this->input('area_id');
            $clientId = $this->input('client_id');

            if ($areaId && ! Area::where('id', $areaId)->where('client_id', $clientId)->exists()) {
                $validator->errors()->add('area_id', 'El área seleccionada no pertenece al cliente.');
            }
        });
    }
}
