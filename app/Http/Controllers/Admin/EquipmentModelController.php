<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentModel\StoreEquipmentModelRequest;
use App\Http\Requests\EquipmentModel\UpdateEquipmentModelRequest;
use App\Models\Brand;
use App\Models\EquipmentModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EquipmentModelController extends Controller
{
    public function index(): View
    {
        $this->authorize('view equipment_models');

        $models = EquipmentModel::with('brand')->withCount('equipment')
            ->orderBy('brand_id')->orderBy('name')->paginate(20);

        return view('admin.equipment_models.index', compact('models'));
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
