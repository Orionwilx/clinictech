<?php

namespace App\Services;

use App\Models\Technician;
use App\Models\Upload;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class WorkOrderService
{
    /**
     * Características del equipo editables desde la OT (se guardan en su ficha).
     *
     * @var list<string>
     */
    public const EQUIPMENT_FIELDS = [
        'risk_class', 'voltage', 'amperage', 'current', 'power',
        'temperature', 'pressure', 'weight', 'speed', 'predominant_technology',
    ];

    /**
     * Separa del payload de la OT los campos del equipo (prefijo eq_).
     * Devuelve [datosOT, datosEquipo].
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public function splitEquipmentData(array $data): array
    {
        $equipmentData = [];

        foreach (self::EQUIPMENT_FIELDS as $field) {
            $key = "eq_{$field}";
            if (array_key_exists($key, $data)) {
                $equipmentData[$field] = Arr::pull($data, $key);
            }
        }

        return [$data, $equipmentData];
    }

    /**
     * Persiste en la ficha del equipo vinculado lo editado desde la OT:
     * características (eq_*) y subtareas/accesorios (= lo marcado en la orden).
     * No hace nada si la OT no tiene equipo.
     *
     * @param  array<string, mixed>  $equipmentData  características (ya separadas)
     * @param  array<string, mixed>  $orderData  payload de la OT (para tomar los checklists)
     */
    public function syncEquipmentData(WorkOrder $workOrder, array $equipmentData, array $orderData): void
    {
        $equipment = $workOrder->equipment;

        if (! $equipment) {
            return;
        }

        if (array_key_exists('maintenance_tasks', $orderData)) {
            $equipmentData['maintenance_tasks'] = $orderData['maintenance_tasks'] ?? [];
        }

        if (array_key_exists('accessories_checked', $orderData)) {
            $equipmentData['accessories'] = $orderData['accessories_checked'] ?? [];
        }

        if ($equipmentData !== []) {
            $equipment->update($equipmentData);
        }
    }

    /**
     * Crea una OT asignando código consecutivo y sellos de tiempo por estado.
     *
     * @param  array<string, mixed>  $data  validado por StoreWorkOrderRequest
     */
    public function create(array $data): WorkOrder
    {
        $data['code'] = $this->nextCode($data['type'] ?? null);
        $data = $this->applyStatusTimestamps($data, null);

        $workOrder = WorkOrder::create($data);

        // El admin puede crear la OT ya asignada a un técnico: notifícalo.
        if (! empty($data['technician_id'])) {
            $this->notifyTechnicianAssigned($workOrder);
        }

        return $workOrder;
    }

    /**
     * Actualiza una OT ajustando los sellos de tiempo si cambió el estado.
     * Si cambió el tipo, re-deriva la sigla del código (OT-000001_MP…).
     * Si se asignó/reasignó técnico, lo notifica.
     *
     * @param  array<string, mixed>  $data  validado por UpdateWorkOrderRequest
     */
    public function update(WorkOrder $workOrder, array $data): void
    {
        $data = $this->applyStatusTimestamps($data, $workOrder);

        if (array_key_exists('type', $data) && $data['type'] !== $workOrder->type) {
            $data['code'] = $this->appendTypeAbbr($this->stripTypeAbbr($workOrder->code), $data['type']);
        }

        $technicianChanged = array_key_exists('technician_id', $data)
            && ! empty($data['technician_id'])
            && (int) $data['technician_id'] !== (int) $workOrder->technician_id;

        $workOrder->update($data);

        if ($technicianChanged) {
            $this->notifyTechnicianAssigned($workOrder);
        }
    }

    /**
     * Guarda (reemplazando) la firma del técnico o del cliente de una OT.
     * $kind: 'technician' | 'client'. Se sube al disco privado sin recompresión
     * para no ennegrecer PNG con transparencia.
     */
    public function storeSignature(WorkOrder $workOrder, UploadedFile $file, string $kind): Upload
    {
        $collection = "signature_{$kind}";

        // Solo hay una firma vigente por tipo: purga la anterior.
        $workOrder->uploadMany($collection)->get()->each->purge();

        $path = $file->store("work_order_signatures/{$workOrder->id}", 'private');

        return $workOrder->uploads()->create([
            'collection' => $collection,
            'disk' => 'private',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);
    }

    // ─── Transiciones de estado ───────────────────────────────────────────────

    /** Cliente crea solicitud → draft */
    public function createClientRequest(array $data, int $clientUserId): WorkOrder
    {
        $data['code'] = $this->nextCode($data['type'] ?? null);
        $data['status'] = 'draft';
        $data['requested_by_client'] = true;
        $data['visible_to_client'] = false;

        $workOrder = WorkOrder::create($data);

        // Notificar a todos los admins
        User::role('admin')->each(fn ($admin) => $admin->notify(new WorkOrderNotification(
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
                "Tu solicitud {$workOrder->code} fue rechazada.".($reason ? " Motivo: {$reason}" : ''),
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

        User::role('admin')->each(fn ($admin) => $admin->notify(new WorkOrderNotification(
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

    /**
     * Avanza la OT al siguiente estado positivo según su punto de decisión.
     * draft→aprueba solicitud · pending_review→aprueba trabajo · closed→envía al cliente.
     * Devuelve false si la OT no estaba en un punto de decisión (batch la ignora).
     */
    public function advanceForAdmin(WorkOrder $workOrder, ?int $technicianId = null): bool
    {
        if ($workOrder->status === 'draft' && $workOrder->requested_by_client) {
            $this->approveClientRequest($workOrder, $technicianId);

            return true;
        }

        if ($workOrder->status === 'pending_review') {
            $this->approveWork($workOrder);

            return true;
        }

        if ($workOrder->status === 'closed' && ! $workOrder->visible_to_client) {
            $this->sendToClient($workOrder);

            return true;
        }

        return false;
    }

    /**
     * Retrocede la OT (rechazo/devolución) según su punto de decisión.
     * draft→rechaza solicitud · pending_review→devuelve al técnico.
     */
    public function regressForAdmin(WorkOrder $workOrder, ?string $reason = null): bool
    {
        if ($workOrder->status === 'draft' && $workOrder->requested_by_client) {
            $this->rejectClientRequest($workOrder, $reason);

            return true;
        }

        if ($workOrder->status === 'pending_review') {
            $this->rejectWork($workOrder, $reason ?: 'Sin especificar');

            return true;
        }

        return false;
    }

    /**
     * Asigna/reasigna técnico sin salir de la lista. Ajusta open⇆assigned.
     */
    public function assignTechnician(WorkOrder $workOrder, ?int $technicianId): void
    {
        $status = $workOrder->status;
        if ($technicianId && $status === 'open') {
            $status = 'assigned';
        } elseif (! $technicianId && $status === 'assigned') {
            $status = 'open';
        }

        $workOrder->update(['technician_id' => $technicianId, 'status' => $status]);

        if ($technicianId) {
            Technician::find($technicianId)?->user?->notify(new WorkOrderNotification(
                $workOrder,
                "Se te asignó la orden {$workOrder->code} — {$workOrder->title}.",
                route('technician.work_orders.show', $workOrder),
            ));
        }
    }

    /**
     * Aplica una acción masiva a varias OT. Devuelve cuántas se afectaron.
     *
     * @param  'approve'|'reject'|'assign'  $action
     * @param  array<int>  $ids
     */
    public function batchForAdmin(string $action, array $ids, ?int $technicianId = null, ?string $reason = null): int
    {
        $orders = WorkOrder::whereIn('id', $ids)->get();
        $affected = 0;

        foreach ($orders as $order) {
            if ($action === 'assign') {
                $this->assignTechnician($order, $technicianId);
                $affected++;

                continue;
            }

            $ok = $action === 'approve'
                ? $this->advanceForAdmin($order, $technicianId)
                : $this->regressForAdmin($order, $reason);

            if ($ok) {
                $affected++;
            }
        }

        return $affected;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Genera el siguiente código de OT con sigla del tipo: OT-000001_MP.
     */
    private function nextCode(?string $type = null): string
    {
        $next = (WorkOrder::withTrashed()->max('id') ?? 0) + 1;
        $base = 'OT-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);

        return $this->appendTypeAbbr($base, $type);
    }

    /**
     * Añade la sigla del tipo (MP/MC/MR) a un código base OT-000001.
     */
    private function appendTypeAbbr(string $base, ?string $type): string
    {
        $abbr = WorkOrder::TYPE_ABBREVIATIONS[$type] ?? null;

        return $abbr ? "{$base}_{$abbr}" : $base;
    }

    /**
     * Quita la sigla del tipo del final del código (OT-000001_MP → OT-000001).
     */
    private function stripTypeAbbr(string $code): string
    {
        return preg_replace('/_[A-Z]{2}$/', '', $code);
    }

    /**
     * Notifica al técnico asignado que tiene una nueva OT.
     */
    private function notifyTechnicianAssigned(WorkOrder $workOrder): void
    {
        Technician::find($workOrder->technician_id)?->user?->notify(new WorkOrderNotification(
            $workOrder,
            "Se te asignó la orden {$workOrder->code} — {$workOrder->title}.",
            route('technician.work_orders.show', $workOrder),
        ));
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
