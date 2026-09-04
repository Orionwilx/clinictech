<?php

namespace App\Http\Controllers\Client;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentController extends ClientPanelController
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);

        $equipment = $this->client()->equipment()
            ->with(['brand', 'model', 'area'])
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where(fn ($q) => $q->where('name', 'like', "%$s%")
                    ->orWhere('serial_number', 'like', "%$s%"))
            )
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('client.equipment.index', [
            'equipment' => $equipment,
            'filters' => $filters,
            'statuses' => Equipment::STATUSES,
        ]);
    }

    public function show(Equipment $equipment): View
    {
        abort_if($equipment->client_id !== $this->client()->id, 403);

        $equipment->load([
            'brand', 'model', 'area',
            'workOrders' => fn ($q) => $q->with('technician')->latest(),
        ]);

        return view('client.equipment.show', compact('equipment'));
    }
}
