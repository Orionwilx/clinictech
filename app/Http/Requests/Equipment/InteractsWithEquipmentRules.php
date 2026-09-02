<?php

namespace App\Http\Requests\Equipment;

use App\Models\Area;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Reglas compartidas entre Store/Update de equipos (todo menos el serial,
 * cuya regla de unicidad difiere entre crear y actualizar).
 */
trait InteractsWithEquipmentRules
{
    /**
     * @return array<string, mixed>
     */
    protected function baseRules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'model_id' => ['nullable', 'exists:equipment_models,id'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Equipment::STATUSES))],

            // Datos específicos del cliente
            'entry_date' => ['nullable', 'date'],
            'warranty_status' => ['nullable', Rule::in(array_keys(Equipment::WARRANTY_STATUSES))],

            // Identificación
            'risk_class' => ['nullable', Rule::in(array_keys(Equipment::RISK_CLASSES))],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => [Rule::in(array_keys(Equipment::SPECIALTIES))],
            'invima_registry' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'origin_country' => ['nullable', 'string', 'max:255'],
            'maintenance_frequency' => ['nullable', Rule::in(array_keys(Equipment::FREQUENCIES))],
            'acquisition_type' => ['nullable', Rule::in(array_keys(Equipment::ACQUISITION_TYPES))],

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

            // Plantilla de mantenimiento / accesorios
            'maintenance_tasks' => ['nullable', 'array'],
            'maintenance_tasks.*' => [Rule::in(array_keys(Equipment::MAINTENANCE_TASKS))],
            'accessories' => ['nullable', 'array'],
            'accessories.*' => [Rule::in(array_keys(Equipment::ACCESSORIES))],
            'components' => ['nullable', 'string'],
            'default_ot_observations' => ['nullable', 'string'],
        ];
    }

    /**
     * Validaciones cruzadas: el modelo pertenece a la marca y el área al cliente.
     */
    protected function applyCrossFieldChecks(Validator $validator): void
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
