<?php

namespace App\Models;

use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Estados: valor (código, EN) => etiqueta (UI, ES).
     */
    public const STATUSES = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'maintenance' => 'En mantenimiento',
        'retired' => 'Dado de baja',
    ];

    protected $fillable = [
        'client_id',
        'name',
        'type',
        'brand_id',
        'model_id',
        'serial_number',
        'purchase_date',
        'warranty_expiry',
        'location',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'model_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Etiqueta del estado en español para la UI.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
