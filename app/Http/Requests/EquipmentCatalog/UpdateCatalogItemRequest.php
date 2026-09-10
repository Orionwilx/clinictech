<?php

namespace App\Http\Requests\EquipmentCatalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCatalogItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update equipment_catalogs');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique($this->route('catalog'), 'name')->ignore($this->route('item')),
            ],
            'is_active' => ['boolean'],
        ];
    }
}
