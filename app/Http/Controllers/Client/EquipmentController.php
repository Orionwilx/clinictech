<?php

namespace App\Http\Controllers\Client;

use App\Models\Equipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EquipmentController extends ClientPanelController
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);

        $equipment = $this->client()->equipment()
            ->with(['brand', 'model', 'area'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%$s%")
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
            'brand', 'model', 'area', 'category',
            'workOrders' => fn ($q) => $q->with('technician')->latest(),
        ]);

        return view('client.equipment.show', compact('equipment'));
    }

    /**
     * Hoja de vida del equipo en PDF (mismo formato que la del admin).
     */
    public function pdf(Equipment $equipment): Response
    {
        abort_if($equipment->client_id !== $this->client()->id, 403);

        $equipment->load([
            'client', 'area', 'category', 'brand', 'model',
            'workOrders' => fn ($q) => $q->with('technician')->latest(),
        ]);

        $pdf = Pdf::loadView('admin.equipment.pdf', [
            'equipment' => $equipment,
            'logoBase64' => $equipment->client?->logoBase64(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download("hoja-de-vida-{$equipment->serial_number}.pdf");
    }
}
