<?php

namespace App\Services;

use App\Models\WorkOrder;

class WorkOrderService
{
    /**
     * Crea una OT asignando código consecutivo y sellos de tiempo por estado.
     *
     * @param  array<string, mixed>  $data  validado por StoreWorkOrderRequest
     */
    public function create(array $data): WorkOrder
    {
        $data['code'] = $this->nextCode();
        $data = $this->applyStatusTimestamps($data, null);

        return WorkOrder::create($data);
    }

    /**
     * Actualiza una OT ajustando los sellos de tiempo si cambió el estado.
     *
     * @param  array<string, mixed>  $data  validado por UpdateWorkOrderRequest
     */
    public function update(WorkOrder $workOrder, array $data): void
    {
        $data = $this->applyStatusTimestamps($data, $workOrder);

        $workOrder->update($data);
    }

    /**
     * Genera el siguiente código de OT: OT-000001.
     */
    private function nextCode(): string
    {
        $next = (WorkOrder::withTrashed()->max('id') ?? 0) + 1;

        return 'OT-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Sella started_at/completed_at/closed_at según el estado destino,
     * solo si aún no tienen valor.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyStatusTimestamps(array $data, ?WorkOrder $workOrder): array
    {
        $status = $data['status'] ?? $workOrder?->status;

        $stamps = [
            'in_progress' => 'started_at',
            'completed' => 'completed_at',
            'closed' => 'closed_at',
        ];

        if (isset($stamps[$status])) {
            $column = $stamps[$status];
            $current = $data[$column] ?? $workOrder?->{$column};

            if (empty($current)) {
                $data[$column] = now();
            }
        }

        return $data;
    }
}
