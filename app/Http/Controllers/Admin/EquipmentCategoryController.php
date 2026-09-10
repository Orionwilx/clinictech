<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentCategory\StoreEquipmentCategoryRequest;
use App\Http\Requests\EquipmentCategory\UpdateEquipmentCategoryRequest;
use App\Models\Accessory;
use App\Models\EquipmentCategory;
use App\Models\MaintenanceTask;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view equipment_categories');

        $filters = $request->only(['search']);

        $categories = EquipmentCategory::withCount(['models', 'equipment'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.equipment_categories.index', compact('categories', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create equipment_categories');

        return view('admin.equipment_categories.create', $this->catalogOptions());
    }

    public function store(StoreEquipmentCategoryRequest $request): RedirectResponse
    {
        EquipmentCategory::create($request->validated());

        return redirect()->route('admin.equipment_categories.index')
            ->with('status', 'Categoría creada correctamente.');
    }

    public function edit(EquipmentCategory $equipmentCategory): View
    {
        $this->authorize('update equipment_categories');

        return view('admin.equipment_categories.edit', [
            'category' => $equipmentCategory,
            ...$this->catalogOptions(),
        ]);
    }

    public function update(UpdateEquipmentCategoryRequest $request, EquipmentCategory $equipmentCategory): RedirectResponse
    {
        $equipmentCategory->update($request->validated());

        return redirect()->route('admin.equipment_categories.index')
            ->with('status', 'Categoría actualizada. Los cambios aplican solo a equipos nuevos.');
    }

    public function destroy(EquipmentCategory $equipmentCategory): RedirectResponse
    {
        $this->authorize('delete equipment_categories');

        if ($equipmentCategory->models()->exists()) {
            return redirect()->route('admin.equipment_categories.index')
                ->with('error', 'No se puede eliminar: la categoría tiene modelos asociados.');
        }

        $equipmentCategory->delete();

        return redirect()->route('admin.equipment_categories.index')
            ->with('status', 'Categoría eliminada. Los equipos existentes conservan sus datos.');
    }

    /**
     * Opciones activas de los catálogos para los checkboxes del formulario.
     *
     * @return array<string, mixed>
     */
    private function catalogOptions(): array
    {
        return [
            'specialtyOptions' => Specialty::active()->orderBy('name')->pluck('name'),
            'taskOptions' => MaintenanceTask::active()->orderBy('name')->pluck('name'),
            'accessoryOptions' => Accessory::active()->orderBy('name')->pluck('name'),
        ];
    }
}
