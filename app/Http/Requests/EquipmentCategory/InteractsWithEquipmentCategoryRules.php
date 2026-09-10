<?php

namespace App\Http\Requests\EquipmentCategory;

use App\Models\Equipment;
use Illuminate\Validation\Rule;

trait InteractsWithEquipmentCategoryRules
{
    /**
     * Reglas de la plantilla de categoría (compartidas por Store/Update).
     *
     * @return array<string, mixed>
     */
    protected function templateRules(): array
    {
        return [
            'description' => ['nullable', 'string'],
            // Identificación
            'risk_class' => ['nullable', Rule::in(array_keys(Equipment::RISK_CLASSES))],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'origin_country' => ['nullable', 'string', 'max:255'],
            'maintenance_frequency' => ['nullable', Rule::in(array_keys(Equipment::FREQUENCIES))],
            // Características técnicas
            'voltage' => ['nullable', 'string', 'max:255'],
            'amperage' => ['nullable', 'string', 'max:255'],
            'current' => ['nullable', 'string', 'max:255'],
            'power' => ['nullable', 'string', 'max:255'],
            'temperature' => ['nullable', 'string', 'max:255'],
            'pressure' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:255'],
            'speed' => ['nullable', 'string', 'max:255'],
            'predominant_technology' => ['nullable', 'string', 'max:255'],
            'technical_observations' => ['nullable', 'string'],
            'general_observations' => ['nullable', 'string'],
            // Mantenimiento / accesorios
            'maintenance_tasks' => ['nullable', 'array'],
            'maintenance_tasks.*' => ['string', 'max:255'],
            'accessories' => ['nullable', 'array'],
            'accessories.*' => ['string', 'max:255'],
            'components' => ['nullable', 'string'],
            'default_ot_observations' => ['nullable', 'string'],
        ];
    }
}
