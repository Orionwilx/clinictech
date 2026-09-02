<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\EquipmentModel;
use Illuminate\Database\Seeder;

/**
 * Catálogo inicial de marcas y modelos de equipos biomédicos.
 * Idempotente: usa firstOrCreate, se puede re-ejecutar sin duplicar.
 *
 * Ejecutar: php artisan db:seed --class=EquipmentCatalogSeeder
 */
class EquipmentCatalogSeeder extends Seeder
{
    /**
     * Marca => lista de modelos.
     *
     * @var array<string, list<string>>
     */
    private const CATALOG = [
        'Philips' => ['IntelliVue MX40', 'IntelliVue MX450', 'IntelliVue MX550', 'Efficia CM120', 'HeartStart XL', 'Respironics V60'],
        'GE Healthcare' => ['CARESCAPE B650', 'CARESCAPE B450', 'Dash 4000', 'Logiq E10', 'Vivid E95', 'Giraffe OmniBed'],
        'Dräger' => ['Evita V300', 'Evita V500', 'Babylog VN500', 'Fabius Tiro', 'Savina 300', 'Infinity C700'],
        'Mindray' => ['BeneVision N1', 'BeneVision N22', 'uMEC12', 'SV300', 'BeneHeart D6', 'Resona 7'],
        'Medtronic' => ['Puritan Bennett 980', 'Nellcor PM1000N', 'BIS Complete', 'Newport HT70'],
        'B. Braun' => ['Infusomat Space', 'Perfusor Space', 'Dialog+', 'Aesculap'],
        'Fresenius' => ['Agilia VP', 'Agilia SP', '4008S', '5008S CorDiax'],
        'Siemens Healthineers' => ['Acuson Juniper', 'Magnetom Sola', 'Somatom Go', 'Multix Impact'],
        'Zoll' => ['R Series', 'X Series', 'AED Plus', 'Propaq MD'],
        'Nihon Kohden' => ['Life Scope G9', 'Life Scope PT', 'Cardiolife TEC-5600', 'BSM-6000'],
    ];

    public function run(): void
    {
        foreach (self::CATALOG as $brandName => $models) {
            $brand = Brand::firstOrCreate(['name' => $brandName]);

            foreach ($models as $modelName) {
                EquipmentModel::firstOrCreate([
                    'brand_id' => $brand->id,
                    'name' => $modelName,
                ]);
            }
        }

        $this->command?->info('EquipmentCatalogSeeder: '.count(self::CATALOG).' marcas y sus modelos sembrados.');
    }
}
