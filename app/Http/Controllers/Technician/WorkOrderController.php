<?php

namespace App\Http\Controllers\Technician;

use App\Http\Requests\Technician\UpdateWorkOrderRequest;
use App\Models\Accessory;
use App\Models\MaintenanceTask;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Http\JsonResponse;
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

        $workOrder->load('client', 'equipment.brand', 'equipment.model', 'equipment.category', 'equipment.area', 'photos', 'technicianSignature', 'clientSignature');

        return view('technician.work_orders.show', [
            'workOrder' => $workOrder,
            'taskOptions' => MaintenanceTask::active()->orderBy('name')->pluck('name'),
            'accessoryOptions' => Accessory::active()->orderBy('name')->pluck('name'),
        ]);
    }

    public function submit(WorkOrder $workOrder): RedirectResponse
    {
        abort_if($workOrder->technician_id !== $this->technician()->id, 403);
        abort_if($workOrder->status !== 'in_progress', 403);

        $this->service->submitForReview($workOrder);

        return redirect()->route('technician.work_orders.show', $workOrder)
            ->with('status', 'Formulario enviado a revisión del administrador.');
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse|JsonResponse
    {
        [$orderData, $equipmentData] = $this->service->splitEquipmentData($request->validated());

        // Si estaba assigned, moverlo a in_progress al empezar a editar
        if ($workOrder->status === 'assigned') {
            $orderData['status'] = 'in_progress';
            $orderData['started_at'] = $workOrder->started_at ?? now();
        }

        $workOrder->update($orderData);
        // Persiste en la ficha del equipo lo editado desde la OT.
        $this->service->syncEquipmentData($workOrder, $equipmentData, $orderData);

        // Autoguardado del borrador (AJAX desde el formulario del técnico).
        if ($request->expectsJson()) {
            return response()->json(['saved_at' => now()->toIso8601String()]);
        }

        return redirect()->route('technician.work_orders.show', $workOrder)
            ->with('status', 'Formulario guardado.');
    }
}
