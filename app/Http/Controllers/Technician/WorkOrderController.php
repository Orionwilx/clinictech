<?php

namespace App\Http\Controllers\Technician;

use App\Http\Requests\Technician\UpdateWorkOrderRequest;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends TechnicianPanelController
{
    public function __construct(private readonly WorkOrderService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status']);
        $techId = $this->technician()->id;

        $workOrders = WorkOrder::where('technician_id', $techId)
            ->with('client', 'equipment')
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('technician.work_orders.index', [
            'workOrders' => $workOrders,
            'filters' => $filters,
            'statuses' => WorkOrder::STATUSES,
        ]);
    }

    public function show(WorkOrder $workOrder): View
    {
        abort_if($workOrder->technician_id !== $this->technician()->id, 403);

        $workOrder->load('client', 'equipment.brand', 'equipment.model');

        return view('technician.work_orders.show', compact('workOrder'));
    }

    public function submit(WorkOrder $workOrder): RedirectResponse
    {
        abort_if($workOrder->technician_id !== $this->technician()->id, 403);
        abort_if($workOrder->status !== 'in_progress', 403);

        $this->service->submitForReview($workOrder);

        return redirect()->route('technician.work_orders.show', $workOrder)
            ->with('status', 'Formulario enviado a revisión del administrador.');
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validated();

        // Si estaba assigned, moverlo a in_progress al empezar a editar
        if ($workOrder->status === 'assigned') {
            $validated['status'] = 'in_progress';
            $validated['started_at'] = $workOrder->started_at ?? now();
        }

        $workOrder->update($validated);

        return redirect()->route('technician.work_orders.show', $workOrder)
            ->with('status', 'Formulario guardado.');
    }
}
