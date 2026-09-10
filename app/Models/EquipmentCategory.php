<?php

namespace App\Models;

use Database\Factories\EquipmentCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentCategory extends Model
{
    /** @use HasFactory<EquipmentCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        // Plantilla — Identificación
        'risk_class',
        'specialties',
        'manufacturer',
        'origin_country',
        'maintenance_frequency',
        // Plantilla — Características técnicas
        'voltage',
        'amperage',
        'current',
        'power',
        'temperature',
        'pressure',
        'weight',
        'speed',
        'predominant_technology',
        'technical_observations',
        'general_observations',
        // Plantilla — Mantenimiento / accesorios
        'maintenance_tasks',
        'accessories',
        'components',
        'default_ot_observations',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'maintenance_tasks' => 'array',
            'accessories' => 'array',
        ];
    }

    public function models(): HasMany
    {
        return $this->hasMany(EquipmentModel::class, 'category_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }

    /**
     * Plantilla que se prediligencia (snapshot) en el formulario de equipo.
     */
    public function templateData(): array
    {
        return [
            'name' => $this->name,
            'risk_class' => $this->risk_class,
            'specialties' => $this->specialties ?? [],
            'manufacturer' => $this->manufacturer,
            'origin_country' => $this->origin_country,
            'maintenance_frequency' => $this->maintenance_frequency,
            'voltage' => $this->voltage,
            'amperage' => $this->amperage,
            'current' => $this->current,
            'power' => $this->power,
            'temperature' => $this->temperature,
            'pressure' => $this->pressure,
            'weight' => $this->weight,
            'speed' => $this->speed,
            'predominant_technology' => $this->predominant_technology,
            'technical_observations' => $this->technical_observations,
            'general_observations' => $this->general_observations,
            'maintenance_tasks' => $this->maintenance_tasks ?? [],
            'accessories' => $this->accessories ?? [],
            'components' => $this->components,
            'default_ot_observations' => $this->default_ot_observations,
        ];
    }
}
