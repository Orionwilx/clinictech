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
        'open' => 'Abierta',
        'assigned' => 'Asignada',
        'in_progress' => 'En proceso',
        'completed' => 'Completada',
        'closed' => 'Cerrada',
        'cancelled' => 'Cancelada',
    ];

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
        'scheduled_at',
        'started_at',
        'completed_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
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
