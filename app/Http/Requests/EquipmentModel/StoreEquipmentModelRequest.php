<?php

namespace App\Http\Requests\EquipmentModel;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create equipment_models') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('equipment_models', 'name')->where(fn ($q) => $q->where('brand_id', $this->input('brand_id'))),
            ],
        ];
    }
}
