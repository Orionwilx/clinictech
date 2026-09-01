<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Equipment\StoreEquipmentRequest;
use App\Http\Requests\Equipment\UpdateEquipmentRequest;
use App\Models\Client;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('view equipment');

        $equipment = Equipment::with('client')->withTrashed()->latest()->paginate(15);

        return view('admin.equipment.index', compact('equipment'));
    }

    public function create(): View
    {
        $this->authorize('create equipment');

        return view('admin.equipment.create', ['clients' => $this->clientOptions()]);
    }

    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        Equipment::create($request->validated());

        return redirect()->route('admin.equipment.index')
            ->with('status', 'Equipo creado correctamente.');
    }

    public function show(Equipment $equipment): View
    {
        $this->authorize('view equipment');

        $equipment->load('client');

        return view('admin.equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment): View
    {
        $this->authorize('update equipment');

        return view('admin.equipment.edit', [
            'equipment' => $equipment,
            'clients' => $this->clientOptions(),
        ]);
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $equipment->update($request->validated());

        return redirect()->route('admin.equipment.index')
            ->with('status', 'Equipo actualizado correctamente.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->authorize('delete equipment');

        $equipment->delete();

        return redirect()->route('admin.equipment.index')
            ->with('status', 'Equipo eliminado (recuperable).');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('delete equipment');

        Equipment::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.equipment.index')
            ->with('status', 'Equipo recuperado correctamente.');
    }

    /**
     * Clientes activos para el selector (id => nombre).
     *
     * @return Collection<int, string>
     */
    private function clientOptions()
    {
        return Client::orderBy('name')->pluck('name', 'id');
    }
}
