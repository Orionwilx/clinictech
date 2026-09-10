<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentCatalog\StoreCatalogItemRequest;
use App\Http\Requests\EquipmentCatalog\UpdateCatalogItemRequest;
use App\Models\Accessory;
use App\Models\MaintenanceTask;
use App\Models\Specialty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Catálogos de opciones configurables (subtareas, accesorios, especialidades).
 * El segmento {catalog} coincide con el nombre de la tabla del catálogo.
 */
class EquipmentCatalogController extends Controller
{
    public const CATALOGS = [
        'maintenance_tasks' => ['model' => MaintenanceTask::class, 'label' => 'Subtareas de mantenimiento', 'item' => 'Subtarea'],
        'accessories' => ['model' => Accessory::class, 'label' => 'Accesorios', 'item' => 'Accesorio'],
        'specialties' => ['model' => Specialty::class, 'label' => 'Especialidades', 'item' => 'Especialidad'],
    ];

    public function index(Request $request, string $catalog = 'maintenance_tasks'): View
    {
        $this->authorize('view equipment_catalogs');

        $items = self::CATALOGS[$catalog]['model']::orderBy('name')->get();

        return view('admin.equipment_catalogs.index', [
            'catalog' => $catalog,
            'catalogs' => self::CATALOGS,
            'items' => $items,
        ]);
    }

    public function store(StoreCatalogItemRequest $request, string $catalog): RedirectResponse|JsonResponse
    {
        $item = self::CATALOGS[$catalog]['model']::create($request->validated());

        // Alta rápida desde formularios de categoría/equipo.
        if ($request->wantsJson()) {
            return response()->json(['id' => $item->id, 'name' => $item->name], 201);
        }

        return redirect()->route('admin.equipment_catalogs.index', $catalog)
            ->with('status', self::CATALOGS[$catalog]['item'].' creada correctamente.');
    }

    public function update(UpdateCatalogItemRequest $request, string $catalog, int $item): RedirectResponse
    {
        self::CATALOGS[$catalog]['model']::findOrFail($item)
            ->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.equipment_catalogs.index', $catalog)
            ->with('status', self::CATALOGS[$catalog]['item'].' actualizada.');
    }

    public function destroy(string $catalog, int $item): RedirectResponse
    {
        $this->authorize('delete equipment_catalogs');

        self::CATALOGS[$catalog]['model']::findOrFail($item)->delete();

        return redirect()->route('admin.equipment_catalogs.index', $catalog)
            ->with('status', self::CATALOGS[$catalog]['item'].' eliminada. Los valores ya guardados en categorías y equipos se conservan.');
    }
}
