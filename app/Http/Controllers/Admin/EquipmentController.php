<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Equipment\StoreEquipmentRequest;
use App\Http\Requests\Equipment\UpdateEquipmentRequest;
use App\Models\Accessory;
use App\Models\Area;
use App\Models\Brand;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\MaintenanceTask;
use App\Models\Specialty;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view equipment');

        $filters = $request->only(['search', 'client_id', 'status']);

        $equipment = Equipment::with(['client', 'category', 'brand', 'model'])
            ->withTrashed()
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('serial_number', 'like', "%$s%"))
            )
            ->when($filters['client_id'] ?? null, fn ($q, $c) => $q->where('client_id', $c))
            ->when(isset($filters['status']), function ($q) use ($filters) {
                match ($filters['status']) {
                    'deleted' => $q->onlyTrashed(),
                    default => $q->where('status', $filters['status']),
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $clients = Client::orderBy('name')->pluck('name', 'id');

        return view('admin.equipment.index', compact('equipment', 'filters', 'clients'));
    }

    public function create(): View
    {
        $this->authorize('create equipment');

        return view('admin.equipment.create', $this->formOptions());
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

        $equipment->load([
            'client', 'area', 'category', 'brand', 'model',
            'workOrders' => fn ($q) => $q->with('technician')->latest(),
        ]);

        return view('admin.equipment.show', compact('equipment'));
    }

    /**
     * Hoja de vida del equipo en PDF: ficha técnica completa + historial de OT.
     */
    public function pdf(Equipment $equipment): Response
    {
        $this->authorize('view equipment');

        $equipment->load([
            'client', 'area', 'category', 'brand', 'model',
            'workOrders' => fn ($q) => $q->with('technician')->latest(),
        ]);

        $pdf = Pdf::loadView('admin.equipment.pdf', [
            'equipment' => $equipment,
            'logoBase64' => $equipment->client?->logoBase64(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download($equipment->pdfFileName());
    }

    public function edit(Equipment $equipment): View
    {
        $this->authorize('update equipment');

        return view('admin.equipment.edit', array_merge(
            ['equipment' => $equipment],
            $this->formOptions()
        ));
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
     * Alterna rápido activo⇄inactivo sin abrir el formulario.
     * Los estados 'maintenance'/'retired' se fijan desde el formulario completo:
     * desde cualquiera de ellos, activar lleva a 'active'.
     */
    public function toggleActive(Equipment $equipment): RedirectResponse
    {
        $this->authorize('update equipment');

        $equipment->update([
            'status' => $equipment->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('status', "Equipo «{$equipment->name}» marcado como {$equipment->statusLabel()}.");
    }

    /**
     * Opciones para los selectores del formulario.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'areas' => Area::orderBy('name')->get(['id', 'name', 'client_id']),
            // Con fabricante/país para autodiligenciar al elegir la marca.
            'brands' => Brand::orderBy('name')->get(['id', 'name', 'manufacturer', 'origin_country']),
            'models' => EquipmentModel::orderBy('name')->get(['id', 'name', 'brand_id', 'category_id']),
            // Plantillas por categoría, embebidas para el prediligenciado (Alpine).
            'categories' => EquipmentCategory::orderBy('name')->get()
                ->map(fn ($c) => ['id' => $c->id, ...$c->templateData()]),
            'specialtyOptions' => Specialty::active()->orderBy('name')->pluck('name'),
            'taskOptions' => MaintenanceTask::active()->orderBy('name')->pluck('name'),
            'accessoryOptions' => Accessory::active()->orderBy('name')->pluck('name'),
        ];
    }
}
