<?php

namespace App\Http\Requests\EquipmentCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentCategoryRequest extends FormRequest
{
    use InteractsWithEquipmentCategoryRules;

    public function authorize(): bool
    {
        return $this->user()->can('update equipment_categories');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('equipment_categories', 'name')->ignore($this->route('equipment_category')),
            ],
            ...$this->templateRules(),
        ];
    }
}
