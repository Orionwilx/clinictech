<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const STATUSES = [
        'pending'    => 'Pendiente',
        'processing' => 'Procesando',
        'done'       => 'Listo',
        'failed'     => 'Fallido',
    ];

    public const TYPE_LABELS = [
        'work_orders' => 'Órdenes de trabajo',
        'maintenance' => 'Mantenimientos',
        'technicians' => 'Por técnico',
        'equipment'   => 'Por equipo',
    ];

    protected $fillable = [
        'type', 'filters', 'status',
        'generated_by', 'file_path', 'duration_ms',
        'downloaded_by', 'downloaded_at', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'filters'       => 'array',
            'downloaded_at' => 'datetime',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function downloader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'downloaded_by');
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function durationLabel(): string
    {
        if (! $this->duration_ms) {
            return '—';
        }

        return $this->duration_ms < 1000
            ? $this->duration_ms . ' ms'
            : round($this->duration_ms / 1000, 1) . ' s';
    }
}
