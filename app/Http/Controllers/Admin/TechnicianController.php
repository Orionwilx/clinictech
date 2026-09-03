<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\StoreTechnicianRequest;
use App\Http\Requests\Technician\UpdateTechnicianRequest;
use App\Models\Technician;
use App\Services\TechnicianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TechnicianController extends Controller
{
    public function __construct(private readonly TechnicianService $technicians) {}

    public function index(Request $request): View
    {
        $this->authorize('view technicians');

        $filters = $request->only(['search', 'status']);

        $technicians = Technician::withTrashed()
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where(fn ($q) => $q->where('name', 'like', "%$s%")
                    ->orWhere('document', 'like', "%$s%")
                    ->orWhere('specialty', 'like', "%$s%"))
            )
            ->when(isset($filters['status']), function ($q) use ($filters) {
                match ($filters['status']) {
                    'deleted'  => $q->onlyTrashed(),
                    'inactive' => $q->where('is_active', false),
                    'active'   => $q->where('is_active', true),
                    default    => null,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.technicians.index', compact('technicians', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create technicians');

        return view('admin.technicians.create');
    }

    public function store(StoreTechnicianRequest $request): RedirectResponse
    {
        $this->technicians->create($request->validated());

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico creado correctamente.');
    }

    public function show(Technician $technician): View
    {
        $this->authorize('view technicians');

        $technician->load([
            'workOrders.client',
            'workOrders.equipment.brand',
            'workOrders.equipment.model',
            'workOrders.equipment.client',
        ]);

        $workedEquipment = $technician->workOrders
            ->whereNotNull('equipment_id')
            ->pluck('equipment')
            ->unique('id')
            ->filter()
            ->sortBy('name')
            ->values();

        return view('admin.technicians.show', compact('technician', 'workedEquipment'));
    }

    public function edit(Technician $technician): View
    {
        $this->authorize('update technicians');

        return view('admin.technicians.edit', compact('technician'));
    }

    public function update(UpdateTechnicianRequest $request, Technician $technician): RedirectResponse
    {
        $this->technicians->update($technician, $request->validated());

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico actualizado correctamente.');
    }

    public function destroy(Technician $technician): RedirectResponse
    {
        $this->authorize('delete technicians');

        $this->technicians->delete($technician);

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico eliminado (recuperable).');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('delete technicians');

        Technician::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico recuperado correctamente.');
    }
}
