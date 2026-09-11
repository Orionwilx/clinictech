<?php

namespace Database\Seeders;

use App\Models\Accessory;
use App\Models\Brand;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\MaintenanceTask;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

/**
 * Catálogo maestro de equipos: opciones (subtareas/accesorios/especialidades),
 * categorías (plantillas) y marcas/modelos por categoría.
 * Idempotente: usa firstOrCreate, se puede re-ejecutar sin duplicar.
 *
 * Ejecutar: php artisan db:seed --class=EquipmentCatalogSeeder
 */
class EquipmentCatalogSeeder extends Seeder
{
    private const MAINTENANCE_TASKS = [
        'Prueba de funcionamiento', 'Desarmado y limpieza', 'Prueba de fugas', 'Revisión de alarma',
        'Revisión de conectores', 'Ajuste sistema electrónico', 'Ajuste sistema eléctrico',
        'Limpieza de tarjetas', 'Ajuste de extractores', 'Ajustes mecánicos', 'Revisión panel de control',
        'Limpieza de filtros', 'Limpieza neumático', 'Ajuste pieza de mano', 'Cambio de accesorios',
    ];

    private const ACCESSORIES = [
        'Cable de AC', 'Transductor', 'Cable ECG', 'Sensor de temperatura', 'Sensor SpO2',
        'Sensor de oxígeno', 'Manguera NIBP', 'Manguera de oxígeno', 'Brazalete', 'Manguera de aire',
        'Control', 'Pala EKG', 'Chupas precordiales', 'Batería', 'Pieza de mano',
    ];

    private const SPECIALTIES = [
        'Prevención', 'Rehabilitación', 'Tratamiento', 'Análisis de laboratorio',
    ];

    /**
     * Categoría => plantilla (defaults del formulario de equipo).
     *
     * @var array<string, array<string, mixed>>
     */
    private const CATEGORIES = [
        'Monitor de signos vitales' => [
            'risk_class' => 'IIB',
            'voltage' => '110-240V', 'power' => '150W',
            'specialties' => ['Prevención', 'Tratamiento'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma', 'Revisión de conectores', 'Limpieza de tarjetas'],
            'accessories' => ['Cable de AC', 'Sensor SpO2', 'Manguera NIBP', 'Brazalete', 'Batería'],
        ],
        'Ventilador mecánico' => [
            'risk_class' => 'III',
            'voltage' => '110-240V',
            'specialties' => ['Tratamiento'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Limpieza de filtros', 'Prueba de fugas', 'Revisión de alarma'],
            'accessories' => ['Cable de AC', 'Sensor de oxígeno', 'Manguera de oxígeno', 'Manguera de aire', 'Batería'],
        ],
        'Desfibrilador' => [
            'risk_class' => 'III',
            'specialties' => ['Tratamiento'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma', 'Ajuste sistema eléctrico'],
            'accessories' => ['Cable de AC', 'Pala EKG', 'Cable ECG', 'Batería'],
        ],
        'Bomba de infusión' => [
            'risk_class' => 'IIB',
            'specialties' => ['Tratamiento'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma', 'Ajustes mecánicos'],
            'accessories' => ['Cable de AC', 'Batería'],
        ],
        'Ecógrafo' => [
            'risk_class' => 'IIA',
            'specialties' => ['Prevención', 'Análisis de laboratorio'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Limpieza de tarjetas', 'Revisión panel de control'],
            'accessories' => ['Cable de AC', 'Transductor', 'Control'],
        ],
        'Máquina de anestesia' => [
            'risk_class' => 'III', 'voltage' => '110-240V', 'pressure' => '3-5 bar',
            'specialties' => ['Tratamiento'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Prueba de fugas', 'Revisión de alarma', 'Ajustes mecánicos'],
            'accessories' => ['Cable de AC', 'Manguera de oxígeno', 'Manguera de aire', 'Sensor de oxígeno'],
        ],
        'Incubadora' => [
            'risk_class' => 'IIB', 'voltage' => '110-127V', 'power' => '400W', 'temperature' => '20-39°C',
            'specialties' => ['Tratamiento', 'Prevención'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma', 'Limpieza de filtros', 'Desarmado y limpieza'],
            'accessories' => ['Cable de AC', 'Sensor de temperatura', 'Sensor SpO2'],
        ],
        'Equipo de imagenología' => [
            'risk_class' => 'IIA', 'voltage' => '220-440V', 'power' => '80kW',
            'specialties' => ['Prevención', 'Análisis de laboratorio'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Limpieza de tarjetas', 'Revisión panel de control', 'Ajuste sistema electrónico'],
            'accessories' => ['Cable de AC', 'Control', 'Transductor'],
        ],
        'Máquina de diálisis' => [
            'risk_class' => 'III', 'voltage' => '110-240V', 'pressure' => '1.5-2 bar', 'temperature' => '35-39°C',
            'specialties' => ['Tratamiento'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Prueba de fugas', 'Limpieza de filtros', 'Revisión de conectores'],
            'accessories' => ['Cable de AC', 'Manguera de aire', 'Sensor de temperatura'],
        ],
        'Equipo quirúrgico' => [
            'risk_class' => 'IIB', 'voltage' => '110-240V', 'power' => '300W',
            'specialties' => ['Tratamiento'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de conectores', 'Ajustes mecánicos', 'Desarmado y limpieza'],
            'accessories' => ['Cable de AC', 'Pieza de mano', 'Batería'],
        ],
    ];

    /**
     * Marca => [fabricante, país de origen] (autodiligenciados en el equipo).
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const BRAND_INFO = [
        'Philips' => ['Philips Medical Systems', 'Países Bajos'],
        'GE Healthcare' => ['GE Healthcare', 'Estados Unidos'],
        'Dräger' => ['Drägerwerk AG', 'Alemania'],
        'Mindray' => ['Mindray Bio-Medical', 'China'],
        'Medtronic' => ['Medtronic plc', 'Irlanda'],
        'B. Braun' => ['B. Braun Melsungen', 'Alemania'],
        'Fresenius' => ['Fresenius Medical Care', 'Alemania'],
        'Siemens Healthineers' => ['Siemens Healthineers', 'Alemania'],
        'Zoll' => ['ZOLL Medical', 'Estados Unidos'],
        'Nihon Kohden' => ['Nihon Kohden Corp.', 'Japón'],
    ];

    /**
     * Marca => [modelo => categoría].
     *
     * @var array<string, array<string, string>>
     */
    private const CATALOG = [
        'Philips' => [
            'IntelliVue MX40' => 'Monitor de signos vitales', 'IntelliVue MX450' => 'Monitor de signos vitales',
            'IntelliVue MX550' => 'Monitor de signos vitales', 'Efficia CM120' => 'Monitor de signos vitales',
            'HeartStart XL' => 'Desfibrilador', 'Respironics V60' => 'Ventilador mecánico',
        ],
        'GE Healthcare' => [
            'CARESCAPE B650' => 'Monitor de signos vitales', 'CARESCAPE B450' => 'Monitor de signos vitales',
            'Dash 4000' => 'Monitor de signos vitales', 'Logiq E10' => 'Ecógrafo',
            'Vivid E95' => 'Ecógrafo', 'Giraffe OmniBed' => 'Incubadora',
        ],
        'Dräger' => [
            'Evita V300' => 'Ventilador mecánico', 'Evita V500' => 'Ventilador mecánico',
            'Babylog VN500' => 'Ventilador mecánico', 'Fabius Tiro' => 'Máquina de anestesia',
            'Savina 300' => 'Ventilador mecánico', 'Infinity C700' => 'Monitor de signos vitales',
        ],
        'Mindray' => [
            'BeneVision N1' => 'Monitor de signos vitales', 'BeneVision N22' => 'Monitor de signos vitales',
            'uMEC12' => 'Monitor de signos vitales', 'SV300' => 'Ventilador mecánico',
            'BeneHeart D6' => 'Desfibrilador', 'Resona 7' => 'Ecógrafo',
        ],
        'Medtronic' => [
            'Puritan Bennett 980' => 'Ventilador mecánico', 'Nellcor PM1000N' => 'Monitor de signos vitales',
            'BIS Complete' => 'Monitor de signos vitales', 'Newport HT70' => 'Ventilador mecánico',
        ],
        'B. Braun' => [
            'Infusomat Space' => 'Bomba de infusión', 'Perfusor Space' => 'Bomba de infusión',
            'Dialog+' => 'Máquina de diálisis', 'Aesculap' => 'Equipo quirúrgico',
        ],
        'Fresenius' => [
            'Agilia VP' => 'Bomba de infusión', 'Agilia SP' => 'Bomba de infusión',
            '4008S' => 'Máquina de diálisis', '5008S CorDiax' => 'Máquina de diálisis',
        ],
        'Siemens Healthineers' => [
            'Acuson Juniper' => 'Ecógrafo', 'Magnetom Sola' => 'Equipo de imagenología',
            'Somatom Go' => 'Equipo de imagenología', 'Multix Impact' => 'Equipo de imagenología',
        ],
        'Zoll' => [
            'R Series' => 'Desfibrilador', 'X Series' => 'Desfibrilador',
            'AED Plus' => 'Desfibrilador', 'Propaq MD' => 'Monitor de signos vitales',
        ],
        'Nihon Kohden' => [
            'Life Scope G9' => 'Monitor de signos vitales', 'Life Scope PT' => 'Monitor de signos vitales',
            'Cardiolife TEC-5600' => 'Desfibrilador', 'BSM-6000' => 'Monitor de signos vitales',
        ],
    ];

    public function run(): void
    {
        foreach (self::MAINTENANCE_TASKS as $name) {
            MaintenanceTask::firstOrCreate(['name' => $name]);
        }
        foreach (self::ACCESSORIES as $name) {
            Accessory::firstOrCreate(['name' => $name]);
        }
        foreach (self::SPECIALTIES as $name) {
            Specialty::firstOrCreate(['name' => $name]);
        }

        $categories = [];
        foreach (self::CATEGORIES as $name => $template) {
            $categories[$name] = EquipmentCategory::firstOrCreate(['name' => $name], $template);
        }

        foreach (self::CATALOG as $brandName => $models) {
            [$manufacturer, $originCountry] = self::BRAND_INFO[$brandName] ?? [null, null];
            $brand = Brand::firstOrCreate(['name' => $brandName], [
                'manufacturer' => $manufacturer,
                'origin_country' => $originCountry,
            ]);

            foreach ($models as $modelName => $categoryName) {
                EquipmentModel::firstOrCreate(
                    ['brand_id' => $brand->id, 'name' => $modelName],
                    ['category_id' => $categories[$categoryName]->id],
                );
            }
        }

        $this->command?->info('EquipmentCatalogSeeder: catálogos de opciones, '.count(self::CATEGORIES).' categorías y '.count(self::CATALOG).' marcas sembrados.');
    }
}
