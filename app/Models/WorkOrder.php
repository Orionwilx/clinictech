<?php

namespace App\Models;

use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Estados: valor (código, EN) => etiqueta (UI, ES).
     */
    public const STATUSES = [
        'draft' => 'Borrador',
        'open' => 'Abierta',
        'assigned' => 'Asignada',
        'in_progress' => 'En proceso',
        'pending_review' => 'En revisión',
        'completed' => 'Completada',
        'closed' => 'Cerrada',
        'cancelled' => 'Cancelada',
    ];

    /**
     * Estados considerados "activos" (pendiente / en proceso).
     */
    public const ACTIVE_STATUSES = ['open', 'assigned', 'in_progress', 'pending_review'];

    /**
     * Tipos: valor (código, EN) => etiqueta (UI, ES).
     */
    public const TYPES = [
        'corrective' => 'Correctivo',
        'preventive' => 'Preventivo',
    ];

    /**
     * Prioridades: valor (código, EN) => etiqueta (UI, ES).
     */
    public const PRIORITIES = [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
    ];

    protected $fillable = [
        'code',
        'client_id',
        'equipment_id',
        'technician_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'diagnosis',
        'work_performed',
        'maintenance_tasks',
        'accessories_checked',
        'additional_observations',
        'scheduled_at',
        'started_at',
        'completed_at',
        'closed_at',
        'visible_to_client',
        'requested_by_client',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
            'maintenance_tasks' => 'array',
            'accessories_checked' => 'array',
            'visible_to_client' => 'boolean',
            'requested_by_client' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }
}
