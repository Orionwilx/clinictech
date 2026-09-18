<?php

namespace App\Models;

use App\Traits\HasUploads;
use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory, HasUploads, SoftDeletes;

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
        'preventive' => 'Preventivo',
        'corrective' => 'Correctivo',
        'review' => 'Revisión',
    ];

    /**
     * Sigla por tipo, usada en el código de la OT y en el nombre de los PDF.
     * MP = mantenimiento preventivo · MC = correctivo · MR = revisión.
     */
    public const TYPE_ABBREVIATIONS = [
        'preventive' => 'MP',
        'corrective' => 'MC',
        'review' => 'MR',
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

    public function photos(): MorphMany
    {
        return $this->uploadMany('photo');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Sigla del tipo (MP/MC/MR) para código y nombres de archivo.
     */
    public function typeAbbreviation(): string
    {
        return self::TYPE_ABBREVIATIONS[$this->type] ?? 'OT';
    }

    /**
     * Nombre del archivo PDF de la OT, saneado y sin acentos:
     * MP_OT000001_equipo_marca_serie.pdf  (para preventivas MP, correctivas MC, revisión MR).
     */
    public function pdfFileName(): string
    {
        $numericCode = preg_replace('/_[A-Z]{2}$/', '', (string) $this->code);

        $parts = [$this->typeAbbreviation(), $numericCode];

        if ($equipment = $this->equipment) {
            $parts[] = $equipment->name;
            $parts[] = optional($equipment->brand)->name;
            $parts[] = $equipment->serial_number;
        }

        return static::sanitizeFileName($parts).'.pdf';
    }

    /**
     * Une partes en un nombre de archivo seguro: sin acentos, espacios → guion,
     * separando cada parte con guion bajo, descartando vacías.
     *
     * @param  array<int, string|null>  $parts
     */
    public static function sanitizeFileName(array $parts): string
    {
        return collect($parts)
            ->filter(fn ($p) => filled($p))
            ->map(fn ($p) => trim(preg_replace('/[^A-Za-z0-9]+/', '-', Str::ascii((string) $p)), '-'))
            ->filter(fn ($p) => $p !== '')
            ->implode('_');
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    // ─── Visibilidad para el cliente ───────────────────────────────────────────

    /**
     * OT aprobadas y enviadas por el admin: lo ÚNICO que el cliente ve en
     * hojas de vida, PDFs y métricas. En proceso, sin aprobar o eliminadas
     * jamás se muestran.
     */
    public function scopeSentToClient($query)
    {
        return $query->where('visible_to_client', true);
    }

    /**
     * Lo listable en el panel del cliente: OT enviadas + sus propias
     * solicitudes (draft/cancelled), para que pueda seguirlas.
     */
    public function scopeListableForClient($query)
    {
        return $query->where(fn ($q) => $q
            ->where('visible_to_client', true)
            ->orWhere(fn ($q) => $q->where('requested_by_client', true)
                ->whereIn('status', ['draft', 'cancelled'])));
    }

    // ─── Flujo colaborativo: puntos de decisión del admin ─────────────────────

    /**
     * OT que esperan una decisión del admin: solicitud del cliente (draft),
     * trabajo del técnico por revisar (pending_review) o cierre sin enviar.
     */
    public function scopeAwaitingAdminAction($query)
    {
        return $query->where(function ($q) {
            $q->where(fn ($q) => $q->where('status', 'draft')->where('requested_by_client', true))
                ->orWhere('status', 'pending_review')
                ->orWhere(fn ($q) => $q->where('status', 'closed')->where('visible_to_client', false));
        });
    }

    /**
     * Acción primaria que el admin puede ejecutar sobre esta OT desde la lista,
     * o null si no requiere decisión. Fat model: la vista solo pinta.
     *
     * @return array{key: string, label: string, color: string, can_reject: bool, reject_label: string, reject_required: bool, needs_technician: bool}|null
     */
    public function primaryAdminAction(): ?array
    {
        if ($this->status === 'draft' && $this->requested_by_client) {
            return [
                'key' => 'approve', 'label' => 'Aprobar', 'color' => 'green',
                'can_reject' => true, 'reject_label' => 'Rechazar', 'reject_required' => false,
                'needs_technician' => true,
            ];
        }

        if ($this->status === 'pending_review') {
            return [
                'key' => 'approve', 'label' => 'Aprobar', 'color' => 'green',
                'can_reject' => true, 'reject_label' => 'Devolver', 'reject_required' => true,
                'needs_technician' => false,
            ];
        }

        if ($this->status === 'closed' && ! $this->visible_to_client) {
            return [
                'key' => 'send', 'label' => 'Enviar', 'color' => 'brand',
                'can_reject' => false, 'reject_label' => '', 'reject_required' => false,
                'needs_technician' => false,
            ];
        }

        return null;
    }
}
