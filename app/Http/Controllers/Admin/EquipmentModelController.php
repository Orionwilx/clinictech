<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentModel\StoreEquipmentModelRequest;
use App\Http\Requests\EquipmentModel\UpdateEquipmentModelRequest;
use App\Models\Brand;
use App\Models\EquipmentModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EquipmentModelController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view equipment_models');

        $filters = $request->only(['search', 'brand_id']);

        $models = EquipmentModel::with('brand')->withCount('equipment')
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($filters['brand_id'] ?? null, fn ($q, $b) => $q->where('brand_id', $b))
            ->orderBy('brand_id')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $brands = Brand::orderBy('name')->pluck('name', 'id');

        return view('admin.equipment_models.index', compact('models', 'filters', 'brands'));
    }

    public function create(): View
    {
        $this->authorize('create equipment_models');

        return view('admin.equipment_models.create', ['brands' => $this->brandOptions()]);
    }

    public function store(StoreEquipmentModelRequest $request): RedirectResponse
    {
        EquipmentModel::create($request->validated());

        return redirect()->route('admin.equipment_models.index')
            ->with('status', 'Modelo creado correctamente.');
    }

    public function edit(EquipmentModel $equipmentModel): View
    {
        $this->authorize('update equipment_models');

        return view('admin.equipment_models.edit', [
            'equipmentModel' => $equipmentModel,
            'brands' => $this->brandOptions(),
        ]);
    }

    public function update(UpdateEquipmentModelRequest $request, EquipmentModel $equipmentModel): RedirectResponse
    {
        $equipmentModel->update($request->validated());

        return redirect()->route('admin.equipment_models.index')
            ->with('status', 'Modelo actualizado correctamente.');
    }

    /** Devuelve los defaults técnicos del modelo en JSON (para auto-fill en formulario de equipo). */
    public function data(EquipmentModel $equipmentModel): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view equipment_models');

        return response()->json($equipmentModel->autoFillData());
    }

    public function destroy(EquipmentModel $equipmentModel): RedirectResponse
    {
        $this->authorize('delete equipment_models');

        $equipmentModel->delete();

        return redirect()->route('admin.equipment_models.index')
            ->with('status', 'Modelo eliminado.');
    }

    /**
     * @return Collection<int, string>
     */
    private function brandOptions()
    {
        return Brand::orderBy('name')->pluck('name', 'id');
    }
}
