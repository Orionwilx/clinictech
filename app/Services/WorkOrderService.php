<?php

namespace App\Services;

use App\Models\Technician;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use Illuminate\Support\Facades\URL;

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

    // ─── Transiciones de estado ───────────────────────────────────────────────

    /** Cliente crea solicitud → draft */
    public function createClientRequest(array $data, int $clientUserId): WorkOrder
    {
        $data['code'] = $this->nextCode();
        $data['status'] = 'draft';
        $data['requested_by_client'] = true;
        $data['visible_to_client'] = false;

        $workOrder = WorkOrder::create($data);

        // Notificar a todos los admins
        User::role('admin')->each(fn ($admin) =>
            $admin->notify(new WorkOrderNotification(
                $workOrder,
                "Nueva solicitud de mantenimiento: {$workOrder->code} — {$workOrder->title}",
                route('admin.work_orders.show', $workOrder),
            ))
        );

        return $workOrder;
    }

    /** Admin aprueba solicitud del cliente → open (o assigned si se asigna técnico) */
    public function approveClientRequest(WorkOrder $workOrder, ?int $technicianId = null): void
    {
        $newStatus = $technicianId ? 'assigned' : 'open';
        $workOrder->update([
            'status' => $newStatus,
            'technician_id' => $technicianId,
            'rejection_reason' => null,
        ]);

        // Notificar al cliente
        if ($workOrder->client?->user) {
            $workOrder->client->user->notify(new WorkOrderNotification(
                $workOrder,
                "Tu solicitud {$workOrder->code} fue aprobada.",
                route('client.dashboard'),
            ));
        }

        // Notificar al técnico si fue asignado
        if ($technicianId) {
            $tech = Technician::find($technicianId);
            $tech?->user?->notify(new WorkOrderNotification(
                $workOrder,
                "Se te asignó la orden {$workOrder->code} — {$workOrder->title}.",
                route('technician.work_orders.show', $workOrder),
            ));
        }
    }

    /** Admin rechaza solicitud del cliente → cancelled */
    public function rejectClientRequest(WorkOrder $workOrder, ?string $reason = null): void
    {
        $workOrder->update([
            'status' => 'cancelled',
            'rejection_reason' => $reason,
        ]);

        if ($workOrder->client?->user) {
            $workOrder->client->user->notify(new WorkOrderNotification(
                $workOrder,
                "Tu solicitud {$workOrder->code} fue rechazada." . ($reason ? " Motivo: {$reason}" : ''),
                route('client.dashboard'),
            ));
        }
    }

    /** Técnico envía formulario a revisión → pending_review */
    public function submitForReview(WorkOrder $workOrder): void
    {
        $workOrder->update([
            'status' => 'pending_review',
            'completed_at' => $workOrder->completed_at ?? now(),
        ]);

        User::role('admin')->each(fn ($admin) =>
            $admin->notify(new WorkOrderNotification(
                $workOrder,
                "La orden {$workOrder->code} está lista para revisión.",
                route('admin.work_orders.show', $workOrder),
            ))
        );
    }

    /** Admin aprueba trabajo del técnico → closed */
    public function approveWork(WorkOrder $workOrder): void
    {
        $workOrder->update([
            'status' => 'closed',
            'rejection_reason' => null,
            'closed_at' => $workOrder->closed_at ?? now(),
        ]);

        $workOrder->technician?->user?->notify(new WorkOrderNotification(
            $workOrder,
            "Tu trabajo en la orden {$workOrder->code} fue aprobado.",
            route('technician.work_orders.show', $workOrder),
        ));
    }

    /** Admin rechaza trabajo del técnico → in_progress */
    public function rejectWork(WorkOrder $workOrder, string $reason): void
    {
        $workOrder->update([
            'status' => 'in_progress',
            'rejection_reason' => $reason,
        ]);

        $workOrder->technician?->user?->notify(new WorkOrderNotification(
            $workOrder,
            "Tu trabajo en la orden {$workOrder->code} fue devuelto para corrección. Motivo: {$reason}",
            route('technician.work_orders.show', $workOrder),
        ));
    }

    /** Admin envía OT al cliente → visible_to_client = true */
    public function sendToClient(WorkOrder $workOrder): void
    {
        $workOrder->update(['visible_to_client' => true]);

        if ($workOrder->client?->user) {
            $workOrder->client->user->notify(new WorkOrderNotification(
                $workOrder,
                "La orden de trabajo {$workOrder->code} ya está disponible en tu panel.",
                route('client.work_orders.show', $workOrder),
            ));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

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
            'pending_review' => 'completed_at',
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
