<?php

namespace App\Http\Requests\EquipmentCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentCategoryRequest extends FormRequest
{
    use InteractsWithEquipmentCategoryRules;

    public function authorize(): bool
    {
        return $this->user()->can('create equipment_categories');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:equipment_categories,name'],
            ...$this->templateRules(),
        ];
    }
}
