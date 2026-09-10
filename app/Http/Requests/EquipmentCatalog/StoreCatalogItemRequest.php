<?php

namespace App\Http\Requests\EquipmentCatalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatalogItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create equipment_catalogs');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // El segmento {catalog} coincide con el nombre de la tabla.
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique($this->route('catalog'), 'name')],
        ];
    }
}
