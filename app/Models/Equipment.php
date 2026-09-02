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

    /**
     * Estado de garantía del equipo.
     */
    public const WARRANTY_STATUSES = [
        'en_garantia' => 'En garantía',
        'sin_garantia' => 'Sin garantía',
        'leasing' => 'Leasing',
    ];

    /**
     * Clasificación por riesgo (INVIMA, Colombia).
     */
    public const RISK_CLASSES = [
        'I' => 'Clase I',
        'IIA' => 'Clase IIA',
        'IIB' => 'Clase IIB',
        'III' => 'Clase III',
    ];

    /**
     * Clasificación por especialidad (multi-selección).
     */
    public const SPECIALTIES = [
        'prevention' => 'Prevención',
        'rehabilitation' => 'Rehabilitación',
        'treatment' => 'Tratamiento',
        'lab_analysis' => 'Análisis de laboratorio',
    ];

    /**
     * Periodicidad de mantenimiento.
     */
    public const FREQUENCIES = [
        'monthly' => 'Mensual',
        'bimonthly' => 'Bimestral',
        'quarterly' => 'Trimestral',
        'biannual' => 'Semestral',
        'annual' => 'Anual',
    ];

    /**
     * Tipo de adquisición.
     */
    public const ACQUISITION_TYPES = [
        'purchase' => 'Compra',
        'comodato' => 'Comodato',
        'leasing' => 'Leasing',
        'donation' => 'Donación',
    ];

    /**
     * Subtareas de mantenimiento que pueden aplicar al equipo (plantilla).
     */
    public const MAINTENANCE_TASKS = [
        'functional_test' => 'Prueba de funcionamiento',
        'disassembly_cleaning' => 'Desarmado y limpieza',
        'leak_test' => 'Prueba de fugas',
        'alarm_check' => 'Revisión de alarma',
        'connectors_check' => 'Revisión de conectores',
        'electronic_adjustment' => 'Ajuste sistema electrónico',
        'electrical_adjustment' => 'Ajuste sistema eléctrico',
        'boards_cleaning' => 'Limpieza de tarjetas',
        'extractors_adjustment' => 'Ajuste de extractores',
        'mechanical_adjustment' => 'Ajustes mecánicos',
        'control_panel_check' => 'Revisión panel de control',
        'filters_cleaning' => 'Limpieza de filtros',
        'pneumatic_cleaning' => 'Limpieza neumático',
        'handpiece_adjustment' => 'Ajuste pieza de mano',
        'accessories_change' => 'Cambio de accesorios',
    ];

    /**
     * Accesorios cuyo estado puede aplicar al equipo (plantilla).
     */
    public const ACCESSORIES = [
        'ac_cable' => 'Cable de AC',
        'transducer' => 'Transductor',
        'ecg_cable' => 'Cable ECG',
        'temp_sensor' => 'Sensor de temperatura',
        'spo2_sensor' => 'Sensor SpO2',
        'oxygen_sensor' => 'Sensor de oxígeno',
        'nibp_hose' => 'Manguera NIBP',
        'oxygen_hose' => 'Manguera de oxígeno',
        'cuff' => 'Brazalete',
        'air_hose' => 'Manguera de aire',
        'control' => 'Control',
        'ekg_paddle' => 'Pala EKG',
        'precordial_cups' => 'Chupas precordiales',
        'battery' => 'Batería',
        'handpiece' => 'Pieza de mano',
    ];

    protected $fillable = [
        'client_id',
        'area_id',
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
        // Datos específicos del cliente
        'entry_date',
        'warranty_status',
        // Identificación
        'risk_class',
        'specialties',
        'invima_registry',
        'manufacturer',
        'origin_country',
        'maintenance_frequency',
        'acquisition_type',
        // Características técnicas
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
        // Plantilla de mantenimiento / accesorios
        'maintenance_tasks',
        'accessories',
        'components',
        'default_ot_observations',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
            'entry_date' => 'date',
            'specialties' => 'array',
            'maintenance_tasks' => 'array',
            'accessories' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
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

    public function warrantyStatusLabel(): ?string
    {
        return self::WARRANTY_STATUSES[$this->warranty_status] ?? $this->warranty_status;
    }

    public function riskClassLabel(): ?string
    {
        return self::RISK_CLASSES[$this->risk_class] ?? $this->risk_class;
    }

    public function frequencyLabel(): ?string
    {
        return self::FREQUENCIES[$this->maintenance_frequency] ?? $this->maintenance_frequency;
    }

    public function acquisitionTypeLabel(): ?string
    {
        return self::ACQUISITION_TYPES[$this->acquisition_type] ?? $this->acquisition_type;
    }

    /**
     * Etiquetas ES de las especialidades seleccionadas.
     *
     * @return array<int, string>
     */
    public function specialtyLabels(): array
    {
        return collect($this->specialties ?? [])
            ->map(fn ($key) => self::SPECIALTIES[$key] ?? $key)
            ->all();
    }
}
