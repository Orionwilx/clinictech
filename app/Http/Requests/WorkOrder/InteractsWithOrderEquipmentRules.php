<?php

namespace App\Http\Requests\WorkOrder;

use App\Models\Equipment;
use App\Services\WorkOrderService;
use Illuminate\Validation\Rule;

/**
 * Reglas de los campos del equipo editables desde la OT (características +
 * subtareas/accesorios). Compartidas por los Store/Update de admin y técnico.
 */
trait InteractsWithOrderEquipmentRules
{
    /**
     * @return array<string, mixed>
     */
    protected function orderEquipmentRules(): array
    {
        $rules = [
            'maintenance_tasks' => ['nullable', 'array'],
            'maintenance_tasks.*' => ['string', 'max:255'],
            'accessories_checked' => ['nullable', 'array'],
            'accessories_checked.*' => ['string', 'max:255'],
            'eq_risk_class' => ['nullable', Rule::in(array_keys(Equipment::RISK_CLASSES))],
        ];

        foreach (WorkOrderService::EQUIPMENT_FIELDS as $field) {
            if ($field !== 'risk_class') {
                $rules["eq_{$field}"] = ['nullable', 'string', 'max:255'];
            }
        }

        return $rules;
    }
}
