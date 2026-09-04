<?php

namespace App\Models;

use Database\Factories\EquipmentModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentModel extends Model
{
    /** @use HasFactory<EquipmentModelFactory> */
    use HasFactory;

    protected $fillable = [
        'brand_id', 'name',
        'type', 'manufacturer', 'origin_country',
        'risk_class', 'specialties', 'invima_registry',
        'maintenance_frequency', 'maintenance_tasks', 'accessories',
    ];

    protected function casts(): array
    {
        return [
            'specialties'       => 'array',
            'maintenance_tasks' => 'array',
            'accessories'       => 'array',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'model_id');
    }

    /** Retorna los campos que pueden auto-completarse en el formulario de equipo. */
    public function autoFillData(): array
    {
        return [
            'type'                 => $this->type,
            'manufacturer'         => $this->manufacturer,
            'origin_country'       => $this->origin_country,
            'risk_class'           => $this->risk_class,
            'specialties'          => $this->specialties ?? [],
            'invima_registry'      => $this->invima_registry,
            'maintenance_frequency'=> $this->maintenance_frequency,
            'maintenance_tasks'    => $this->maintenance_tasks ?? [],
            'accessories'          => $this->accessories ?? [],
        ];
    }
}
